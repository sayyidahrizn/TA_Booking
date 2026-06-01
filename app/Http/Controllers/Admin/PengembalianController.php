<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Denda;
use App\Models\Pembayaran;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    /**
     * HALAMAN VALIDASI PENGEMBALIAN
     */
    public function index()
    {
        $pengembalian = Pengembalian::with([
            'penyewaan.fasilitas',
            'penyewaan.user',
            'penyewaan.denda',
            'penyewaan.pembayaran'
        ])->latest()->get();

        // Hitung denda keterlambatan secara dinamis
        $pengembalian->each(function ($item) {
            $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
            $tglKembali = Carbon::parse($item->tanggal_pengembalian)->startOfDay();

            $item->hari_telat = $tglKembali->gt($deadline) ? $deadline->diffInDays($tglKembali) : 0;
            $item->denda_telat_otomatis = $item->hari_telat * 10000;
        });

        // Grouping berdasarkan kode booking
        $grouped = $pengembalian->groupBy(function ($item) {
            return $item->penyewaan->kode_booking ?? 'ID-' . $item->id_penyewaan;
        });

        // Pagination Manual
        $currentPage = request()->get('page', 1);
        $perPage = 8;
        $currentItems = $grouped->slice(($currentPage - 1) * $perPage, $perPage);

        $data = new LengthAwarePaginator(
            $currentItems,
            $grouped->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.pengembalian.index', compact('data'));
    }

    /**
     * VALIDASI PENGEMBALIAN
     */
    public function validasi(Request $request)
    {
        $kodeBooking = $request->kode_booking ?? $request->submit_booking;
        $request->merge(['kode_booking' => $kodeBooking]);

        $request->validate([
            'kode_booking'    => 'required',
            'jenis_kerusakan' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $pengembalianList = Pengembalian::with(['penyewaan.fasilitas', 'penyewaan.user'])
                ->whereHas('penyewaan', function ($q) use ($kodeBooking) {
                    $q->where('kode_booking', $kodeBooking);
                })->get();

            if ($pengembalianList->isEmpty()) {
                return back()->with('error', 'Data tidak ditemukan.');
            }

            $penyewaanIds = $pengembalianList->pluck('id_penyewaan')->unique()->toArray();
            $penyewaanUtama = $pengembalianList->first()->penyewaan;
            
            // Hapus denda lama agar tidak double
            Denda::where('kode_booking', $kodeBooking)->where('status_denda', 'belum_bayar')->delete();

            $totalDendaTelat = 0;
            $totalDendaRusak = 0;
            $catatanGrup = [];
            $jenisKerusakanTerparah = 'tidak_rusak';

            // Bagian dalam method validasi()
            foreach ($pengembalianList as $item) {
                $id = $item->id;
                
                // Ambil data dari request
                $kerusakan = $request->jenis_kerusakan[$id] ?? 'tidak_rusak';
                // Gunakan regex untuk menghapus semua karakter kecuali angka
                $nominalDendaRequest = preg_replace('/[^0-9]/', '', $request->denda_rusak[$id] ?? '0');
                $catatan = $request->catatan_admin[$id] ?? null;

                // 1. Hitung Denda Telat (Gunakan endOfDay agar adil bagi penyewa)
                $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->endOfDay();
                $tglKembali = Carbon::parse($item->tanggal_pengembalian);

                // Jika tanggal kembali melewati deadline
                if ($tglKembali->gt($deadline)) {
                    // diffInDays menghasilkan angka bulat
                    $hariTelat = $tglKembali->diffInDays($deadline);
                    $totalDendaTelat += ($hariTelat * 10000);
                }

                // 2. Hitung Denda Rusak
                $biayaRusakItem = 0;
                if ($kerusakan === 'ringan') {
                    $biayaRusakItem = (int) $nominalDendaRequest;
                    if($jenisKerusakanTerparah !== 'berat') $jenisKerusakanTerparah = 'ringan';
                } elseif ($kerusakan === 'berat') {
                    $biayaRusakItem = $item->penyewaan->fasilitas->harga_benda ?? 0;
                    $jenisKerusakanTerparah = 'berat';
                }
                $totalDendaRusak += $biayaRusakItem;

                // 3. Update status item pengembalian
                $item->update([
                    'status_validasi' => 'disetujui',
                    'catatan_admin'   => $catatan,
                ]);

                if($catatan) {
                    $catatanGrup[] = $item->penyewaan->fasilitas->nama_fasilitas . ": " . $catatan;
                }
            }

            $totalDendaFinal = $totalDendaTelat + $totalDendaRusak;

            // JIKA TOTAL DENDA > 0, BARU SIMPAN KE TABEL DENDA
            if ($totalDendaFinal > 0) {
                Denda::create([
                    'id_penyewaan'         => $penyewaanUtama->id_penyewaan,
                    'kode_booking'         => $kodeBooking,
                    'jenis_kerusakan'      => $jenisKerusakanTerparah,
                    'biaya_keterlambatan'  => $totalDendaTelat,
                    'biaya_kerusakan'      => $totalDendaRusak,
                    'total_denda'          => $totalDendaFinal,
                    'keterangan_kerusakan' => implode("; ", $catatanGrup),
                    'status_denda'         => 'belum_bayar',
                ]);

                // Update status menjadi menunggu denda
                DB::table('penyewaan')->whereIn('id_penyewaan', $penyewaanIds)
                    ->update(['status_sewa' => 'menunggu_pembayaran_denda']);
                
                // WA Notifikasi...
                $user = $penyewaanUtama->user;
                if ($user && $user->no_hp) {
                    $pesan = "Halo *{$user->name}*\n\nPengembalian fasilitas kode booking *{$kodeBooking}* sudah divalidasi.\nTotal Denda: *Rp " . number_format($totalDendaFinal, 0, ',', '.') . "*\nSilakan selesaikan pembayaran denda melalui dashboard.";
                    FonnteService::send($user->no_hp, $pesan);
                }
            } else {
                // Jika benar-benar 0 (tidak telat & tidak rusak), status baru selesai
                DB::table('penyewaan')->whereIn('id_penyewaan', $penyewaanIds)
                    ->update(['status_sewa' => 'selesai']);
            }

            DB::commit();
            return back()->with('success', 'Berhasil memvalidasi booking ' . $kodeBooking);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Validasi: " . $e->getMessage());
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * KONFIRMASI PEMBAYARAN TUNAI
     */
    public function konfirmasiPembayaran(Request $request, $id)
    {

        $request->validate([
            'jumlah_bayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $denda = Denda::with('penyewaan')->findOrFail($id);

            if ($request->jumlah_bayar < $denda->total_denda) {
                return back()->with('error', 'Uang kurang dari total denda!');
            }

            // ✅ simpan ke tabel pembayaran
            Pembayaran::create([
                'id_penyewaan'      => $denda->id_penyewaan,
                'kode_pembayaran'   => 'BYR-' . strtoupper(uniqid()),
                'jenis_pembayaran'  => 'pelunasan',
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar'      => $request->jumlah_bayar,
                'status_pembayaran' => 'berhasil',
                'tanggal_bayar'     => now(),
            ]);

            // ✅ update status denda
            $denda->update([
                'status_denda' => 'lunas',

            ]);

            // ✅ update status sewa (semua item satu booking)
            if (!empty($denda->kode_booking)) {
                DB::table('penyewaan')
                    ->where('kode_booking', $denda->kode_booking)
                    ->update(['status_sewa' => 'selesai']);
            } else {
                $denda->penyewaan->update([
                    'status_sewa' => 'selesai',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Pembayaran tunai berhasil!');

        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Konfirmasi pembayaran tunai denda gagal', [
                'id_denda' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * CETAK BUKTI DENDA
     */
    public function buktiDenda($id)
    {
        $denda = Denda::with(['penyewaan.user', 'penyewaan.fasilitas'])->findOrFail($id);

        if ($denda->status_denda == 'belum_bayar') {
            return back()->with('error', 'Tagihan belum divalidasi admin.');
        }

        return view('admin.pengembalian.bukti', compact('denda'));
    }
}

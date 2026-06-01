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
        // Ambil nilai dari 'kode_booking' atau 'submit_booking'
        $kodeBooking = $request->kode_booking ?? $request->submit_booking;

        // Masukkan kembali ke request agar validator Laravel bekerja
        $request->merge(['kode_booking' => $kodeBooking]);

        // 1. Validasi input
        $request->validate([
            'kode_booking'    => 'required',
            'jenis_kerusakan' => 'required|array',
        ], [
            'kode_booking.required'    => 'Kode booking tidak ditemukan.',
            'jenis_kerusakan.required' => 'Kondisi kerusakan belum dipilih.',
        ]);

        DB::beginTransaction();

        try {
            // 2. Ambil semua data pengembalian
            $pengembalianList = Pengembalian::with(['penyewaan.fasilitas', 'penyewaan.user'])
                ->whereHas('penyewaan', function ($q) use ($kodeBooking) {
                    $q->where('kode_booking', $kodeBooking);
                })->get();

            if ($pengembalianList->isEmpty()) {
                return back()->with('error', 'Data pengembalian tidak ditemukan di sistem.');
            }

            $penyewaanIds = $pengembalianList
                ->pluck('id_penyewaan')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $existingDenda = Denda::where('kode_booking', $kodeBooking)
                ->whereIn('status_denda', ['belum_bayar', 'lunas'])
                ->first();

            if ($existingDenda) {
                if (!empty($penyewaanIds)) {
                    DB::table('penyewaan')
                        ->whereIn('id_penyewaan', $penyewaanIds)
                        ->update([
                            'status_sewa' => $existingDenda->status_denda === 'lunas'
                                ? 'selesai'
                                : 'menunggu_pembayaran_denda'
                        ]);
                }
                return back()->with('success', 'Kode Booking ' . $kodeBooking . ' sudah ditagih sebelumnya.');
            }

            $totalDendaTelat = 0;
            $totalDendaRusak = 0;
            $catatanGrup = [];
            $jenisKerusakanTerparah = 'tidak_rusak';

            // 3. Loop setiap item
            foreach ($pengembalianList as $item) {
                $id = $item->id;
                $penyewaan = $item->penyewaan;
                
                $kerusakan = $request->jenis_kerusakan[$id] ?? 'tidak_rusak';
                $catatan = $request->catatan_admin[$id] ?? null;

                // A. Hitung Denda Keterlambatan (Misal: 10rb/hari)
                $deadline = \Carbon\Carbon::parse($penyewaan->tgl_selesai)->startOfDay();
                $tglKembali = \Carbon\Carbon::parse($item->tanggal_pengembalian)->startOfDay();
                $hariTelat = $tglKembali->gt($deadline) ? $tglKembali->diffInDays($deadline) : 0;
                $totalDendaTelat += ($hariTelat * 10000);

                // B. Hitung Denda Kerusakan
                $biayaRusakItem = 0;
                if ($kerusakan === 'ringan') {
                    $rawDenda = $request->denda_rusak[$id] ?? '0';
                    $biayaRusakItem = (int) str_replace(['.', ','], '', $rawDenda);
                    
                    if($jenisKerusakanTerparah !== 'berat') $jenisKerusakanTerparah = 'ringan';
                } elseif ($kerusakan === 'berat') {
                    $biayaRusakItem = $penyewaan->fasilitas->harga_benda ?? 0;
                    $jenisKerusakanTerparah = 'berat';
                }
                $totalDendaRusak += $biayaRusakItem;

                // C. Update status item pengembalian
                $item->update([
                    'status_validasi' => 'disetujui',
                    'catatan_admin'   => $catatan,
                ]);

                if($catatan) {
                    $catatanGrup[] = "{$penyewaan->fasilitas->nama_fasilitas}: {$catatan}";
                }
            }

            // 4. Update/Create Tabel Denda & Status Utama
            $penyewaanUtama = $pengembalianList->first()->penyewaan;
            $totalDendaFinal = $totalDendaTelat + $totalDendaRusak;
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

                if (!empty($penyewaanIds)) {
                    DB::table('penyewaan')
                        ->whereIn('id_penyewaan', $penyewaanIds)
                        ->update(['status_sewa' => 'menunggu_pembayaran_denda']);
                }

                // =========================
                // KIRIM WHATSAPP
                // =========================
                $user = $penyewaanUtama->user;
                if ($user && $user->no_hp) {
                    $pesan = "Halo *{$user->name}*\n\n" .
                            "Pengembalian fasilitas sudah divalidasi admin.\n\n" .
                            "Kode Booking: *{$kodeBooking}*\n" .
                            "Total Denda: *Rp " . number_format($totalDendaFinal, 0, ',', '.') . "*\n" .
                            "Status: *Belum Dibayar*\n\n" .
                            "Silakan segera melakukan pembayaran denda melalui dashboard Anda.\n\n" .
                            "Terima kasih.";

                    $sent = FonnteService::send($user->no_hp, $pesan);
                    if (!$sent) {
                        Log::warning('Notifikasi denda gagal dikirim via Fonnte', [
                            'kode_booking' => $kodeBooking,
                            'user_id' => $user->id ?? null,
                            'target' => $user->no_hp,
                        ]);
                    }
                }

            } else {
                // Jika tidak ada denda sama sekali
                if (!empty($penyewaanIds)) {
                    DB::table('penyewaan')
                        ->whereIn('id_penyewaan', $penyewaanIds)
                        ->update(['status_sewa' => 'selesai']);
                }
            }

            DB::commit();
            return back()->with('success', 'Berhasil memvalidasi Kode Booking: ' . $kodeBooking);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Validasi pengembalian gagal', [
                'kode_booking' => $kodeBooking,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal memproses validasi: ' . $e->getMessage());
        }
    }

    /**
     * KONFIRMASI PEMBAYARAN TUNAI
     */
    public function konfirmasiPembayaran(Request $request, $id)
    {
        $request->validate([
            'jumlah_dibayar' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $denda = Denda::with('penyewaan')->findOrFail($id);

            if ($request->jumlah_dibayar < $denda->total_denda) {
                return back()->with('error', 'Uang kurang dari total denda!');
            }

            // ✅ simpan ke tabel pembayaran
            Pembayaran::create([
                'id_penyewaan'      => $denda->id_penyewaan,
                'kode_pembayaran'   => 'BYR-' . strtoupper(uniqid()),
                'jenis_pembayaran'  => 'pelunasan',
                'metode_pembayaran' => 'tunai',
                'jumlah_bayar'      => $request->jumlah_dibayar,
                'status_pembayaran' => 'berhasil',
                'tanggal_bayar'     => now(),
            ]);

            // ✅ update status denda
            $denda->update([
                'status_denda' => 'lunas',
                'metode_pembayaran' => 'tunai',
                'jumlah_dibayar' => $request->jumlah_dibayar,
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

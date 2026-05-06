<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\User;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyewaanController extends Controller
{
    /**
     * VERIFIKASI PEMBAYARAN MANUAL
     */
    public function verifikasiPembayaran($id_pembayaran)
    {
        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::with('penyewaan')->findOrFail($id_pembayaran);

            $pembayaran->update([
                'status_pembayaran' => 'diverifikasi',
                'tanggal_bayar' => now(),
                'metode_pembayaran' => 'tunai',
                // PENTING: Pastikan jumlah_bayar diupdate dari total_harga penyewaan terkait
                'jumlah_bayar' => $pembayaran->penyewaan->total_harga 
            ]);

            DB::commit();
            return back()->with('success', 'Pembayaran diverifikasi. User sekarang bisa cetak bukti.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal verifikasi: ' . $e->getMessage());
        }
    }

    /**
     * HALAMAN PEMBAYARAN ADMIN
     */
    public function pembayaran()
    {
        $pembayarans = Penyewaan::with(['user', 'fasilitas', 'pembayaran'])
            ->orderBy('tgl_mulai', 'desc')
            ->get()
            ->groupBy('kode_booking');

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    /**
     * DASHBOARD ADMIN
     */
    public function dashboard()
    {
        $totalPendapatan = Penyewaan::whereHas('pembayaran', function($q) {
                $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
            })
            ->orWhere('status_sewa', 'disetujui')
            ->sum('total_harga');

        $totalFasilitas = Fasilitas::count();
        $totalPenyewaan = Penyewaan::count();

        $totalKembali = Penyewaan::where('status_sewa', 'selesai')
            ->orWhereHas('pengembalian', function($q) {
                $q->where('status_validasi', 'disetujui');
            })
            ->count();

        $pending = Penyewaan::where('status_sewa', 'proses')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $pendapatanBulanan = Penyewaan::select(
                DB::raw('SUM(total_harga) as total'),
                DB::raw('MONTH(tgl_mulai) as bulan')
            )
            ->whereYear('tgl_mulai', date('Y'))
            ->where(function($query) {
                $query->whereHas('pembayaran', function($q) {
                    $q->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
                })
                ->orWhere('status_sewa', 'disetujui');
            })
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $dataGrafik = [];
        $bulanSekarang = date('n');
        for ($i = 1; $i <= $bulanSekarang; $i++) {
            $dataGrafik[] = $pendapatanBulanan[$i] ?? 0;
        }

        $penyewaan = Penyewaan::with(['user', 'fasilitas', 'pengembalian', 'pembayaran'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        return view('admin.dashboard', compact(
            'totalPendapatan', 'totalFasilitas', 'totalKembali',
            'totalPenyewaan', 'pending', 'penyewaan', 'dataGrafik'
        ));
    }

    /**
     * LIST PENYEWAAN
     */
    public function index()
    {
        $penyewaan = Penyewaan::with(['user', 'fasilitas', 'pembayaran'])
            ->whereNotNull('kode_booking')
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('admin.penyewaan.index', compact('penyewaan'));
    }

    /**
     * KONFIRMASI BOOKING (INI BAGIAN PALING PENTING)
     */
    public function konfirmasiGroup($kode)
    {
        DB::transaction(function () use ($kode) {

            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {

                // 1. SETUJUI SEWA
                $item->update([
                    'status_sewa' => 'disetujui'
                ]);

                // 2. CEK PEMBAYARAN SUDAH ADA ATAU BELUM
                $pembayaran = Pembayaran::where('id_penyewaan', $item->id_penyewaan)->first();

                if ($pembayaran) {
                    // UPDATE (KARENA SUDAH DIBUAT DARI USER)
                    $pembayaran->update([
                        'jenis_pembayaran' => 'pelunasan',
                        'metode_pembayaran' => 'midtrans',
                        'jumlah_bayar' => $item->total_harga,
                        'status_pembayaran' => 'pending'
                    ]);
                } else {
                    // CREATE (JAGA-JAGA KALAU BELUM ADA)
                    Pembayaran::create([
                        'id_penyewaan' => $item->id_penyewaan,
                        'kode_pembayaran' => 'PAY-' . time(),
                        'jenis_pembayaran' => 'lunas',
                        'metode_pembayaran' => 'midtrans',
                        'jumlah_bayar' => $item->total_harga,
                        'status_pembayaran' => 'pending',
                    ]);
                }
            }
        });

        return back()->with('success', 'Booking ' . $kode . ' disetujui.');
    }

    /**
     * TOLAK BOOKING
     */
    public function tolakGroup($kode)
    {
        DB::transaction(function () use ($kode) {

            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {

                if ($item->status_sewa !== 'batal') {
                    $fasilitas = Fasilitas::find($item->id_fasilitas);

                    if ($fasilitas) {
                        $fasilitas->increment('jumlah', $item->jumlah_sewa);

                        if ($fasilitas->jumlah > 0) {
                            $fasilitas->update(['status_fasilitas' => 'tersedia']);
                        }
                    }
                }

                $item->update(['status_sewa' => 'batal']);

                Pembayaran::where('id_penyewaan', $item->id_penyewaan)
                    ->update(['status_pembayaran' => 'batal']);
            }
        });

        return back()->with('success', 'Booking ' . $kode . ' telah ditolak.');
    }

    /**
     * HAPUS DATA
     */
    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }
}
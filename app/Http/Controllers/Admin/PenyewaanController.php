<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PenyewaanController extends Controller
{
    /**
     * DASHBOARD ADMIN - Tetap dengan fitur Statistik & Grafik
     */
    public function dashboard()
    {
        $totalPendapatan = Penyewaan::where('status_pembayaran', 'lunas')
            ->orWhere('status_sewa', 'disetujui')
            ->sum('total_harga');

        $pendapatanBulanan = Penyewaan::select(
                DB::raw('SUM(total_harga) as total'),
                DB::raw('MONTH(tgl_mulai) as bulan')
            )
            ->whereYear('tgl_mulai', date('Y'))
            ->where(function($query) {
                $query->where('status_pembayaran', 'lunas')
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

        $totalFasilitas = Fasilitas::count();
        $totalPenyewaan = Penyewaan::count();
        
        $pending = Penyewaan::where('status_sewa', 'proses')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        $penyewaan = Penyewaan::with(['user','fasilitas'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        return view('admin.dashboard', compact(
            'totalPendapatan', 'totalFasilitas', 'totalPenyewaan', 'pending', 'penyewaan', 'dataGrafik'
        ));
    }

    public function index()
    {
        $penyewaan = Penyewaan::with(['user', 'fasilitas'])
            ->whereNotNull('kode_booking')
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('admin.penyewaan.index', compact('penyewaan'));
    }

    /**
     * KONFIRMASI GROUP - Menyetujui semua item
     */
    public function konfirmasiGroup($kode)
    {
        DB::transaction(function () use ($kode) {
            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {
                $item->update([
                    'status_pembayaran' => 'lunas',
                    'status_sewa' => 'disetujui'
                ]);

                // Update status fasilitas hanya jika stoknya benar-benar habis (0)
                $fasilitas = Fasilitas::find($item->id_fasilitas);
                if ($fasilitas && $fasilitas->jumlah <= 0) {
                    $fasilitas->update(['status_fasilitas' => 'tidak tersedia']);
                }
            }
        });

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' berhasil disetujui!');
    }

    /**
     * TOLAK GROUP - Membatalkan & Mengembalikan Stok
     */
    public function tolakGroup($kode)
    {
        DB::transaction(function () use ($kode) {
            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {
                // Kembalikan stok jika status sebelumnya bukan 'batal'
                if ($item->status_sewa !== 'batal') {
                    $fasilitas = Fasilitas::find($item->id_fasilitas);
                    if ($fasilitas) {
                        $fasilitas->increment('jumlah', $item->jumlah_sewa);
                        
                        // Aktifkan kembali status jika stok bertambah
                        if ($fasilitas->jumlah > 0) {
                            $fasilitas->update(['status_fasilitas' => 'tersedia']);
                        }
                    }
                }
                $item->update(['status_sewa' => 'batal', 'status_pembayaran' => 'batal']);
            }
        });

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' telah ditolak dan stok dikembalikan.');
    }

    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }
}
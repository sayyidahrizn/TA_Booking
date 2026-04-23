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
     * DASHBOARD ADMIN - Fitur Statistik, Grafik & Data Otomatis
     */
    public function dashboard()
    {
        // 1. Total Pendapatan
        $totalPendapatan = Penyewaan::where('status_pembayaran', 'lunas')
            ->orWhere('status_sewa', 'disetujui')
            ->sum('total_harga');

        // 2. Statistik Tambahan (Otomatis)
        $totalFasilitas = Fasilitas::count();
        $totalPenyewaan = Penyewaan::count();
        
        // Logika Fasilitas Kembali: Hitung yang sudah mengisi form pengembalian (has pengembalian) 
        // atau status_sewa sudah selesai
        $totalKembali = Penyewaan::where('status_sewa', 'selesai')
            ->orWhereHas('pengembalian')
            ->count();
        
        $pending = Penyewaan::where('status_sewa', 'proses')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        // 3. Logika Grafik Pendapatan
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

        // 4. Data Tabel Penyewaan Terbaru - Load relasi 'pengembalian' untuk deteksi di blade
        $penyewaan = Penyewaan::with(['user', 'fasilitas', 'pengembalian'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        return view('admin.dashboard', compact(
            'totalPendapatan', 
            'totalFasilitas', 
            'totalKembali', 
            'totalPenyewaan', 
            'pending', 
            'penyewaan', 
            'dataGrafik'
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

    public function konfirmasiGroup($kode)
    {
        DB::transaction(function () use ($kode) {
            $data = Penyewaan::where('kode_booking', $kode)->get();

            foreach ($data as $item) {
                $item->update([
                    'status_pembayaran' => 'lunas',
                    'status_sewa' => 'disetujui'
                ]);

                $fasilitas = Fasilitas::find($item->id_fasilitas);
                if ($fasilitas && $fasilitas->jumlah <= 0) {
                    $fasilitas->update(['status_fasilitas' => 'tidak tersedia']);
                }
            }
        });

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' berhasil disetujui!');
    }

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
                $item->update(['status_sewa' => 'batal', 'status_pembayaran' => 'batal']);
            }
        });

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' telah ditolak.');
    }

    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }
}
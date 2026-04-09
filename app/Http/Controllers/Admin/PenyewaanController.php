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
     * DASHBOARD ADMIN
     * Menampilkan statistik utama termasuk pendapatan hasil sewa
     */
    public function dashboard()
    {
        // 1. Hitung total uang dari penyewaan yang statusnya 'disetujui' atau pembayaran 'lunas'
        $totalPendapatan = Penyewaan::where('status_pembayaran', 'lunas')
            ->orWhere('status_sewa', 'disetujui')
            ->sum('total_harga');

        // 2. Hitung statistik dasar
        $totalFasilitas = Fasilitas::count();
        $totalPenyewaan = Penyewaan::count();
        
        // 3. Hitung jumlah transaksi (grup per kode booking) yang statusnya masih 'proses'
        $pending = Penyewaan::where('status_sewa', 'proses')
            ->get()
            ->groupBy('kode_booking')
            ->count();

        // 4. Ambil 5 transaksi terbaru untuk ditampilkan di tabel dashboard (Grouped)
        $penyewaan = Penyewaan::with(['user','fasilitas'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        return view('admin.dashboard', compact(
            'totalPendapatan',
            'totalFasilitas',
            'totalPenyewaan',
            'pending',
            'penyewaan'
        ));
    }

    /**
     * LIST SEMUA PENYEWAAN (INDEX)
     * Menampilkan semua data booking yang dikelompokkan
     */
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
     * KONFIRMASI GROUP
     * Menyetujui semua item dalam satu kode booking sekaligus
     */
    public function konfirmasiGroup($kode)
    {
        $data = Penyewaan::where('kode_booking', $kode)->get();

        foreach ($data as $item) {
            // Update status sewa dan pembayaran
            $item->update([
                'status_pembayaran' => 'lunas',
                'status_sewa' => 'disetujui'
            ]);

            // Update status fasilitas menjadi tidak tersedia agar tidak bisa dipesan orang lain
            $fasilitas = Fasilitas::find($item->id_fasilitas);
            if ($fasilitas) {
                $fasilitas->update(['status_fasilitas' => 'tidak tersedia']);
            }
        }

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' berhasil disetujui!');
    }

    /**
     * TOLAK GROUP
     * Membatalkan seluruh pesanan dalam satu kode booking
     */
    public function tolakGroup($kode)
    {
        Penyewaan::where('kode_booking', $kode)
            ->update(['status_sewa' => 'batal']);

        return back()->with('success', 'Semua booking dengan kode ' . $kode . ' telah ditolak.');
    }

    /**
     * HAPUS SATUAN
     * Menghapus data penyewaan secara permanen
     */
    public function destroy($id)
    {
        Penyewaan::findOrFail($id)->delete();
        return back()->with('success', 'Data penyewaan berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\Pengembalian;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    private function getLaporanData(Request $request)
    {
        // --- PAKSA SETTING WAKTU DI DALAM KODE ---
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');
        config(['app.locale' => 'id']);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $penyewaanQuery = Penyewaan::query();
        $pengembalianQuery = Pengembalian::query();

        $periodeTeks = "Semua Waktu";

        if ($startDate && $endDate) {
            $penyewaanQuery->whereBetween('penyewaan.created_at', [
                $startDate . ' 00:00:00', 
                $endDate . ' 23:59:59'
            ]);
            
            $pengembalianQuery->whereBetween('pengembalian.created_at', [
                $startDate . ' 00:00:00', 
                $endDate . ' 23:59:59'
            ]);

            $startFormatted = Carbon::parse($startDate)->translatedFormat('d F Y');
            $endFormatted = Carbon::parse($endDate)->translatedFormat('d F Y');
            $periodeTeks = $startFormatted . ' s/d ' . $endFormatted;
        }

        // 1. Statistik User
        $totalPenyewa = User::where('role', 'penyewa')->count();

        // 2. Statistik Pembayaran (Ditolak)
        $belumBayar = (clone $penyewaanQuery)->where('status_pembayaran', 'pending')->count();
        $sudahBayar = (clone $penyewaanQuery)->where('status_pembayaran', 'lunas')->count();
        $ditolak    = (clone $penyewaanQuery)->where('status_pembayaran', 'batal')->count();

        // 3. Statistik Pengembalian (Belum Kembali)
        $belumKembali = (clone $penyewaanQuery)->where('status_pengembalian', 'belum')->count();
        $sudahKembali = (clone $penyewaanQuery)->where('status_pengembalian', 'selesai')->count();

        // 4. Inventaris Fasilitas
        $totalStok = Fasilitas::sum('jumlah');
        $fasilitasDisewa = (clone $penyewaanQuery)->where('status_sewa', 'disetujui')
            ->where('status_pengembalian', 'belum')
            ->sum('jumlah_sewa');
        $fasilitasTersedia = $totalStok - $fasilitasDisewa;

        // --- TAMBAHAN: AMBIL DETAIL PER FASILITAS ---
        $daftarFasilitas = Fasilitas::all(); 

        // 5. Query Fasilitas Terlaris
        $fasilitasTerlaris = (clone $penyewaanQuery)
            ->join('fasilitas', 'penyewaan.id_fasilitas', '=', 'fasilitas.id_fasilitas')
            ->select('fasilitas.nama_fasilitas', DB::raw('SUM(penyewaan.jumlah_sewa) as total'))
            ->groupBy('fasilitas.id_fasilitas', 'fasilitas.nama_fasilitas')
            ->orderByDesc('total')->limit(5)->get();

        // 6. Keuangan
        $totalUang = (clone $penyewaanQuery)->where('status_pembayaran', 'lunas')->sum('total_harga');
        $totalDenda = $pengembalianQuery->where('status_pembayaran_denda', 'lunas')->sum('total_denda');

        // 7. WAKTU CETAK REALTIME (WIB)
        $tglCetak = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');
        $waktuCetak = Carbon::now('Asia/Jakarta')->format('H:i');

        return compact(
            'totalPenyewa', 'belumBayar', 'sudahBayar', 'ditolak',
            'belumKembali', 'sudahKembali', 'fasilitasTersedia', 'totalStok',
            'fasilitasDisewa', 'fasilitasTerlaris', 'totalUang', 'totalDenda',
            'startDate', 'endDate', 'periodeTeks', 'tglCetak', 'waktuCetak',
            'daftarFasilitas'
        );
    }

    public function index(Request $request)
    {
        return view('admin.laporan.index', $this->getLaporanData($request));
    }

    public function downloadPDF(Request $request)
    {
        $data = $this->getLaporanData($request);
        $pdf = Pdf::loadView('admin.laporan.pdf', $data)->setPaper('a4', 'portrait');
        $namaFile = 'Laporan-Desa-'.Carbon::now('Asia/Jakarta')->format('d-m-Y-H-i').'.pdf';
        return $pdf->download($namaFile);
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->getLaporanData($request);
        $fileName = 'Laporan-Desa-'.date('d-m-Y').'.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Trik 1: Tambahkan BOM UTF-8 agar karakter Rp dan simbol terbaca rapi
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 

            // Trik 2: Gunakan Titik Koma (;) sebagai pemisah karena Excel Indonesia biasanya pakai itu
            // Jika Excel kamu masih berantakan, ganti ';' menjadi ','
            $delimiter = ";"; 

            fputcsv($file, ['LAPORAN PENYEWAAN FASILITAS DESA KESAMBEN'], $delimiter);
            fputcsv($file, ['Periode Laporan:', $data['periodeTeks']], $delimiter);
            fputcsv($file, ['Tanggal Cetak:', $data['tglCetak'] . ' ' . $data['waktuCetak'] . ' WIB'], $delimiter);
            fputcsv($file, [], $delimiter); 

            fputcsv($file, ['I. RINGKASAN AKTIVITAS & KEUANGAN'], $delimiter);
            fputcsv($file, ['NO', 'KATEGORI', 'KETERANGAN', 'JUMLAH', 'NOMINAL (Rp)'], $delimiter);
            fputcsv($file, ['1', 'User', 'Total Penyewa', $data['totalPenyewa'], '-'], $delimiter);
            fputcsv($file, ['2', 'Pembayaran', 'Sewa Lunas', $data['sudahBayar'], '-'], $delimiter);
            fputcsv($file, ['3', 'Pembayaran', 'Sewa Ditolak', $data['ditolak'], '-'], $delimiter);
            fputcsv($file, ['4', 'Pengembalian', 'Belum Kembali', $data['belumKembali'], '-'], $delimiter);
            fputcsv($file, ['5', 'Keuangan', 'Uang Sewa', '-', number_format($data['totalUang'], 0, ',', '.')], $delimiter);
            fputcsv($file, ['6', 'Keuangan', 'Denda', '-', number_format($data['totalDenda'], 0, ',', '.')], $delimiter);
            fputcsv($file, ['', '', 'GRAND TOTAL PENDAPATAN', '', 'Rp ' . number_format($data['totalUang'] + $data['totalDenda'], 0, ',', '.')], $delimiter);
            fputcsv($file, [], $delimiter);

            fputcsv($file, ['II. RINCIAN STOK FASILITAS TERSEDIA'], $delimiter);
            fputcsv($file, ['NO', 'NAMA FASILITAS', 'TOTAL STOK'], $delimiter);
            
            foreach($data['daftarFasilitas'] as $key => $f) {
                fputcsv($file, [
                    $key + 1,
                    $f->nama_fasilitas,
                    $f->jumlah . ' Unit'
                ], $delimiter);
            }
            fputcsv($file, ['', 'TOTAL KESELURUHAN UNIT', $data['totalStok'] . ' Unit'], $delimiter);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
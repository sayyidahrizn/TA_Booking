<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    private function getLaporanData(Request $request, $isExport = false)
    {
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        // DEFAULT SEMUA
        $jenis  = $request->jenis ?? 'semua';
        $status = $request->status;

        // =========================================
        // QUERY DASAR
        // =========================================

        $query = Penyewaan::with([
            'user',
            'fasilitas'
        ]);

        // =========================================
        // FILTER TANGGAL
        // =========================================

        if ($startDate && $endDate) {

            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }

        // =========================================
        // FILTER STATUS
        // =========================================

        if ($status) {

            if ($jenis == 'penyewaan') {

                $query->where('status_sewa', $status);

            } elseif ($jenis == 'pembayaran') {

                $query->where('status_pembayaran', $status);

            } elseif ($jenis == 'pengembalian') {

                $query->where('status_pengembalian', $status);
            }
        }

        // =========================================
        // AMBIL DATA
        // =========================================

        if ($isExport) {

            // UNTUK PDF & EXCEL
            $detailLaporan = $query
                ->latest()
                ->get();

        } else {

            // UNTUK HALAMAN ADMIN
            $detailLaporan = $query
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        // =========================================
        // PERIODE
        // =========================================

        $periodeTeks = "Semua Waktu";

        if ($startDate && $endDate) {

            $periodeTeks =
                Carbon::parse($startDate)->translatedFormat('d F Y')
                . ' s/d ' .
                Carbon::parse($endDate)->translatedFormat('d F Y');
        }

        // =========================================
        // WAKTU CETAK
        // =========================================

        $tglCetak = Carbon::now('Asia/Jakarta')
            ->translatedFormat('d F Y');

        $waktuCetak = Carbon::now('Asia/Jakarta')
            ->format('H:i');

        return compact(
            'detailLaporan',
            'jenis',
            'status',
            'startDate',
            'endDate',
            'periodeTeks',
            'tglCetak',
            'waktuCetak'
        );
    }

    // =========================================
    // HALAMAN LAPORAN
    // =========================================

    public function index(Request $request)
    {
        return view(
            'admin.laporan.index',
            $this->getLaporanData($request)
        );
    }

    // =========================================
    // DOWNLOAD PDF
    // =========================================

    public function downloadPDF(Request $request)
    {
        // TRUE = AMBIL SEMUA DATA TANPA PAGINATION
        $data = $this->getLaporanData($request, true);

        $pdf = Pdf::loadView(
            'admin.laporan.pdf',
            $data
        )->setPaper('a4', 'landscape');

        $namaFile =
            'Laporan-' .
            ucfirst($request->jenis ?? 'semua') .
            '-' .
            Carbon::now('Asia/Jakarta')->format('d-m-Y-H-i')
            . '.pdf';

        return $pdf->download($namaFile);
    }

    // =========================================
    // DOWNLOAD EXCEL / CSV
    // =========================================

    public function downloadExcel(Request $request)
    {
        // TRUE = AMBIL SEMUA DATA TANPA PAGINATION
        $data = $this->getLaporanData($request, true);

        $fileName =
            'Laporan-' .
            ucfirst($request->jenis ?? 'semua') .
            '-' .
            date('d-m-Y')
            . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate",
            "Expires"             => "0"
        ];

        $callback = function () use ($data) {

            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $delimiter = ";";

            fputcsv($file, [
                'LAPORAN ' . strtoupper($data['jenis'])
            ], $delimiter);

            fputcsv($file, [
                'Periode',
                $data['periodeTeks']
            ], $delimiter);

            fputcsv($file, [
                'Tanggal Cetak',
                $data['tglCetak'] . ' ' .
                $data['waktuCetak'] . ' WIB'
            ], $delimiter);

            fputcsv($file, [], $delimiter);

            fputcsv($file, [
                'NO',
                'KODE BOOKING',
                'PENYEWA',
                'FASILITAS',
                'JUMLAH',
                'TOTAL',
                'STATUS SEWA',
                'STATUS PEMBAYARAN',
                'STATUS PENGEMBALIAN',
                'TANGGAL'
            ], $delimiter);

            foreach ($data['detailLaporan'] as $key => $item) {

                fputcsv($file, [

                    $key + 1,

                    $item->kode_booking,

                    $item->user->name ?? '-',

                    $item->fasilitas->nama_fasilitas ?? '-',

                    $item->jumlah_sewa,

                    'Rp ' . number_format(
                        $item->total_harga,
                        0,
                        ',',
                        '.'
                    ),

                    ucfirst($item->status_sewa ?? '-'),

                    ucfirst($item->status_pembayaran ?? '-'),

                    ucfirst($item->status_pengembalian ?? '-'),

                    $item->created_at->format('d-m-Y')

                ], $delimiter);
            }

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }
}
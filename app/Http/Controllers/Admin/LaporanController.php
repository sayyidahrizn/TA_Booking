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
        $jenis     = $request->jenis ?? 'semua';
        $status    = $request->status;

        $query = Penyewaan::with(['user', 'fasilitas', 'denda']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }

        if ($jenis == 'denda') {
            $query->whereHas('denda');
        }

        if ($status) {
            if ($jenis == 'denda') {
                if ($status == 'denda_belum_bayar') {
                    $query->whereHas('denda', function ($q) {
                        $q->where('status_denda', '!=', 'lunas');
                    });
                } elseif ($status == 'denda_lunas') {
                    $query->whereHas('denda', function ($q) {
                        $q->where('status_denda', 'lunas');
                    });
                }
            } else {
                $query->where('status_sewa', $status);
            }
        }

        if ($isExport) {
            $detailLaporan = $query->latest()->get();
        } else {
            $detailLaporan = $query->latest()->paginate(10)->withQueryString();
        }

        $periodeTeks = ($startDate && $endDate) 
            ? Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y')
            : "Semua Waktu";

        $tglCetak = Carbon::now('Asia/Jakarta')->translatedFormat('d F Y');
        $waktuCetak = Carbon::now('Asia/Jakarta')->format('H:i');

        return compact(
            'detailLaporan', 'jenis', 'status', 'startDate', 
            'endDate', 'periodeTeks', 'tglCetak', 'waktuCetak'
        );
    }

    public function index(Request $request)
    {
        return view('admin.laporan.index', $this->getLaporanData($request));
    }

    public function downloadPDF(Request $request)
    {
        $data = $this->getLaporanData($request, true);
        $pdf = Pdf::loadView('admin.laporan.pdf', $data)->setPaper('a4', 'landscape');
        $namaFile = 'Laporan-'.ucfirst($request->jenis ?? 'semua').'-'.Carbon::now()->format('d-m-Y-H-i').'.pdf';
        return $pdf->download($namaFile);
    }

    public function sewa(Request $request)
    {
        $request->merge(['jenis' => 'sewa']);
        $data = $this->getLaporanData($request);
        $startDate = $request->start_date ?? '1970-01-01';
        $endDate   = $request->end_date ?? now()->format('Y-m-d');

        $data['totalPemasukan'] = Penyewaan::whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ])->sum('total_harga');

        return view('admin.laporan.sewa', $data);
    }

    public function denda(Request $request)
    {
        $request->merge(['jenis' => 'denda']);
        $data = $this->getLaporanData($request);
        
        $data['totalDenda'] = Penyewaan::whereHas('denda')
            ->whereBetween('created_at', [
                ($request->start_date ?? '1970-01-01') . ' 00:00:00',
                ($request->end_date ?? now()->format('Y-m-d')) . ' 23:59:59'
            ])
            ->get()
            ->sum(function($item) {
                // Akumulasi total_denda dari semua denda yang ada di koleksi
                return $item->denda->sum('total_denda');
            });

        return view('admin.laporan.denda', $data);
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->getLaporanData($request, true);
        $fileName = 'Laporan-'.ucfirst($request->jenis ?? 'semua').'-'.date('d-m-Y').'.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate",
            "Expires"             => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $delimiter = ";";

            fputcsv($file, ['LAPORAN ' . strtoupper($data['jenis'])], $delimiter);
            fputcsv($file, ['Periode', $data['periodeTeks']], $delimiter);
            fputcsv($file, ['Tanggal Cetak', $data['tglCetak'] . ' WIB'], $delimiter);
            fputcsv($file, [], $delimiter);

            fputcsv($file, [
                'NO', 'KODE BOOKING', 'PENYEWA', 'FASILITAS', 'JUMLAH SEWA', 'TOTAL HARGA',
                'STATUS SEWA', 'KONDISI BARANG', 'JUMLAH DENDA', 'ALASAN DENDA',
                'CATATAN DENDA', 'STATUS PEMBAYARAN DENDA', 'TANGGAL'
            ], $delimiter);

            foreach ($data['detailLaporan'] as $key => $item) {
                // Menggabungkan data denda dari HasMany
                $totalNominal = $item->denda->sum('total_denda');
                $jenisDenda   = $item->denda->pluck('jenis_kerusakan')->filter()->implode(', ');
                $alasanDenda  = $item->denda->pluck('keterangan_kerusakan')->filter()->implode(' | ');
                $isLunas      = !$item->denda->contains('status_denda', 'belum_bayar');

                fputcsv($file, [
                    $key + 1,
                    $item->kode_booking,
                    $item->user->name ?? '-',
                    $item->fasilitas->nama_fasilitas ?? '-',
                    $item->jumlah_sewa,
                    'Rp ' . number_format($item->total_harga, 0, ',', '.'),
                    ucfirst($item->status_sewa ?? '-'),
                    $jenisDenda ?: '-',
                    'Rp ' . number_format($totalNominal, 0, ',', '.'),
                    $alasanDenda ?: '-',
                    '-',
                    $isLunas ? 'Lunas' : 'Ada Tunggakan',
                    $item->created_at->format('d-m-Y')
                ], $delimiter);
            }
            fclose($file);
        };

        if (ob_get_level() > 0) ob_end_clean();
        return response()->stream($callback, 200, $headers);
    }
}
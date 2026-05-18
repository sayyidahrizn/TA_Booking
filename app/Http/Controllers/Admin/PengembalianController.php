<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'penyewaan.denda'
            ])
            ->latest()
            ->get();

        // Hitung otomatis denda keterlambatan untuk tampilan preview admin
        $pengembalian->each(function ($item) {
            $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
            $tglKembali = Carbon::parse($item->tanggal_pengembalian)->startOfDay();

            $item->hari_telat = 0;
            $item->denda_telat_otomatis = 0;

            if ($tglKembali->gt($deadline)) {
                $hariTelat = $deadline->diffInDays($tglKembali);
                $item->hari_telat = $hariTelat;
                $item->denda_telat_otomatis = $hariTelat * 10000;
            }
        });

        $data = $pengembalian->groupBy(function ($item) {
            return $item->penyewaan->kode_penyewaan ?? 'TANPA-KODE';
        });

        return view('admin.pengembalian.index', compact('data'));
    }

    /**
     * VALIDASI PENGEMBALIAN
     */
    public function validasi(Request $request)
    {
        $request->validate([
            'jenis_kerusakan'   => 'required|array',
            'denda_rusak'       => 'required|array',
            'catatan_admin'     => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->jenis_kerusakan as $idPengembalian => $jenisKerusakan) {

                $item = Pengembalian::with([
                    'penyewaan.fasilitas',
                    'penyewaan.denda'
                ])->findOrFail($idPengembalian);

                /*
                |--------------------------------------------------------------------------
                | HITUNG DENDA KETERLAMBATAN
                |--------------------------------------------------------------------------
                */

                $deadline = Carbon::parse(
                    $item->penyewaan->tgl_selesai
                )->startOfDay();

                $tglKembali = Carbon::parse(
                    $item->tanggal_pengembalian
                )->startOfDay();

                $hariTelat = 0;
                $dendaTelat = 0;

                if ($tglKembali->gt($deadline)) {

                    $hariTelat = $deadline->diffInDays($tglKembali);

                    $dendaTelat = $hariTelat * 10000;
                }

                /*
                |--------------------------------------------------------------------------
                | HITUNG DENDA KERUSAKAN
                |--------------------------------------------------------------------------
                */

                $biayaRusak = 0;

                $inputDendaRusak =
                    $request->denda_rusak[$idPengembalian] ?? 0;

                if ($jenisKerusakan == 'ringan') {

                    $biayaRusak = (int) $inputDendaRusak;

                } elseif ($jenisKerusakan == 'berat') {

                    $biayaRusak =
                        $item->penyewaan->fasilitas->harga ?? 0;
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL DENDA
                |--------------------------------------------------------------------------
                */

                $totalDenda = $dendaTelat + $biayaRusak;

                $catatan =
                    $request->catatan_admin[$idPengembalian] ?? null;

                /*
                |--------------------------------------------------------------------------
                | UPDATE STATUS PENGEMBALIAN
                |--------------------------------------------------------------------------
                */

                $item->update([
                    'status_validasi' => 'disetujui',
                    'catatan_admin'   => $catatan,
                ]);

                /*
                |--------------------------------------------------------------------------
                | JIKA ADA DENDA
                |--------------------------------------------------------------------------
                */

                if ($totalDenda > 0) {

                    Denda::updateOrCreate(

                        [
                            'id_penyewaan' => $item->id_penyewaan
                        ],

                        [
                            'jenis_kerusakan'      => $jenisKerusakan,
                            'biaya_keterlambatan'  => $dendaTelat,
                            'biaya_kerusakan'      => $biayaRusak,
                            'total_denda'          => $totalDenda,
                            'keterangan_kerusakan' => $catatan,
                            'status_denda'         => 'belum_bayar',
                        ]
                    );

                    $item->penyewaan->update([
                        'status_sewa' => 'menunggu_pembayaran_denda'
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | TANPA DENDA
                    |--------------------------------------------------------------------------
                    */

                    $item->penyewaan->update([
                        'status_sewa' => 'selesai'
                    ]);
                }
            }

            DB::commit();

            return back()->with(
                'success',
                'Validasi pengembalian berhasil!'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Terjadi kesalahan: ' . $e->getMessage()
            );
        }
    }

    public function konfirmasiPembayaran($id)
    {
        DB::beginTransaction();
        try {
            $denda = Denda::with('penyewaan')->findOrFail($id);
            $denda->update(['status_denda' => 'lunas']);

            if ($denda->penyewaan) {
                $denda->penyewaan->update(['status_sewa' => 'selesai']);
            }

            DB::commit();
            return back()->with('success', 'Pembayaran denda berhasil dikonfirmasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
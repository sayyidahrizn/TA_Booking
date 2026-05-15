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
            'jenis_kerusakan.*' => 'required',
            'denda_rusak.*'     => 'nullable|numeric|min:0',
            'catatan_admin.*'   => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            if ($request->denda_rusak) {
                foreach ($request->denda_rusak as $id => $dendaRusak) {
                    $item = Pengembalian::with(['penyewaan.fasilitas'])->findOrFail($id);

                    // HITUNG DENDA TELAT (FIXED LOGIC)
                    $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
                    $tglKembali = Carbon::parse($item->tanggal_pengembalian)->startOfDay();
                    $dendaTelat = 0;

                    if ($tglKembali->gt($deadline)) {
                        $hariTelat = $deadline->diffInDays($tglKembali);
                        $dendaTelat = $hariTelat * 10000;
                    }

                    // HITUNG BIAYA RUSAK
                    $jenisKerusakan = $request->jenis_kerusakan[$id] ?? 'tidak_rusak';
                    $biayaRusak = 0;

                    if ($jenisKerusakan == 'ringan') {
                        $biayaRusak = (float)($dendaRusak ?? 0);
                    } elseif ($jenisKerusakan == 'berat') {
                        $biayaRusak = $item->penyewaan->fasilitas->harga ?? 0;
                    }

                    $totalDenda = $dendaTelat + $biayaRusak;
                    $catatan = $request->catatan_admin[$id] ?? null;

                    // UPDATE STATUS PENGEMBALIAN
                    $item->update([
                        'status_validasi' => 'disetujui',
                        'catatan_admin'   => $catatan,
                    ]);

                    // SIMPAN KE TABEL DENDA
                    if ($totalDenda > 0) {
                        Denda::updateOrCreate(
                            ['id_penyewaan' => $item->id_penyewaan],
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
                        $item->penyewaan->update([
                            'status_sewa' => 'selesai'
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Validasi pengembalian berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
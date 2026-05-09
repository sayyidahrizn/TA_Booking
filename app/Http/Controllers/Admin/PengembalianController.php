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
    public function index()
    {
        $data = Pengembalian::with(['penyewaan.fasilitas', 'penyewaan.user'])
            ->where('status_validasi', 'pending')
            ->get()
            ->map(function($item) {
                $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
                $tgl_kembali_user = Carbon::parse($item->tanggal_pengembalian)->startOfDay();
                
                $item->hari_telat = 0;
                $item->denda_telat_otomatis = 0;
                
                if ($tgl_kembali_user->gt($deadline)) {
                    $item->hari_telat = $deadline->diffInDays($tgl_kembali_user);
                    $item->denda_telat_otomatis = $item->hari_telat * 10000; 
                }
                return $item;
            })
            ->groupBy(function($item) {
                return Carbon::parse($item->penyewaan->tgl_mulai)->format('Y-m-d');
            });

        return view('admin.pengembalian.index', compact('data'));
    }

    public function validasi(Request $request)
    {
        $request->validate([
            'denda_rusak.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->denda_rusak as $id => $denda_rusak) {
                $item = Pengembalian::with('penyewaan')->findOrFail($id);
                
                $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
                $tgl_kembali_user = Carbon::parse($item->tanggal_pengembalian)->startOfDay();
                
                $denda_telat = $tgl_kembali_user->gt($deadline) ? $deadline->diffInDays($tgl_kembali_user) * 10000 : 0;
                $total_denda = $denda_telat + (float)($denda_rusak ?? 0);

                // Update Pengembalian
                $item->update([
                    'status_validasi' => 'disetujui',
                    'catatan_admin' => $request->catatan_admin[$id] ?? null,
                ]);

                // Simpan ke tabel Denda
                if ($total_denda > 0) {
                    Denda::create([
                        'id_penyewaan' => $item->id_penyewaan,
                        'biaya_keterlambatan' => $denda_telat,
                        'biaya_kerusakan' => (float)($denda_rusak ?? 0),
                        'total_denda' => $total_denda,
                        'keterangan_kerusakan' => $request->catatan_admin[$id] ?? null,
                        'status_denda' => 'belum_bayar',
                    ]);
                }

                // tentukan status penyewaan
                $statusSewa = $total_denda > 0
                    ? 'menunggu_pembayaran_denda'
                    : 'selesai';

                // Update status penyewaan utama
                $item->penyewaan->update([
                    'status_sewa' => $statusSewa
                ]);
            }

            DB::commit();
            return back()->with('success', 'Validasi Berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Kesalahan: ' . $e->getMessage());
        }
    }
}
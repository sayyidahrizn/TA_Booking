<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
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
                // Deadline (Tgl Selesai Sewa)
                $deadline = Carbon::parse($item->penyewaan->tgl_selesai)->startOfDay();
                // Realisasi (Tgl User Kembalikan)
                $tgl_kembali_user = Carbon::parse($item->tanggal_pengembalian)->startOfDay();
                
                $item->hari_telat = 0;
                $item->denda_telat_otomatis = 0;
                
                // Jika tanggal kembali lebih besar dari deadline, berarti TELAT
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
                
                $denda_telat = 0;
                if ($tgl_kembali_user->gt($deadline)) {
                    $hari = $deadline->diffInDays($tgl_kembali_user);
                    $denda_telat = $hari * 10000;
                }

                $denda_rusak_val = (float)($denda_rusak ?? 0);
                $total_denda = $denda_telat + $denda_rusak_val;
                $catatan = $request->catatan_admin[$id] ?? null;

                // Update Tabel Pengembalian
                $item->update([
                    'status_validasi' => 'disetujui',
                    'denda_telat' => $denda_telat,
                    'denda_rusak' => $denda_rusak_val,
                    'total_denda' => $total_denda,
                    'catatan_admin' => $catatan,
                    'status_pembayaran_denda' => $total_denda > 0 ? 'pending' : 'lunas',
                ]);

                // Update Status di Tabel Penyewaan
                if ($total_denda > 0) {
                    // Jika ada denda, status gantung agar user bayar dulu
                    $item->penyewaan->update(['status_pengembalian' => 'denda_pending']);
                } else {
                    // Jika denda 0, langsung lunas/selesai
                    $item->penyewaan->update(['status_pengembalian' => 'selesai']);
                }
            }

            DB::commit();
            return back()->with('success', 'Validasi Berhasil Disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Kesalahan: ' . $e->getMessage());
        }
    }
}
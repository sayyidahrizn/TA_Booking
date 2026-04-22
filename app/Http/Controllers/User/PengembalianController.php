<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        // Data untuk form pengembalian (yang belum kembali)
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->where('status_sewa', 'disetujui')
            ->where('status_pengembalian', 'belum')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->tgl_mulai)->format('Y-m-d');
            });

        // Data denda yang harus dibayar (fitur baru hasil validasi admin)
        $denda_tunggakan = Pengembalian::with('penyewaan.fasilitas')
            ->whereHas('penyewaan', function($q) {
                $q->where('id_user', Auth::id());
            })
            ->where('status_pembayaran_denda', 'pending')
            ->get();

        return view('user.pengembalian.index', compact('penyewaan', 'denda_tunggakan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_penyewaan' => 'required|array|min:1',
            'bukti_pengembalian' => 'required|array',
            'bukti_pengembalian.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'id_penyewaan.required' => 'Silakan pilih fasilitas yang akan dikembalikan.',
            'bukti_pengembalian.*.image' => 'File bukti harus berupa gambar.',
            'bukti_pengembalian.*.max' => 'Ukuran gambar maksimal adalah 5MB.',
        ]);

        $count = 0;
        DB::beginTransaction();

        try {
            foreach ($request->id_penyewaan as $id) {
                if (!$request->hasFile("bukti_pengembalian.$id")) {
                    throw new Exception("Bukti foto untuk salah satu fasilitas belum diunggah.");
                }

                $penyewaan = Penyewaan::where('id_penyewaan', $id)
                    ->where('id_user', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if (!$penyewaan) {
                    throw new Exception("Data penyewaan tidak ditemukan.");
                }

                if ($penyewaan->sisa_pembayaran <= 0 && $penyewaan->status_pengembalian == 'belum') {
                    $file = $request->file("bukti_pengembalian.$id")->store('pengembalian', 'public');

                    Pengembalian::create([
                        'id_penyewaan' => $penyewaan->id_penyewaan,
                        'tanggal_pengembalian' => now(),
                        'bukti_pengembalian' => $file,
                        'status_validasi' => 'pending',
                        'status_pembayaran_denda' => null 
                    ]);

                    $penyewaan->update(['status_pengembalian' => 'proses']);
                    $count++;
                } else {
                    throw new Exception("Fasilitas {$penyewaan->fasilitas->nama_fasilitas} belum lunas.");
                }
            }

            if ($count > 0) {
                DB::commit();
                return redirect()->route('user.pengembalian')
                                 ->with('success', "$count Fasilitas berhasil diajukan.");
            }
            throw new Exception("Tidak ada data yang diproses.");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function bayarDenda($id)
    {
        $pengembalian = Pengembalian::with(['penyewaan.fasilitas', 'penyewaan.user'])->findOrFail($id);
        
        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Cek jika snap token sudah ada agar tidak generate ulang (mencegah Duplicate Order ID)
        if ($pengembalian->snap_token_denda) {
            $snapToken = $pengembalian->snap_token_denda;
        } else {
            $params = [
                'transaction_details' => [
                    'order_id' => 'DENDA-' . $pengembalian->id . '-' . time(),
                    'gross_amount' => (int) $pengembalian->total_denda,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'item_details' => [
                    [
                        'id' => 'DND-' . $pengembalian->id,
                        'price' => (int) $pengembalian->total_denda,
                        'quantity' => 1,
                        'name' => 'Denda Fasilitas: ' . $pengembalian->penyewaan->fasilitas->nama_fasilitas,
                    ]
                ]
            ];

            try {
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                $pengembalian->update(['snap_token_denda' => $snapToken]);
            } catch (Exception $e) {
                return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
            }
        }

        return view('user.pengembalian.bayar', compact('pengembalian', 'snapToken'));
    }
}
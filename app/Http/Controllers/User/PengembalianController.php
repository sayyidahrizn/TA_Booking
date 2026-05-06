<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use App\Models\Denda;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Ambil data penyewaan yang sudah disetujui tapi BELUM ada di tabel pengembalian
        $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran'])
            ->where('id_user', $userId)
            ->where('status_sewa', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->get()
            ->map(function($item) {
                // Hitung total pembayaran yang sukses
                $totalBayar = $item->pembayaran->whereIn('status_pembayaran', ['pending', 'berhasil'])->sum('jumlah_bayar');
                
                // Logika Sisa Bayar: Total Harga - Total Bayar
                $sisaRaw = $item->total_harga - $totalBayar;

                // FIX: Jika sisa kurang dari 1 rupiah (karena selisih desimal/admin), anggap 0 (Lunas)
                $item->sisa_pembayaran = ($sisaRaw < 1) ? 0 : $sisaRaw;

                // Cek apakah sudah melewati tanggal selesai
                $item->sudah_waktunya_kembali = Carbon::now()->greaterThanOrEqualTo(Carbon::parse($item->tgl_selesai));
                
                return $item;
            })
            ->groupBy(function($item) {
                return Carbon::parse($item->tgl_mulai)->format('Y-m-d');
            });

        // 2. Ambil data denda yang belum dibayar
        $denda_tunggakan = Denda::with('penyewaan.fasilitas')
            ->whereHas('penyewaan', function($q) use ($userId) {
                $q->where('id_user', $userId);
            })
            ->where('status_denda', 'belum_bayar')
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

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->id_penyewaan as $id) {
                if (!$request->hasFile("bukti_pengembalian.$id")) {
                    throw new Exception("Bukti foto untuk salah satu fasilitas belum diunggah.");
                }

                $penyewaan = Penyewaan::with('pembayaran')
                    ->where('id_penyewaan', $id)
                    ->where('id_user', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if (!$penyewaan) {
                    throw new Exception("Data penyewaan tidak ditemukan.");
                }

                // Hitung ulang sisa pembayaran secara dinamis
                $totalBayar = $penyewaan->pembayaran->whereIn('status_pembayaran', ['pending', 'berhasil'])->sum('jumlah_bayar');
                $sisa = $penyewaan->total_harga - $totalBayar;

                if ($sisa <= 0) {
                    $file = $request->file("bukti_pengembalian.$id")->store('pengembalian', 'public');

                    Pengembalian::create([
                        'id_penyewaan' => $penyewaan->id_penyewaan,
                        'tanggal_pengembalian' => now(),
                        'bukti_pengembalian' => $file,
                        'status_validasi' => 'pending',
                    ]);

                    $count++;
                } else {
                    throw new Exception("Fasilitas {$penyewaan->fasilitas->nama_fasilitas} belum lunas (Sisa: Rp ".number_format($sisa,0,',','.').").");
                }
            }

            DB::commit();
            return redirect()->route('user.pengembalian')->with('success', "$count Fasilitas berhasil diajukan.");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function bayarDenda($id)
    {
        // Mencari data di tabel Denda
        $denda = Denda::with(['penyewaan.fasilitas', 'penyewaan.user'])->findOrFail($id);
        
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Gunakan snap_token yang sudah ada di tabel denda jika tersedia
        if ($denda->snap_token) {
            $snapToken = $denda->snap_token;
        } else {
            $orderId = 'DENDA-' . $denda->id_denda . '-' . time();
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $denda->total_denda,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'item_details' => [
                    [
                        'id' => 'DND-' . $denda->id_denda,
                        'price' => (int) $denda->total_denda,
                        'quantity' => 1,
                        'name' => 'Denda: ' . $denda->penyewaan->fasilitas->nama_fasilitas,
                    ]
                ]
            ];

            try {
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                $denda->update(['snap_token' => $snapToken]);
            } catch (Exception $e) {
                return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
            }
        }

        return view('user.pengembalian.bayar', compact('denda', 'snapToken'));
    }
}
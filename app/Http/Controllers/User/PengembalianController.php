<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use App\Models\Denda;
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

        // 1. Ambil data penyewaan yang sudah disetujui
        // tapi BELUM ada di tabel pengembalian
        $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran'])
            ->where('id_user', $userId)
            ->where('status_sewa', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->get()
            ->map(function($item) {

                // =========================
                // HITUNG TOTAL PEMBAYARAN
                // =========================
                $totalBayar = $item->pembayaran
                    ->where('status_pembayaran', 'berhasil')
                    ->sum('jumlah_bayar');

                // =========================
                // HITUNG SISA PEMBAYARAN
                // =========================
                $sisaRaw = $item->total_harga - $totalBayar;

                // Jika selisih kecil dianggap lunas
                $item->sisa_pembayaran = ($sisaRaw < 1) ? 0 : $sisaRaw;

                // =========================
                // GABUNGKAN TANGGAL & JAM
                // =========================
                $waktuSelesai = Carbon::parse(
                    $item->tgl_selesai . ' ' . $item->jam_selesai
                );

                // =========================
                // STATUS BOLEH KEMBALI
                // =========================
                // Pengembalian baru bisa setelah waktu selesai
                $item->sudah_boleh_kembali = Carbon::now()->greaterThanOrEqualTo($waktuSelesai);

                // =========================
                // BATAS TANPA DENDA
                // =========================
                // Toleransi 12 jam
                $item->batas_tanpa_denda = $waktuSelesai->copy()->addHours(12);

                // =========================
                // STATUS TERLAMBAT
                // =========================
                $item->terlambat = Carbon::now()->greaterThan($item->batas_tanpa_denda);

                return $item;
            })
            ->groupBy(function($item) {
                return Carbon::parse($item->tgl_mulai)->format('Y-m-d');
            });

        // =========================
        // AMBIL DENDA BELUM DIBAYAR
        // =========================
        $denda_tunggakan = Denda::with('penyewaan.fasilitas')
            ->whereHas('penyewaan', function($q) use ($userId) {
                $q->where('id_user', $userId);
            })
            ->where('status_denda', 'belum_bayar')
            ->get();

        return view(
            'user.pengembalian.index',
            compact('penyewaan', 'denda_tunggakan')
        );
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
                // VALIDASI FOTO
                if (!$request->hasFile("bukti_pengembalian.$id")) {
                    throw new Exception("Bukti foto untuk salah satu fasilitas belum diunggah.");
                }

                // AMBIL DATA PENYEWAAN
                $penyewaan = Penyewaan::with(['pembayaran', 'fasilitas'])
                    ->where('id_penyewaan', $id)
                    ->where('id_user', Auth::id())
                    ->lockForUpdate()
                    ->first();

                if (!$penyewaan) {
                    throw new Exception("Data penyewaan tidak ditemukan.");
                }

                // CEK WAKTU SELESAI
                $waktuSelesai = Carbon::parse($penyewaan->tgl_selesai . ' ' . $penyewaan->jam_selesai);

                if (Carbon::now()->lt($waktuSelesai)) {
                    throw new Exception("Fasilitas {$penyewaan->fasilitas->nama_fasilitas} belum bisa dikembalikan karena waktu penyewaan belum selesai.");
                }

                // CEK PEMBAYARAN
                $totalBayar = $penyewaan->pembayaran
                    ->where('status_pembayaran', 'berhasil')
                    ->sum('jumlah_bayar');

                $sisa = $penyewaan->total_harga - $totalBayar;

                if ($sisa > 0) {
                    throw new Exception("Fasilitas {$penyewaan->fasilitas->nama_fasilitas} belum lunas. Sisa pembayaran Rp " . number_format($sisa, 0, ',', '.'));
                }

                // CEK DUPLIKAT
                $cekPengembalian = Pengembalian::where('id_penyewaan', $penyewaan->id_penyewaan)->first();
                if ($cekPengembalian) {
                    throw new Exception("Fasilitas {$penyewaan->fasilitas->nama_fasilitas} sudah diajukan pengembalian.");
                }

                // UPLOAD FOTO
                $file = $request->file("bukti_pengembalian.$id")->store('pengembalian', 'public');

                // SIMPAN PENGEMBALIAN
                Pengembalian::create([
                    'id_penyewaan' => $penyewaan->id_penyewaan,
                    'tanggal_pengembalian' => now(),
                    'bukti_pengembalian' => $file,
                    'status_validasi' => 'pending',
                ]);

                $penyewaan->update([
                    'status_sewa' => 'menunggu_validasi_pengembalian'
                ]);

                $count++;
            }

            DB::commit();
            return redirect()->route('user.pengembalian')->with('success', "$count fasilitas berhasil diajukan pengembalian.");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function bayarDenda($id)
    {
        // =========================================
        // AMBIL DATA DENDA (Lengkap dengan Pengembalian untuk hitung hari)
        // =========================================
        $denda = Denda::with([
            'penyewaan.fasilitas',
            'penyewaan.user',
            'penyewaan.pengembalian' 
        ])->findOrFail($id);

        // =========================================
        // KONFIGURASI MIDTRANS
        // =========================================
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        if ($denda->status_denda == 'lunas') {
            return redirect()->route('user.pengembalian')->with('success', 'Denda sudah dibayar.');
        }

        // =========================================
        // JIKA BELUM ADA SNAP TOKEN, GENERATE BARU
        // =========================================
        if (!$denda->snap_token) {
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
                        'name' => 'Denda ' . $denda->penyewaan->fasilitas->nama_fasilitas,
                    ]
                ],
                'callbacks' => [
                    'finish' => route('user.pengembalian')
                ]
            ];

            try {
                $snapToken = \Midtrans\Snap::getSnapToken($params);
                $denda->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
            }
        } else {
            $snapToken = $denda->snap_token;
        }

        return view('user.pengembalian.bayar_denda', compact('denda', 'snapToken'));
    }
}
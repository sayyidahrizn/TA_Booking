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
use Illuminate\Validation\Rule;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran'])
            ->where('id_user', $userId)
            ->where('status_sewa', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->orderBy('tgl_mulai', 'desc')
            ->paginate(10);
            
        $penyewaan->getCollection()->transform(function($item){

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
            });
            $data = $penyewaan->getCollection()->groupBy('kode_booking');

        // =========================
        // AMBIL DENDA BELUM DIBAYAR
        // =========================
        $denda_tunggakan = Denda::with('penyewaan.fasilitas')
            ->whereHas('penyewaan', function($q) use ($userId) {
                $q->where('id_user', $userId);
            })
            ->whereIn('status_denda', ['belum_bayar', 'pending', 'menunggu_pembayaran', 'pending_tunai'])
            ->get()
            ->groupBy(function ($item) {
                return $item->penyewaan->kode_booking;
            });

        return view(
            'user.pengembalian.index',
            [
                'data' => $data,
                'pagination' => $penyewaan,
                'denda_tunggakan' => $denda_tunggakan
            ]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_group'        => 'required',
            'id_penyewaan'         => 'required|array|min:1',
            'id_penyewaan.*'       => ['integer', Rule::exists(Penyewaan::class, 'id_penyewaan')],
            'bukti_pengembalian'   => 'required|array',
            'bukti_pengembalian.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->id_penyewaan as $id) {

                // Cek bukti ada
                if (!$request->hasFile("bukti_pengembalian.{$id}")) {
                    throw new Exception("Bukti pengembalian untuk fasilitas ID {$id} belum diupload.");
                }

                // Ambil data penyewaan milik user
                $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran'])
                    ->where('id_penyewaan', $id)
                    ->where('id_user', Auth::id())
                    ->where('status_sewa', 'disetujui')
                    ->firstOrFail();

                // Pastikan lunas
                $totalBayar = $penyewaan->pembayaran
                    ->where('status_pembayaran', 'berhasil')
                    ->sum('jumlah_bayar');

                $sisa = $penyewaan->total_harga - $totalBayar;
                if ($sisa >= 1) {
                    throw new Exception(
                        "Pembayaran untuk fasilitas {$penyewaan->fasilitas->nama_fasilitas} belum lunas."
                    );
                }

                // Upload bukti
                $file = $request->file("bukti_pengembalian.{$id}")
                    ->store('pengembalian', 'public');

                // Insert 1 baris pengembalian per fasilitas
                Pengembalian::create([
                    'id_penyewaan'         => $id,
                    'id_user'              => Auth::id(),
                    'tanggal_pengembalian' => now(),
                    'bukti_pengembalian'   => $file,
                    'status_validasi'      => 'pending',
                ]);

                // Update status penyewaan
                $penyewaan->update([
                    'status_sewa' => 'menunggu_validasi_pengembalian',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('user.pengembalian')
                ->with('success', 'Pengajuan pengembalian berhasil dikirim.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function bayarDenda($id)
    {
        $denda = Denda::with([
            'penyewaan.fasilitas',
            'penyewaan.user'
        ])->findOrFail($id);

        if (str_starts_with((string) $denda->kode_pembayaran, 'TUNAI-REQ-')) {
            return redirect()->route('user.pengembalian')
                ->with('success', 'Pembayaran tunai sedang menunggu verifikasi admin.');
        }

        // ❌ kalau sudah lunas jangan lanjut
        if ($denda->status_denda == 'lunas') {
            return redirect()->route('user.pengembalian')
                ->with('success', 'Denda sudah dibayar.');
        }

        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // 👉 selalu generate ulang atau pakai yang ada
        $orderId = $denda->kode_pembayaran ?? 'DENDA-' . $denda->id_denda . '-' . time();

        if (!$denda->snap_token) {
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
                        'id' => 'DENDA-' . $denda->id_denda,
                        'price' => (int) $denda->total_denda,
                        'quantity' => 1,
                        'name' => 'Denda - ' . $denda->penyewaan->fasilitas->nama_fasilitas,
                    ]
                ],
                'callbacks' => [
                    'finish' => route('user.pengembalian')
                ]
            ];

            try {
                $snapToken = \Midtrans\Snap::getSnapToken($params);

                $denda->update([
                    'snap_token' => $snapToken,
                    'kode_pembayaran' => $orderId,
                ]);

            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
            }
        } else {
            $snapToken = $denda->snap_token;
        }

        return view('user.pengembalian.bayar_denda', compact('denda', 'snapToken'));
    }

    public function tunaiDenda($id)
    {
        $denda = Denda::findOrFail($id);

        $denda->update([
            'status_denda' => 'belum_bayar',
            'kode_pembayaran' => 'TUNAI-REQ-' . $denda->id_denda . '-' . time(),
        ]);

        return redirect()
            ->route('user.pengembalian')
            ->with('success', 'Permintaan pembayaran tunai dikirim. Menunggu verifikasi admin.');
    }
}

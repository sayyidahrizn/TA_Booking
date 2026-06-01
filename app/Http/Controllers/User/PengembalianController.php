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
use Illuminate\Validation\Rule;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $now = Carbon::now();

        // 1. Ambil data penyewaan dengan relasi yang dibutuhkan
        $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran', 'pengembalian'])
            ->where('id_user', $userId)
            ->where('status_sewa', 'disetujui')
            ->whereDoesntHave('pengembalian')
            ->orderBy('tgl_mulai', 'desc')
            ->paginate(10);

        // 2. Transformasi koleksi
        $penyewaan->getCollection()->transform(function ($item) use ($now) {
            $kodeBooking = $item->kode_booking;

            // Hitung Total Tagihan & Pembayaran (Saran: Sebaiknya buat method di Model)
            $totalTagihanBooking = Penyewaan::where('kode_booking', $kodeBooking)->sum('total_harga');
            
            $totalBayarBooking = Pembayaran::whereHas('penyewaan', fn($q) => $q->where('kode_booking', $kodeBooking))
                ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
                ->sum('jumlah_bayar');

            // Logic Tanggal & Status
            $waktuSelesai = Carbon::parse($item->tgl_selesai);
            $batasTanpaDenda = $waktuSelesai->copy()->addHours(12);

            // Tambahkan atribut custom ke object
            $item->sisa_pembayaran      = max($totalTagihanBooking - $totalBayarBooking, 0);
            $item->sudah_boleh_kembali  = $now->greaterThanOrEqualTo($waktuSelesai);
            $item->batas_tanpa_denda    = $batasTanpaDenda;
            $item->terlambat            = $now->greaterThan($batasTanpaDenda);

            return $item;
        });

        // 3. Kelompokkan berdasarkan kode_booking
        $dataGrouped = $penyewaan->getCollection()->groupBy('kode_booking');

        // 4. Ambil denda yang belum lunas
        $dendaTunggakan = Denda::with(['penyewaan.fasilitas'])
            ->whereHas('penyewaan', fn($q) => $q->where('id_user', $userId))
            ->whereIn('status_denda', ['belum_bayar', 'pending', 'menunggu_pembayaran', 'pending_tunai'])
            ->get()
            ->groupBy(fn($item) => $item->penyewaan->kode_booking);

        return view('user.pengembalian.index', [
            'data'            => $dataGrouped,
            'pagination'      => $penyewaan,
            'denda_tunggakan' => $dendaTunggakan
        ]);
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
            $userId = Auth::id();

            foreach ($request->id_penyewaan as $id) {
                // 1. Validasi keberadaan file bukti
                if (!$request->hasFile("bukti_pengembalian.{$id}")) {
                    throw new Exception("Bukti pengembalian untuk ID {$id} belum diunggah.");
                }

                // 2. Ambil data penyewaan (Eager Loading untuk performa)
                $penyewaan = Penyewaan::where([
                    ['id_penyewaan', '=', $id],
                    ['id_user', '=', $userId],
                    ['status_sewa', '=', 'disetujui']
                ])->firstOrFail();

                // 3. Cek Status Pembayaran (Total Tagihan vs Total Bayar)
                $kodeBooking = $penyewaan->kode_booking;
                
                $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)->sum('total_harga');
                
                $totalBayar = Pembayaran::whereHas('penyewaan', fn($q) => $q->where('kode_booking', $kodeBooking))
                    ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi'])
                    ->sum('jumlah_bayar');

                $sisa = $totalTagihan - $totalBayar;

                if ($sisa > 0) {
                    throw new Exception("Booking {$kodeBooking} belum lunas. Sisa tagihan: Rp " . number_format($sisa, 0, ',', '.'));
                }

                // 4. Proses Simpan File
                $path = $request->file("bukti_pengembalian.{$id}")->store('pengembalian', 'public');

                // 5. Insert Pengembalian
                Pengembalian::create([
                    'id_penyewaan'         => $id,
                    'id_user'              => $userId,
                    'tanggal_pengembalian' => now(),
                    'bukti_pengembalian'   => $path,
                    'status_validasi'      => 'pending',
                ]);

                // 6. Update Status Penyewaan
                $penyewaan->update([
                    'status_sewa' => 'menunggu_validasi_pengembalian',
                ]);
            }

            DB::commit();
            return redirect()->route('user.pengembalian')
                ->with('success', 'Pengajuan pengembalian berhasil dikirim.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
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

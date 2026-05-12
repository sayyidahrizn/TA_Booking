<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Pembayaran;
use App\Models\Denda;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class PembayaranController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function index($id)
    {
        $penyewaan = Penyewaan::with([
            'fasilitas',
            'pembayaran' => function ($q) {
                $q->where('status_pembayaran', 'berhasil');
            }
        ])
        ->where('id_user', Auth::id())
        ->findOrFail($id);

        $kodeBooking = $penyewaan->kode_booking;

        $semuaFasilitas = Penyewaan::where('kode_booking', $kodeBooking)
            ->with('fasilitas')
            ->get()
            ->pluck('fasilitas.nama_fasilitas')
            ->implode(', ');

        $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)
            ->sum('total_harga');

        $totalBayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                $q->where('kode_booking', $kodeBooking);
            })
            ->where('status_pembayaran', 'berhasil')
            ->sum('jumlah_bayar');

        $sisaTagihan = $totalTagihan - $totalBayar;

        if ($sisaTagihan <= 0) {
            return redirect()->route('user.pengembalian')
                ->with('success', 'Pembayaran sudah lunas.');
        }

        $pembayaran = Pembayaran::where('id_penyewaan', $id)
            ->where('status_pembayaran', 'pending')
            ->first();

        if (!$pembayaran) {
            $pembayaran = Pembayaran::create([
                'id_penyewaan' => $id,
                'kode_pembayaran' => 'TEMP-' . time(),
                'jumlah_bayar' => 0,
                'status_pembayaran' => 'pending',
                'jenis_pembayaran' => 'pelunasan',
            ]);
        }

        return view('user.pembayaran.index', compact(
            'penyewaan',
            'pembayaran',
            'sisaTagihan',
            'totalTagihan',
            'totalBayar',
            'semuaFasilitas'
        ));
    }

    public function proses(Request $request, $id)
    {
        $cleanNominal = str_replace('.', '', $request->nominal_bayar);

        $request->merge([
            'nominal_bayar' => $cleanNominal
        ]);

        $penyewaan = Penyewaan::findOrFail($id);
        $kodeBooking = $penyewaan->kode_booking;

        $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)
            ->sum('total_harga');

        $totalTerbayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                $q->where('kode_booking', $kodeBooking);
            })
            ->where('status_pembayaran', 'berhasil')
            ->sum('jumlah_bayar');

        $sisaTagihan = $totalTagihan - $totalTerbayar;

        if ($totalTerbayar <= 0) {
            $minBayar = round($totalTagihan * 0.5);
            $maxBayar = $totalTagihan;

            $pesanMin = 'Minimal DP Rp ' . number_format($minBayar, 0, ',', '.');
        } else {
            $minBayar = $sisaTagihan;
            $maxBayar = $sisaTagihan;

            $pesanMin = 'Sisa tagihan Rp ' . number_format($sisaTagihan, 0, ',', '.');
        }

        $request->validate([
            'nominal_bayar' => [
                'required',
                'numeric',
                'min:' . $minBayar,
                'max:' . $maxBayar
            ],
        ], [
            'nominal_bayar.required' => 'Nominal wajib diisi',
            'nominal_bayar.min' => $pesanMin,
            'nominal_bayar.max' => 'Nominal terlalu besar'
        ]);

        $pembayaran = Pembayaran::where('id_penyewaan', $id)
            ->where('status_pembayaran', 'pending')
            ->firstOrFail();

        $orderId = 'PAY-' . $kodeBooking . '-' . time();

        try {

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $request->nominal_bayar,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'enabled_payments' => [
                    'credit_card',
                    'bca_va',
                    'bni_va',
                    'bri_va',
                    'gopay',
                    'shopeepay',
                    'other_va'
                ],
                'callbacks' => [
                    'finish' => route('user.pengembalian')
                ]
            ];

            $snapToken = Snap::getSnapToken($params);

            $pembayaran->update([
                'kode_pembayaran' => $orderId,
                'jumlah_bayar' => $request->nominal_bayar,
                'jenis_pembayaran' => ($request->nominal_bayar == $sisaTagihan) ? 'pelunasan' : 'dp',
                'snap_token' => $snapToken,
                'metode_pembayaran' => 'midtrans',
            ]);

            // 🔥 FIX UTAMA DI SINI
            $totalBayar = $totalTerbayar;

            return view('user.pembayaran.index', compact(
                'penyewaan',
                'pembayaran',
                'snapToken',
                'sisaTagihan',
                'totalTagihan',
                'totalBayar',
                'semuaFasilitas'
            ));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $orderId = $request->order_id;

        if (str_contains($orderId, 'PAY-')) {

            $pembayaran = Pembayaran::where('kode_pembayaran', $orderId)->first();

            if ($pembayaran) {

                if (in_array($request->transaction_status, ['settlement', 'capture'])) {
                    $pembayaran->update([
                        'status_pembayaran' => 'berhasil',
                        'tanggal_bayar' => now(),
                    ]);
                }

                if (in_array($request->transaction_status, ['expire', 'cancel', 'deny'])) {
                    $pembayaran->update([
                        'status_pembayaran' => 'gagal',
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
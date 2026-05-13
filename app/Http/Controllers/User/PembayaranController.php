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
        // =========================================
        // KONFIGURASI MIDTRANS
        // =========================================

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * =========================================
     * HALAMAN PEMBAYARAN
     * =========================================
     */
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

        // =========================================
        // AMBIL KODE BOOKING
        // =========================================

        $kodeBooking = $penyewaan->kode_booking;

        // =========================================
        // AMBIL SEMUA FASILITAS DALAM 1 BOOKING
        // =========================================

        $semuaFasilitas = Penyewaan::where(
            'kode_booking',
            $kodeBooking
        )
        ->with('fasilitas')
        ->get()
        ->pluck('fasilitas.nama_fasilitas')
        ->implode(', ');

        // =========================================
        // TOTAL SELURUH TAGIHAN DALAM 1 BOOKING
        // =========================================

        $totalTagihan = Penyewaan::where(
            'kode_booking',
            $kodeBooking
        )->sum('total_harga');

        // =========================================
        // TOTAL SUDAH DIBAYAR
        // =========================================

        $totalBayar = Pembayaran::whereHas(
            'penyewaan',
            function ($q) use ($kodeBooking) {

                $q->where('kode_booking', $kodeBooking);
            }
        )
        ->where('status_pembayaran', 'berhasil')
        ->sum('jumlah_bayar');

        // =========================================
        // HITUNG SISA TAGIHAN
        // =========================================

        $sisaTagihan = $totalTagihan - $totalBayar;

        // =========================================
        // JIKA SUDAH LUNAS
        // =========================================

        if ($sisaTagihan <= 0) {

            return redirect()
                ->route('user.pengembalian')
                ->with(
                    'success',
                    'Pembayaran sudah lunas.'
                );
        }

        // =========================================
        // AMBIL PEMBAYARAN PENDING
        // =========================================

        $pembayaran = Pembayaran::where(
            'id_penyewaan',
            $id
        )
        ->where('status_pembayaran', 'pending')
        ->first();

        // =========================================
        // JIKA BELUM ADA PAYMENT PENDING
        // =========================================

        if (!$pembayaran) {

            $pembayaran = Pembayaran::create([

                'id_penyewaan'      => $id,

                'kode_pembayaran'   =>
                    'TEMP-' . time(),

                'jumlah_bayar'      => 0,

                'status_pembayaran' =>
                    'pending',

                'jenis_pembayaran'  =>
                    'pelunasan',
            ]);
        }

        return view(
            'user.pembayaran.index',
            compact(
                'penyewaan',
                'pembayaran',
                'sisaTagihan',
                'totalTagihan',
                'totalBayar',
                'semuaFasilitas'
            )
        );
    }

    /**
     * =========================================
     * PROSES PEMBAYARAN
     * =========================================
     */
    public function proses(Request $request, $id)
    {
        // =========================================
        // BERSIHKAN FORMAT NOMINAL
        // =========================================

        $cleanNominal = str_replace(
            '.',
            '',
            $request->nominal_bayar
        );

        $request->merge([
            'nominal_bayar' => $cleanNominal
        ]);

        // =========================================
        // AMBIL DATA PENYEWAAN
        // =========================================

        $penyewaan = Penyewaan::with([
            'pembayaran' => function ($query) {

                $query->where(
                    'status_pembayaran',
                    'berhasil'
                );
            }
        ])
        ->findOrFail($id);

        // =========================================
        // AMBIL KODE BOOKING
        // =========================================

        $kodeBooking = $penyewaan->kode_booking;

        // =========================================
        // TOTAL SEMUA TAGIHAN BOOKING
        // =========================================

        $totalTagihan = Penyewaan::where(
            'kode_booking',
            $kodeBooking
        )->sum('total_harga');

        // =========================================
        // TOTAL YANG SUDAH DIBAYAR
        // =========================================

        $totalTerbayar = Pembayaran::whereHas(
            'penyewaan',
            function ($q) use ($kodeBooking) {

                $q->where(
                    'kode_booking',
                    $kodeBooking
                );
            }
        )
        ->where('status_pembayaran', 'berhasil')
        ->sum('jumlah_bayar');

        // =========================================
        // HITUNG SISA TAGIHAN
        // =========================================

        $sisaTagihan =
            $totalTagihan - $totalTerbayar;

        // =========================================
        // VALIDASI DINAMIS DP / PELUNASAN
        // =========================================

        if ($totalTerbayar <= 0) {

            // DP minimal 50%

            $minBayar =
                round($totalTagihan * 0.5);

            $maxBayar =
                $totalTagihan;

            $pesanMin =
                'Minimal pembayaran DP adalah Rp '
                . number_format(
                    $minBayar,
                    0,
                    ',',
                    '.'
                );

        } else {

            // Pelunasan wajib sesuai sisa

            $minBayar =
                $sisaTagihan;

            $maxBayar =
                $sisaTagihan;

            $pesanMin =
                'Sisa tagihan yang harus dilunasi adalah Rp '
                . number_format(
                    $sisaTagihan,
                    0,
                    ',',
                    '.'
                );
        }

        // =========================================
        // VALIDASI NOMINAL
        // =========================================

        $request->validate([

            'nominal_bayar' => [

                'required',
                'numeric',
                'min:' . $minBayar,
                'max:' . $maxBayar
            ],

        ], [

            'nominal_bayar.required' =>
                'Nominal bayar wajib diisi.',

            'nominal_bayar.min' =>
                $pesanMin,

            'nominal_bayar.max' =>
                'Nominal melebihi sisa tagihan Rp '
                . number_format(
                    $maxBayar,
                    0,
                    ',',
                    '.'
                ),
        ]);

        // =========================================
        // AMBIL PEMBAYARAN PENDING
        // =========================================

        $pembayaran = Pembayaran::where(
            'id_penyewaan',
            $id
        )
        ->where('status_pembayaran', 'pending')
        ->firstOrFail();

        // =========================================
        // ORDER ID MIDTRANS
        // =========================================

        $orderId =
            'PAY-' .
            $kodeBooking .
            '-' .
            time();

        try {

            // =========================================
            // PARAMETER MIDTRANS
            // =========================================

            $params = [

                'transaction_details' => [

                    'order_id' =>
                        $orderId,

                    'gross_amount' =>
                        (int) $request->nominal_bayar,
                ],

                'customer_details' => [

                    'first_name' =>
                        Auth::user()->name,

                    'email' =>
                        Auth::user()->email,
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

                /*
                |--------------------------------------------------------------------------
                | CALLBACK FINISH
                |--------------------------------------------------------------------------
                */

                'callbacks' => [

                    'finish' => route('user.pengembalian')
                ]
            ];

            // =========================================
            // GENERATE SNAP TOKEN
            // =========================================

            $snapToken = Snap::getSnapToken($params);

            // UPDATE PEMBAYARAN
            $pembayaran->update([
                'kode_pembayaran'   => $orderId,
                'jumlah_bayar'      => $request->nominal_bayar,
                'jenis_pembayaran'  => ($request->nominal_bayar == $sisaTagihan) ? 'pelunasan' : 'dp',
                'snap_token'        => $snapToken,
                'metode_pembayaran' => 'midtrans',
            ]);

            // Pastikan variabel totalBayar didefinisikan agar tidak error di view
            $totalBayar = $totalTerbayar; 

            return view('user.pembayaran.index', compact(
                'penyewaan',
                'pembayaran',
                'snapToken', // Token ini harus sampai ke view
                'sisaTagihan',
                'totalTagihan',
                'totalBayar',
                'semuaFasilitas'
            ));

        } catch (\Exception $e) {

            \Log::error(
                'Midtrans Error: '
                . $e->getMessage()
            );

            return back()->with(
                'error',
                'Gagal memproses pembayaran: '
                . $e->getMessage()
            );
        }
    }

    /**
     * =========================================
     * CALLBACK MIDTRANS
     * =========================================
     */
    public function callback(Request $request)
    {
        $orderId = $request->order_id;

        /*
        |--------------------------------------------------------------------------
        | CALLBACK PEMBAYARAN SEWA
        |--------------------------------------------------------------------------
        | FORMAT:
        | PAY-KODEBOOKING-123123
        */

        if (str_contains($orderId, 'PAY-')) {

            $pembayaran = Pembayaran::where(
                'kode_pembayaran',
                $orderId
            )->first();

            if ($pembayaran) {

                // =========================================
                // PEMBAYARAN BERHASIL
                // =========================================

                if (in_array(
                    $request->transaction_status,
                    [
                        'settlement',
                        'capture'
                    ]
                )) {

                    $pembayaran->update([

                        'status_pembayaran' =>
                            'berhasil',

                        'tanggal_bayar' =>
                            now(),
                    ]);
                }

                // =========================================
                // PEMBAYARAN GAGAL
                // =========================================

                elseif (in_array(
                    $request->transaction_status,
                    [
                        'expire',
                        'cancel',
                        'deny'
                    ]
                )) {

                    $pembayaran->update([

                        'status_pembayaran' =>
                            'gagal',
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CALLBACK PEMBAYARAN DENDA
        |--------------------------------------------------------------------------
        | FORMAT:
        | DENDA-1-123123
        */

        if (str_contains($orderId, 'DENDA-')) {

            $explode = explode('-', $orderId);

            $idDenda = $explode[1] ?? null;

            $denda = Denda::with(
                'penyewaan'
            )->find($idDenda);

            if ($denda) {

                // =========================================
                // PEMBAYARAN DENDA BERHASIL
                // =========================================

                if (in_array(
                    $request->transaction_status,
                    [
                        'settlement',
                        'capture'
                    ]
                )) {

                    $denda->update([

                        'status_denda' =>
                            'lunas'
                    ]);

                    if ($denda->penyewaan) {

                        $denda->penyewaan->update([

                            'status_sewa' =>
                                'selesai'
                        ]);
                    }
                }

                // =========================================
                // PEMBAYARAN DENDA GAGAL
                // =========================================

                elseif (in_array(
                    $request->transaction_status,
                    [
                        'expire',
                        'cancel',
                        'deny'
                    ]
                )) {

                    $denda->update([

                        'status_denda' =>
                            'belum_bayar'
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
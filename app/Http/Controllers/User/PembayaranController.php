<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;

class PembayaranController extends Controller
{
    public function index($id)
    {
        // =========================
        // AMBIL DATA PENYEWAAN
        // =========================
        $penyewaan = Penyewaan::with('fasilitas')->findOrFail($id);

        // =========================
        // VALIDASI TOTAL
        // =========================
        if (!$penyewaan->total_harga || $penyewaan->total_harga <= 0) {
            return redirect()->back()->with('error', 'Total pembayaran tidak valid!');
        }

        // =========================
        // CONFIG MIDTRANS
        // =========================
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        // =========================
        // ORDER ID (WAJIB UNIK)
        // =========================
        $orderId = $penyewaan->kode_booking . '-' . time();

        // =========================
        // PARAMETER MIDTRANS
        // =========================
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $penyewaan->total_harga,
            ],

            'customer_details' => [
                'first_name' => $penyewaan->nama_penyewa,
                'email' => Auth::user()->email ?? 'user@gmail.com',
            ],

            'item_details' => [
                [
                    'id' => $penyewaan->id_penyewaan,
                    'price' => (int) $penyewaan->total_harga,
                    'quantity' => 1,
                    'name' => $penyewaan->fasilitas->nama_fasilitas ?? 'Sewa Fasilitas',
                ]
            ],
        ];

        // =========================
        // GENERATE SNAP TOKEN
        // =========================
        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Midtrans Error: ' . $e->getMessage());
        }

        // =========================
        // KIRIM KE VIEW
        // =========================
        return view('user.pembayaran.index', compact('penyewaan', 'snapToken'));
    }
}
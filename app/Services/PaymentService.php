<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * =========================
     * INIT MIDTRANS
     * =========================
     */
    protected function initMidtrans()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        if (empty(Config::$serverKey)) {
            throw new \Exception('Server Key Midtrans belum di-set');
        }
    }

    /**
     * =========================
     * CREATE TRANSACTION
     * =========================
     */
    public function createTransaction($penyewaan)
    {
        $this->initMidtrans();

        // =========================
        // VALIDASI DATA
        // =========================
        if (!$penyewaan) {
            throw new \Exception('Data penyewaan tidak ditemukan');
        }

        if ((int)$penyewaan->total_harga <= 0) {
            throw new \Exception('Total harga tidak valid');
        }

        // =========================
        // CEK STATUS PEMBAYARAN
        // =========================
        if ($penyewaan->status_pembayaran === 'lunas') {
            throw new \Exception('Pembayaran sudah lunas');
        }

        // =========================
        // ORDER ID (WAJIB UNIK)
        // =========================
        $order_id = $penyewaan->kode_booking;

        // =========================
        // JIKA TOKEN SUDAH ADA
        // =========================
        if (!empty($penyewaan->snap_token)) {
            return [
                'order_id'   => $order_id,
                'snap_token' => $penyewaan->snap_token
            ];
        }

        // =========================
        // PARAMETER MIDTRANS
        // =========================
        $params = [
            'transaction_details' => [
                'order_id'     => $order_id,
                'gross_amount' => (int) $penyewaan->total_harga,
            ],

            'item_details' => [
                [
                    'id'       => $penyewaan->id,
                    'price'    => (int) $penyewaan->total_harga,
                    'quantity' => 1,
                    'name'     => $penyewaan->fasilitas->nama_fasilitas ?? 'Sewa Fasilitas',
                ]
            ],

            'customer_details' => [
                'first_name' => auth()->user()->name ?? 'User',
                'email'      => auth()->user()->email ?? 'user@mail.com',
            ],

            // OPTIONAL CALLBACK (AMAN)
            'callbacks' => [
                'finish' => url('/payment/finish'),
                'error'  => url('/payment/error'),
            ]
        ];

        // =========================
        // REQUEST SNAP TOKEN
        // =========================
        try {

            $snapToken = Snap::getSnapToken($params);

            // =========================
            // SIMPAN TOKEN KE DATABASE
            // =========================
            $penyewaan->update([
                'snap_token' => $snapToken
            ]);

            return [
                'order_id'   => $order_id,
                'snap_token' => $snapToken
            ];

        } catch (\Exception $e) {

            Log::error('MIDTRANS ERROR: ' . $e->getMessage());

            throw new \Exception("Midtrans gagal: " . $e->getMessage());
        }
    }
}
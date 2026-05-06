<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class PembayaranController extends Controller
{
    public function __construct()
    {
        // Pastikan konfigurasi mengambil dari file config/services.php atau .env
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Tampilan Halaman Input Nominal
     */
    public function index($id)
    {
        // 1. Ambil data penyewaan dan hanya pembayaran yang sudah SUKSES
        $penyewaan = Penyewaan::with(['fasilitas', 'pembayaran' => function($query) {
            $query->where('status_pembayaran', 'berhasil'); // Kunci utama ada di sini
        }])->where('id_user', Auth::id())->findOrFail($id);

        // 2. Ambil total_harga dari tabel PENYEWAAN
        $totalTagihan = $penyewaan->total_harga;

        // 3. Ambil jumlah_bayar dari tabel PEMBAYARAN (semua yang statusnya berhasil)
        $totalYangSudahDibayar = $penyewaan->pembayaran->sum('jumlah_bayar');

        // 4. Hitung Sisa Bayar
        $sisaTagihan = $totalTagihan - $totalYangSudahDibayar;

        // 5. Cari record pembayaran yang statusnya 'pending' untuk diproses transaksinya
        $pembayaran = Pembayaran::where('id_penyewaan', $id)
            ->where('status_pembayaran', 'pending')
            ->first();

        if (!$pembayaran) {
            return redirect()->route('user.penyewaan.index')
                ->with('error', 'Tagihan tidak ditemukan atau sudah lunas.');
        }

        return view('user.pembayaran.index', compact('penyewaan', 'pembayaran', 'sisaTagihan'));
    }

    /**
     * Proses Validasi & Generate Snap Token
     */
    public function proses(Request $request, $id)
    {
        // 1. Bersihkan input nominal
        $cleanNominal = str_replace('.', '', $request->nominal_bayar);
        $request->merge(['nominal_bayar' => $cleanNominal]);

        // Ambil data penyewaan beserta riwayat pembayaran yang sudah 'berhasil'
        $penyewaan = Penyewaan::with(['pembayaran' => function($query) {
            $query->where('status_pembayaran', 'berhasil');
        }])->findOrFail($id);

        // Hitung total yang sudah dibayar dan sisa tagihan
        $totalTerbayar = $penyewaan->pembayaran->sum('jumlah_bayar');
        $sisaTagihan = $penyewaan->total_harga - $totalTerbayar;

        // 2. Tentukan batas nominal secara dinamis
        if ($totalTerbayar <= 0) {
            // Jika belum pernah bayar (Tahap DP)
            $minBayar = round($penyewaan->total_harga * 0.5); 
            $maxBayar = $penyewaan->total_harga;
            $pesanMin = 'Minimal pembayaran DP adalah Rp ' . number_format($minBayar, 0, ',', '.');
        } else {
            // Jika sudah pernah DP (Tahap Pelunasan)
            // Paksa nominal harus tepat sebesar sisa tagihan
            $minBayar = $sisaTagihan;
            $maxBayar = $sisaTagihan;
            $pesanMin = 'Sisa tagihan yang harus dilunasi adalah Rp ' . number_format($sisaTagihan, 0, ',', '.');
        }

        // 3. Validasi berdasarkan batas dinamis
        $request->validate([
            'nominal_bayar' => [
                'required', 
                'numeric', 
                'min:' . $minBayar, 
                'max:' . $maxBayar
            ],
        ], [
            'nominal_bayar.required' => 'Nominal bayar wajib diisi.',
            'nominal_bayar.min'      => $pesanMin,
            'nominal_bayar.max'      => 'Nominal melebihi sisa tagihan Rp ' . number_format($maxBayar, 0, ',', '.'),
        ]);

        $pembayaran = Pembayaran::where('id_penyewaan', $id)
            ->where('status_pembayaran', 'pending')
            ->firstOrFail();

        $orderId = 'PAY-' . $penyewaan->kode_booking . '-' . time();

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
                'enabled_payments' => ['credit_card', 'bca_va', 'bni_va', 'bri_va', 'gopay', 'shopeepay', 'other_va'],
            ];

            $snapToken = Snap::getSnapToken($params);

            // 4. Update data pembayaran dengan jenis yang sesuai
            $pembayaran->update([
                'kode_pembayaran'   => $orderId,
                'jumlah_bayar'      => $request->nominal_bayar,
                // Jika bayar sisa, maka statusnya 'pelunasan'
                'jenis_pembayaran'  => ($request->nominal_bayar == $sisaTagihan) ? 'pelunasan' : 'dp',
                'snap_token'        => $snapToken,
                'metode_pembayaran' => 'midtrans'
            ]);

            return view('user.pembayaran.index', compact('penyewaan', 'pembayaran', 'snapToken'));

        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses ke Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Callback Midtrans (Gunakan untuk verifikasi otomatis)
     */
    public function callback(Request $request)
    {
        // Untuk keamanan, sebaiknya tambahkan Signature Key Verification di sini
        $pembayaran = Pembayaran::where('kode_pembayaran', $request->order_id)->first();

        if ($pembayaran) {
            if (in_array($request->transaction_status, ['settlement', 'capture'])) {
                $pembayaran->update([
                    'status_pembayaran' => 'berhasil',
                    'tanggal_bayar' => now()
                ]);
            } elseif (in_array($request->transaction_status, ['expire', 'cancel', 'deny'])) {
                $pembayaran->update([
                    'status_pembayaran' => 'gagal'
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
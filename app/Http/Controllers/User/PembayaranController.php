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
     * Helper Privat: Mengambil semua nama fasilitas dalam satu booking
     */
    private function getDaftarFasilitas($kodeBooking)
    {
        return Penyewaan::where('kode_booking', $kodeBooking)
            ->with('fasilitas')
            ->get()
            ->pluck('fasilitas.nama_fasilitas')
            ->filter()
            ->unique()
            ->implode(', ');
    }

    /**
     * HALAMAN INDEX PEMBAYARAN
     */
    public function index($id)
    {
        $penyewaan = Penyewaan::with([
            'fasilitas',
            'pembayaran' => function ($q) {
                // Ambil semua pembayaran yang berhasil (DP & Pelunasan) untuk ditampilkan buktinya
                $q->where('status_pembayaran', 'berhasil')->orderBy('created_at', 'desc');
            }
        ])
        ->where('id_user', Auth::id())
        ->findOrFail($id);

        $kodeBooking = $penyewaan->kode_booking;
        $semuaFasilitas = $this->getDaftarFasilitas($kodeBooking);

        // 1. Hitung Total Tagihan asli dari semua item penyewaan dengan kode booking ini
        $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)->sum('total_harga');
        
        // 2. Hitung Total yang sudah dibayar (Berhasil)
        $totalBayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                $q->where('kode_booking', $kodeBooking);
            })
            ->where('status_pembayaran', 'berhasil')
            ->sum('jumlah_bayar');

        $sisaTagihan = $totalTagihan - $totalBayar;

        // 3. Tentukan status lunas (Digunakan di Blade untuk menyembunyikan form bayar)
        $isLunas = ($sisaTagihan <= 0);

        // 4. Ambil atau buat data pembayaran pending untuk proses Midtrans selanjutnya
        // Hanya cari pending jika belum lunas
        $pembayaran = null;
        if (!$isLunas) {
            $pembayaran = Pembayaran::where('id_penyewaan', $id)
                ->where('status_pembayaran', 'pending')
                ->first();

            if (!$pembayaran) {
                $pembayaran = Pembayaran::create([
                    'id_penyewaan'      => $id,
                    'kode_pembayaran'   => 'TEMP-' . time(),
                    'jumlah_bayar'      => 0,
                    'status_pembayaran' => 'pending',
                    'jenis_pembayaran'  => 'pelunasan',
                ]);
            }
        }

        // 5. Return View dengan semua variabel yang dibutuhkan
        return view('user.pembayaran.index', compact(
            'penyewaan', 
            'pembayaran', 
            'sisaTagihan', 
            'isLunas', 
            'totalTagihan', 
            'totalBayar', 
            'semuaFasilitas'
        ));
    }

    /**
     * PROSES PEMBAYARAN (GENERATE SNAP TOKEN)
     */
    public function proses(Request $request, $id)
    {
        // Bersihkan format titik pada input nominal
        $cleanNominal = str_replace('.', '', $request->nominal_bayar);
        $request->merge(['nominal_bayar' => $cleanNominal]);

        $penyewaan = Penyewaan::findOrFail($id);
        $kodeBooking = $penyewaan->kode_booking;
        
        // AMBIL KEMBALI FASILITAS UNTUK VIEW (Menghindari error Undefined Variable)
        $semuaFasilitas = $this->getDaftarFasilitas($kodeBooking);

        $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)->sum('total_harga');

        $totalTerbayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                $q->where('kode_booking', $kodeBooking);
            })
            ->where('status_pembayaran', 'berhasil')
            ->sum('jumlah_bayar');

        $sisaTagihan = $totalTagihan - $totalTerbayar;

        // Validasi Nominal (DP 50% atau Pelunasan Sisa)
        if ($totalTerbayar <= 0) {
            $minBayar = round($totalTagihan * 0.5);
            $maxBayar = $totalTagihan;
            $pesanMin = 'Minimal pembayaran DP adalah Rp ' . number_format($minBayar, 0, ',', '.');
        } else {
            $minBayar = $sisaTagihan;
            $maxBayar = $sisaTagihan;
            $pesanMin = 'Sisa tagihan yang harus dilunasi adalah Rp ' . number_format($sisaTagihan, 0, ',', '.');
        }

        $request->validate([
            'nominal_bayar' => ['required', 'numeric', 'min:' . $minBayar, 'max:' . $maxBayar],
        ], [
            'nominal_bayar.required' => 'Nominal bayar wajib diisi.',
            'nominal_bayar.min'      => $pesanMin,
            'nominal_bayar.max'      => 'Nominal melebihi sisa tagihan.',
        ]);

        $pembayaran = Pembayaran::where('id_penyewaan', $id)
            ->where('status_pembayaran', 'pending')
            ->firstOrFail();

        $orderId = 'PAY-' . $kodeBooking . '-' . time();

        try {
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $request->nominal_bayar,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email'      => Auth::user()->email,
                ],
                'enabled_payments' => ['credit_card', 'bca_va', 'bni_va', 'bri_va', 'gopay', 'shopeepay', 'other_va'],
                'callbacks'        => ['finish' => route('user.pengembalian')]
            ];

            $snapToken = Snap::getSnapToken($params);

            $pembayaran->update([
                'kode_pembayaran'   => $orderId,
                'jumlah_bayar'      => $request->nominal_bayar,
                'jenis_pembayaran'  => ($request->nominal_bayar == $sisaTagihan) ? 'pelunasan' : 'dp',
                'snap_token'        => $snapToken,
                'metode_pembayaran' => 'midtrans',
            ]);

            $totalBayar = $totalTerbayar; 

            return view('user.pembayaran.index', compact(
                'penyewaan', 'pembayaran', 'snapToken', 'sisaTagihan', 
                'totalTagihan', 'totalBayar', 'semuaFasilitas'
            ));

        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * CALLBACK MIDTRANS (WEBHOOK)
     */
    public function callback(Request $request)
    {
        $orderId = $request->order_id;

        if (str_contains($orderId, 'PAY-')) {
            $pembayaran = Pembayaran::where('kode_pembayaran', $orderId)->first();
            if ($pembayaran) {
                // Jika Midtrans bilang sukses, maka status record pembayaran ini BERHASIL
                if (in_array($request->transaction_status, ['settlement', 'capture'])) {
                    $pembayaran->update([
                        'status_pembayaran' => 'berhasil', 
                        'tanggal_bayar' => now()
                    ]);

        // Logika untuk Pembayaran Denda
        if (str_contains($orderId, 'DENDA-')) {
            $explode = explode('-', $orderId);
            $idDenda = $explode[1] ?? null;
            $denda = Denda::with('penyewaan')->find($idDenda);

            if ($denda) {
                if (in_array($request->transaction_status, ['settlement', 'capture'])) {
                    $denda->update(['status_denda' => 'lunas']);
                    if ($denda->penyewaan) {
                        $denda->penyewaan->update(['status_sewa' => 'selesai']);
                    }
                } elseif (in_array($request->transaction_status, ['expire', 'cancel', 'deny'])) {
                    $denda->update(['status_denda' => 'belum_bayar']);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
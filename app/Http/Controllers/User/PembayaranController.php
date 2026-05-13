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

    public function proses(Request $request, $id)
    {
        $cleanNominal = str_replace('.', '', $request->nominal_bayar);
        $request->merge(['nominal_bayar' => $cleanNominal]);

        $penyewaan = Penyewaan::findOrFail($id);
        $kodeBooking = $penyewaan->kode_booking;

        $totalTagihan = Penyewaan::where('kode_booking', $kodeBooking)->sum('total_harga');
        $totalTerbayar = Pembayaran::whereHas('penyewaan', function ($q) use ($kodeBooking) {
                $q->where('kode_booking', $kodeBooking);
            })
            ->where('status_pembayaran', 'berhasil')
            ->sum('jumlah_bayar');

        $sisaTagihan = $totalTagihan - $totalTerbayar;

        try {
            $orderId = 'PAY-' . $kodeBooking . '-' . time();
            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => (int) $request->nominal_bayar,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email'      => Auth::user()->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $pembayaran = Pembayaran::where('id_penyewaan', $id)
                ->where('status_pembayaran', 'pending')
                ->firstOrFail();

            $pembayaran->update([
                'kode_pembayaran'   => $orderId,
                'jumlah_bayar'      => $request->nominal_bayar,
                'jenis_pembayaran'  => ($request->nominal_bayar == $sisaTagihan) ? 'pelunasan' : 'dp',
                'snap_token'        => $snapToken,
                'metode_pembayaran' => 'midtrans',
            ]);

            return view('user.pembayaran.index', array_merge(
                compact('penyewaan', 'pembayaran', 'snapToken', 'sisaTagihan', 'totalTagihan'),
                ['totalBayar' => $totalTerbayar, 'semuaFasilitas' => $this->getDaftarFasilitas($kodeBooking)]
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * CALLBACK MIDTRANS (WEBHOOK)
     */
    public function callback(Request $request)
    {
        $orderId = $request->order_id;

        // Logika untuk Pembayaran Sewa (DP / Pelunasan)
        if (str_contains($orderId, 'PAY-')) {
            $pembayaran = Pembayaran::where('kode_pembayaran', $orderId)->first();
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
        } // Baris ini menutup pengecekan PAY-

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
        } // Baris ini menutup pengecekan DENDA-

        return response()->json(['status' => 'ok']);
    }
}
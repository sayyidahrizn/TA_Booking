<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use App\Models\Pembayaran; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PenyewaanController extends Controller
{
    /**
     * Tampilan Dashboard User
     */
    public function dashboard()
    {
        $penyewaan = Penyewaan::with(['fasilitas', 'pengembalian', 'pembayaran'])
            ->where('id_user', Auth::id())
            ->latest()->get()->groupBy('kode_booking')->take(5);

        $totalPenyewaan = Penyewaan::where('id_user', Auth::id())->count();
        $penyewaanAktif = Penyewaan::where('id_user', Auth::id())
            ->whereIn('status_sewa', ['proses', 'disetujui', 'dibatalkan_user'])
            ->whereDoesntHave('pengembalian', function($q) {
                $q->where('status_validasi', 'disetujui');
            })->count();

        return view('user.dashboard', compact('penyewaan', 'totalPenyewaan', 'penyewaanAktif'));
    }

    /**
     * Daftar Penyewaan Aktif (Transaksi Berjalan)
     */
    public function index()
    {
        $data = Penyewaan::with(['fasilitas', 'pengembalian', 'pembayaran'])
            ->where('id_user', Auth::id())
            ->whereNotIn('status_sewa', ['batal', 'selesai','dibatalkan_user'])
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->map(function ($group) {
                // 1. Hitung total harga untuk seluruh grup booking ini
                $totalHargaGrup = $group->sum('total_harga');
                
                // 2. Ambil semua pembayaran yang VALID (Berhasil/Diverifikasi)
                $pembayaranValid = $group->flatMap->pembayaran
                    ->whereIn('status_pembayaran', ['berhasil', 'diverifikasi']);
                
                $totalSudahBayar = $pembayaranValid->sum('jumlah_bayar');

                return $group->map(function ($item) use ($totalHargaGrup, $totalSudahBayar) {
                    // Properti tambahan untuk digunakan di View secara konsisten
                    $item->is_lunas = $totalSudahBayar >= $totalHargaGrup;
                    $item->sudah_pernah_bayar = $totalSudahBayar > 0; // Trigger Cetak Bukti
                    $item->sisa_tagihan = $totalHargaGrup - $totalSudahBayar;
                    $item->total_masuk = $totalSudahBayar;
                    
                    return $item;
                });
            });

        return view('user.penyewaan.index', compact('data'));
    }

    /**
     * Riwayat Penyewaan Selesai/Batal
     */
    public function riwayat()
    {
        // Mengambil data penyewaan milik user yang sedang login
        $data = Penyewaan::with([
                'fasilitas',      // Mengambil data fasilitas terkait
                'pengembalian',   // Mengambil data pengembalian terkait
                'pembayaran',     // Mengambil data pembayaran terkait
                'denda'           // Mengambil data denda terkait
            ])
            ->where('id_user', Auth::id()) 
            ->latest() // Mengurutkan dari yang terbaru berdasarkan waktu buat
            ->get()
            ->groupBy('kode_booking'); // Dikelompokkan berdasarkan kode booking sesuai kebutuhan Blade

        return view('user.riwayat.index', compact('data'));
    }

    /**
     * Form Booking Baru
     */
    public function create()
    {
        // Mengambil fasilitas yang berstatus tersedia
        $fasilitas = Fasilitas::where('status_fasilitas', 'tersedia')->get();

        // Mengambil semua data booking yang aktif (tidak batal)
        $existingBookings = Penyewaan::whereNotIn('status_sewa', ['batal'])
            ->whereNotNull(['id_fasilitas', 'tgl_mulai', 'tgl_selesai'])
            ->get(['id_fasilitas', 'tgl_mulai', 'tgl_selesai'])
            ->map(function ($item) {
                return [
                    'id_fasilitas' => $item->id_fasilitas,
                    // Format khusus untuk Flatpickr (Y-m-d)
                    'from' => \Carbon\Carbon::parse($item->tgl_mulai)->format('Y-m-d'),
                    'to' => \Carbon\Carbon::parse($item->tgl_selesai)->format('Y-m-d'),
                ];
            })
            ->values();

        return view('user.penyewaan.create', compact('fasilitas', 'existingBookings'));
    }

    /**
     * Simpan Pengajuan Sewa Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'metode_pembayaran' => 'required|in:midtrans,tunai',
        ]);

        $user = Auth::user();
        if (empty($user->nik)) {
            return back()->with('error', 'NIK Anda belum terdaftar.');
        }

        $kodeBooking = 'BOOK-' . strtoupper(Str::random(6));

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {

                $fasilitas = Fasilitas::lockForUpdate()->findOrFail($item['id_fasilitas']);

                $mulai = Carbon::parse($item['tgl_mulai']);
                $selesai = Carbon::parse($item['tgl_selesai']);
                $selisihHari = $mulai->diffInDays($selesai) + 1;

                $totalHarga = ($fasilitas->harga_sewa * $item['jumlah_sewa']) * $selisihHari;

                // ===============================
                // SIMPAN PENYEWAAN
                // ===============================
                $penyewaan = Penyewaan::create([
                    'kode_booking' => $kodeBooking,
                    'id_user' => $user->id,
                    'id_fasilitas' => $item['id_fasilitas'],
                    'jumlah_sewa' => $item['jumlah_sewa'],
                    'nama_penyewa' => $user->name,
                    'nik' => $user->nik,
                    'tgl_mulai' => $item['tgl_mulai'],
                    'tgl_selesai' => $item['tgl_selesai'],
                    'keterangan' => $request->keterangan,
                    'total_harga' => $totalHarga,
                    'status_sewa' => 'proses',
                ]);

                // ===============================
                // SIMPAN PEMBAYARAN (DEFAULT)
                // ===============================
                Pembayaran::create([
                    'id_penyewaan' => $penyewaan->id_penyewaan,
                    'kode_pembayaran' => 'PAY-' . time() . '-' . Str::random(4),

                    // ❗ DIISI NANTI OLEH ADMIN
                    'jenis_pembayaran' => null,
                    'metode_pembayaran' => $request->metode_pembayaran,

                    // sementara 0 dulu
                    'jumlah_bayar' => 0,

                    'status_pembayaran' => 'pending',
                ]);

                // ===============================
                // KURANGI STOK
                // ===============================
                $fasilitas->decrement('jumlah', $item['jumlah_sewa']);
            }

            DB::commit();

            $msg = $request->metode_pembayaran == 'tunai' 
               ? 'Booking diajukan. Silakan datang ke kantor untuk pembayaran tunai setelah disetujui.' 
               : 'Booking diajukan. Silakan lakukan pembayaran via Midtrans setelah disetujui.';

            return redirect()->route('user.penyewaan.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

        /**
     * Batalkan Penyewaan oleh User
     */
    public function batalGroup($kode)
    {
        DB::beginTransaction();

        try {

            $data = Penyewaan::where('kode_booking', $kode)
                ->where('id_user', Auth::id())
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Data penyewaan tidak ditemukan.');
            }

            foreach ($data as $item) {

                // hanya bisa dibatalkan jika masih proses
                if ($item->status_sewa != 'proses') {
                    return back()->with('error', 'Penyewaan sudah divalidasi admin.');
                }

                // kembalikan stok fasilitas
                $fasilitas = Fasilitas::find($item->id_fasilitas);

                if ($fasilitas) {
                    $fasilitas->increment('jumlah', $item->jumlah_sewa);
                }

                // update status
                $item->update([
                    'status_sewa' => 'dibatalkan_user'
                ]);
            }

            DB::commit();

            return back()->with('success', 'Penyewaan berhasil dibatalkan.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cetak Bukti Pembayaran
     */
    public function cetakBukti($kode_booking)
    {
        $data = Penyewaan::with(['user', 'fasilitas', 'pembayaran'])
            ->where('kode_booking', $kode_booking)
            ->where('id_user', Auth::id())->get();

        if ($data->isEmpty()) return back()->with('error', 'Data tidak ditemukan.');
        
        return view('user.penyewaan.bukti', compact('data'));
    }

    /**
     * Halaman Profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile.index', compact('user'));
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PenyewaanController extends Controller
{
    /**
     * Menampilkan Dashboard Pengunjung
     */
    public function dashboard()
    {
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        $totalPenyewaan = Penyewaan::where('id_user', Auth::id())->count();
        
        // Penyewaan aktif adalah yang status sewanya prosess atau disetujui
        $penyewaanAktif = Penyewaan::where('id_user', Auth::id())
            ->whereIn('status_sewa', ['prosess', 'disetujui'])
            ->count();

        return view('user.dashboard', compact('penyewaan', 'totalPenyewaan', 'penyewaanAktif'));
    }

    /**
     * Daftar Penyewaan Aktif (Group by Kode Booking)
     */
    public function index()
    {
        $data = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->whereIn('status_sewa', ['proses', 'disetujui'])
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('user.penyewaan.index', compact('data'));
    }

    /**
     * Riwayat Penyewaan
     */
    public function riwayat()
    {
        $data = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->whereNotIn('status_sewa', ['proses', 'disetujui'])
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('user.riwayat.index', compact('data'));
    }

    /**
     * Detail Penyewaan
     */
    public function show($id)
    {
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->findOrFail($id);

        return view('user.penyewaan.show', compact('penyewaan'));
    }

    /**
     * Halaman Form Pembayaran
     */
    public function pembayaran($id)
    {
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->findOrFail($id);

        if ($penyewaan->status_pembayaran == 'dibayar') {
            return redirect()->route('user.riwayat')->with('error', 'Penyewaan ini sudah dibayar.');
        }

        return view('user.pembayaran.index', compact('penyewaan'));
    }

    /**
     * Form Booking Baru
     */
    public function create()
    {
        $fasilitas = Fasilitas::where('status_fasilitas', 'tersedia')->get();
        return view('user.penyewaan.create', compact('fasilitas'));
    }

    /**
     * Simpan Data Booking (Tanpa Duplikat)
     */
    public function store(Request $request)
    {
        $request->validate([
            'keterangan'   => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.id_fasilitas' => 'required|exists:fasilitas,id_fasilitas',
            'items.*.tgl_mulai'    => 'required|date|after_or_equal:today',
            'items.*.tgl_selesai'  => 'required|date|after_or_equal:items.*.tgl_mulai',
        ]);

        $user = Auth::user();

        if (empty($user->nik)) {
            return back()->with('error', 'Gagal: NIK Anda belum terdaftar di profil.');
        }

        $kodeBooking = 'BOOK-' . strtoupper(Str::random(6));

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $fasilitas = Fasilitas::findOrFail($item['id_fasilitas']);
                $mulai = Carbon::parse($item['tgl_mulai']);
                $selesai = Carbon::parse($item['tgl_selesai']);
                $selisihHari = $mulai->diffInDays($selesai) + 1;
                $totalHarga = $selisihHari * $fasilitas->harga_sewa;

                Penyewaan::create([
                    'kode_booking'      => $kodeBooking,
                    'id_user'           => $user->id,
                    'nama_penyewa'      => $user->name,
                    'nik'               => $user->nik,
                    'id_fasilitas'      => $item['id_fasilitas'],
                    'tgl_mulai'         => $item['tgl_mulai'],
                    'tgl_selesai'       => $item['tgl_selesai'],
                    'keterangan'        => $request->keterangan,
                    'total_harga'       => $totalHarga,
                    'status_sewa'       => 'proses',
                    'status_pembayaran' => 'pending',
                ]);
            }

            DB::commit();
            return redirect()->route('user.penyewaan.index')->with('success', 'Booking berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Simpan Bukti Pembayaran
     */
    public function pembayaranStore(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'metode' => 'required'
        ]);

        $penyewaan = Penyewaan::findOrFail($id);

        if ($request->hasFile('bukti_pembayaran')) {
            $path = $request->file('bukti_pembayaran')->store('bukti_bayar', 'public');

            $penyewaan->update([
                'status_pembayaran' => 'dibayar',
                'bukti_pembayaran'  => $path,
                'metode_bayar'      => $request->metode,
                'status_sewa'       => 'disetujui'
            ]);

            return redirect()->route('user.riwayat')->with('success', 'Bukti pembayaran berhasil diunggah!');
        }

        return back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }
}
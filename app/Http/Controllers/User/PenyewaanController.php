<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\Fasilitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class PenyewaanController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function dashboard()
    {
        // Perbaikan: Hapus filter status_pengembalian agar data yang sudah selesai tetap tampil di list terbaru
        $penyewaan = Penyewaan::with(['fasilitas', 'pengembalian'])
            ->where('id_user', Auth::id())
            ->latest()
            ->get()
            ->groupBy('kode_booking')
            ->take(5);

        $totalPenyewaan = Penyewaan::where('id_user', Auth::id())->count();
        
        // Tetap menghitung penyewaan aktif (yang belum selesai) untuk statistik box
        $penyewaanAktif = Penyewaan::where('id_user', Auth::id())
            ->whereIn('status_sewa', ['proses', 'disetujui'])
            ->where('status_pengembalian', '!=', 'selesai')
            ->count();

        return view('user.dashboard', compact('penyewaan', 'totalPenyewaan', 'penyewaanAktif'));
    }

    public function index()
    {
        // Perbaikan: Menampilkan semua penyewaan (proses, disetujui, dan selesai) agar tidak hilang dari daftar
        $data = Penyewaan::with(['fasilitas', 'pengembalian'])
            ->where('id_user', Auth::id())
            ->whereIn('status_sewa', ['proses', 'disetujui', 'selesai'])
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('user.penyewaan.index', compact('data'));
    }

    public function riwayat()
    {
        // Menampilkan daftar penyewaan yang sudah selesai atau final
        $data = Penyewaan::with(['fasilitas', 'pengembalian'])
            ->where('id_user', Auth::id())
            ->where(function($query) {
                $query->whereIn('status_sewa', ['selesai', 'batal'])
                      ->orWhere('status_pengembalian', 'selesai'); // Muncul di riwayat jika sudah kembali
            })
            ->latest()
            ->get()
            ->groupBy('kode_booking');

        return view('user.riwayat.index', compact('data'));
    }

    public function show($id)
    {
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->findOrFail($id);

        return view('user.penyewaan.show', compact('penyewaan'));
    }

    public function create()
    {
        $fasilitas = Fasilitas::where('status_fasilitas', 'tersedia')->get();
        return view('user.penyewaan.create', compact('fasilitas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id_fasilitas' => 'required|exists:fasilitas,id_fasilitas',
            'items.*.jumlah_sewa' => 'required|integer|min:1', 
            'items.*.tgl_mulai' => 'required|date|after_or_equal:today',
            'items.*.tgl_selesai' => 'required|date|after_or_equal:items.*.tgl_mulai',
        ]);

        $user = Auth::user();
        if (empty($user->nik)) {
            return back()->with('error', 'Gagal: NIK Anda belum terdaftar di profil.');
        }

        $kodeBooking = 'BOOK-' . strtoupper(Str::random(6));

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $fasilitas = Fasilitas::lockForUpdate()->findOrFail($item['id_fasilitas']);

                if ($fasilitas->jumlah < $item['jumlah_sewa']) {
                    throw new \Exception("Stok {$fasilitas->nama_fasilitas} sisa {$fasilitas->jumlah}. Permintaan ({$item['jumlah_sewa']}) gagal.");
                }

                $mulai = Carbon::parse($item['tgl_mulai']);
                $selesai = Carbon::parse($item['tgl_selesai']);
                $selisihHari = $mulai->diffInDays($selesai) + 1;
                $totalHarga = ($fasilitas->harga_sewa * $item['jumlah_sewa']) * $selisihHari;

                Penyewaan::create([
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
                    'status_pembayaran' => 'pending',
                ]);

                $fasilitas->decrement('jumlah', $item['jumlah_sewa']);

                if ($fasilitas->jumlah <= 0) {
                    $fasilitas->update(['status_fasilitas' => 'tidak tersedia']);
                }
            }

            DB::commit();
            return redirect()->route('user.penyewaan.index')->with('success', 'Booking berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function pembayaran($id)
    {
        $penyewaan = Penyewaan::with('fasilitas')
            ->where('id_user', Auth::id())
            ->findOrFail($id);

        if ($penyewaan->status_pembayaran === 'lunas') {
            return redirect()->route('user.riwayat')->with('error', 'Penyewaan ini sudah dibayar.');
        }

        $orderId = $penyewaan->kode_booking . '-' . time();
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $penyewaan->total_harga,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        return view('user.pembayaran.index', compact('penyewaan', 'snapToken'));
    }

    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $signature = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($signature !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $parts = explode('-', $request->order_id);
        $kodeBooking = $parts[0] . '-' . $parts[1];
        $bookings = Penyewaan::where('kode_booking', $kodeBooking)->get();

        if (in_array($request->transaction_status, ['settlement', 'capture'])) {
            foreach ($bookings as $b) {
                $b->update(['status_pembayaran' => 'lunas', 'status_sewa' => 'disetujui']);
            }
        } elseif (in_array($request->transaction_status, ['expire', 'cancel', 'deny'])) {
            foreach ($bookings as $b) {
                if ($b->status_sewa !== 'batal') {
                    $fasilitas = Fasilitas::find($b->id_fasilitas);
                    if ($fasilitas) {
                        $fasilitas->increment('jumlah', $b->jumlah_sewa);
                        $fasilitas->update(['status_fasilitas' => 'tersedia']);
                    }
                }
                $b->update(['status_pembayaran' => 'batal', 'status_sewa' => 'batal']);
            }
        }

        return response()->json(['message' => 'success']);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile.index', compact('user'));
    }

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
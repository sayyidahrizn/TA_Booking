<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Fasilitas;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Fitur Filter Berdasarkan Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Fitur Search Berdasarkan Nama atau NIK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Mengurutkan berdasarkan yang terbaru dan pagination
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'      => 'required|numeric|digits:16|unique:users,nik',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'role'     => 'required|in:kaur,penyewa',
            'password' => 'required|min:6'
        ]);

        User::create([
            'nik'      => $request->nik,
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nik'      => 'required|numeric|digits:16|unique:users,nik,' . $id,
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'role'     => 'required|in:kaur,penyewa',
            'password' => 'nullable|min:6'
        ]);

        $user->nik = $request->nik;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus');
    }

    // ================= FITUR LAPORAN TERPADU =================
    public function laporan()
    {
        // Statistik Utama
        $total_fasilitas = Fasilitas::count();
        
        // 1. Fasilitas Disewa (Menghitung yang sudah disetujui atau sudah selesai)
        $fasilitas_disewa = Penyewaan::whereIn('status_sewa', ['disetujui', 'selesai'])->count();
        
        // 2. Booking Pending (Di database kamu namanya 'proses')
        $booking_pending = Penyewaan::where('status_sewa', 'proses')->count();
        
        // 3. Total Pendapatan (Menghitung total_harga yang status_pembayarannya 'lunas')
        $total_pendapatan = Penyewaan::where('status_pembayaran', 'lunas')->sum('total_harga');

        // Data User untuk Tabel
        $users = User::orderBy('name', 'asc')->get();

        return view('admin.users.laporan', compact(
            'users', 
            'total_fasilitas', 
            'fasilitas_disewa', 
            'booking_pending', 
            'total_pendapatan'
        ));
    }

    // ================= PROFIL ADMIN =================
    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile.index', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password')
                ? Hash::make($request->password)
                : $admin->password,
        ]);

        return redirect()->route('admin.profile')
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Mencegah error "Too Many Redirects" dengan arah ke index
     */
    public function show($id)
    {
        return redirect()->route('users.index');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
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
            'password' => Hash::make($request->password) // Menggunakan Hash::make lebih standar Laravel
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    // ✅ METHOD EDIT (Penting untuk memunculkan halaman edit)
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nik'      => 'required|numeric|digits:16|unique:users,nik,'.$id,
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role'     => 'required|in:kaur,penyewa',
            'password' => 'nullable|min:6' // Password opsional saat edit
        ]);

        // Update Name & Email
        $user->nik = $request->nik;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Cek jika password diisi, maka update password-nya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Redirect ke index supaya notifikasi muncul di halaman utama tabel
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Proteksi agar admin tidak tidak sengaja menghapus dirinya sendiri
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus');
    }
}
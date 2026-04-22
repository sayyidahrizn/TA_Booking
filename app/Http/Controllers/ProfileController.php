<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan form profil (Admin atau User).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Mengarahkan ke folder view yang benar berdasarkan role
        if ($user->role === 'admin') {
            return view('admin.profile.index', [
                'admin' => $user,
            ]);
        }

        return view('user.profile.index', [
            'user' => $user,
        ]);
    }

    /**
     * Memperbarui informasi profil, foto, dan password.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Ambil data yang divalidasi kecuali photo dan password agar tidak tertimpa otomatis
        $data = $request->safe()->except(['photo', 'password']);
        $user->fill($data);

        // Reset verifikasi email jika email berubah
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // 2. Logika Update Foto Profil
        if ($request->hasFile('photo')) {
            // Hapus foto lama dari storage jika ada (mencegah penumpukan file sampah)
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Simpan file baru ke folder 'profile_photos' di disk 'public'
            // Path yang tersimpan di DB: profile_photos/xxxx.jpg
            $path = $request->file('photo')->store('profile_photos', 'public');
            
            // Masukkan path ke model
            $user->photo = $path;
        }

        // 3. Logika Update Password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 4. Simpan semua perubahan ke database
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Menghapus akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Hapus file foto dari storage saat akun dihapus
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
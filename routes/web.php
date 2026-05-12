<?php

use App\Http\Controllers\Admin\LaporanController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\PengembalianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PengembalianController as AdminPengembalianController;
use App\Http\Controllers\User\PenyewaanController as UserPenyewaan;
use App\Http\Controllers\Admin\PenyewaanController as AdminPenyewaan;
use App\Http\Controllers\User\PembayaranController;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA & REGISTER
|--------------------------------------------------------------------------
*/
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/', [FasilitasController::class, 'landingPage'])->name('landing');
Route::resource('fasilitas', FasilitasController::class);

/*
|--------------------------------------------------------------------------
| REDIRECT DASHBOARD SETELAH LOGIN
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if (Auth::user()->role === 'kaur') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard & Fasilitas
    Route::get('/user/dashboard', [UserPenyewaan::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/fasilitas', [FasilitasController::class, 'indexUser'])->name('user.fasilitas.index');
    Route::get('/fasilitas/{id}', [FasilitasController::class, 'show'])->name('fasilitas.show');

    // Penyewaan (Booking)
    Route::get('/user/penyewaan', [UserPenyewaan::class, 'index'])->name('user.penyewaan.index');
    Route::get('/user/penyewaan/baru', [UserPenyewaan::class, 'create'])->name('user.penyewaan.create');
    Route::post('/user/penyewaan/simpan', [UserPenyewaan::class, 'store'])->name('user.penyewaan.store');
    Route::get('/user/riwayat', [UserPenyewaan::class, 'riwayat'])->name('user.riwayat');
    Route::get('/user/penyewaan/detail/{id}', [UserPenyewaan::class, 'show'])->name('user.penyewaan.show');

    // ========================================================
    // ✅ PEMBAYARAN (FIX DI SINI)
    // ========================================================
    Route::get('/user/pembayaran/{id}', [PembayaranController::class, 'index'])
        ->name('user.pembayaran.index');

    Route::post('/user/pembayaran/{id}/proses', [PembayaranController::class, 'proses'])
        ->name('user.pembayaran.proses');

    Route::get('/user/pelunasan/{id}', [App\Http\Controllers\User\PembayaranController::class, 'pelunasan'])
    ->name('user.pelunasan.index');

    Route::post('/user/pelunasan/{id}/proses', [App\Http\Controllers\User\PembayaranController::class, 'prosesPelunasan'])
    ->name('user.pelunasan.proses');

    // Profile
    Route::get('/user/profile', [ProfileController::class, 'edit'])->name('user.profile');
    Route::post('/user/profile/update', [ProfileController::class, 'update'])->name('user.profile.update');

    // Pengembalian
    Route::get('/user/pengembalian', [PengembalianController::class, 'index'])->name('user.pengembalian');
    Route::post('/user/pengembalian/store', [PengembalianController::class, 'store'])->name('user.pengembalian.store');
    Route::get('/pengembalian/bayar-denda/{id}', [PengembalianController::class, 'bayarDenda'])->name('user.pengembalian.bayar');

    // Cetak Bukti
    Route::get('/penyewaan/bukti/{kode_booking}', [UserPenyewaan::class, 'cetakBukti'])->name('user.penyewaan.bukti');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'isAdmin'])->group(function () {

    Route::get('/dashboard', [AdminPenyewaan::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/pembayaran', [AdminPenyewaan::class, 'pembayaran'])->name('admin.pembayaran.index');

    Route::get('/users/laporan', [UserController::class, 'laporan'])->name('users.laporan');
    Route::resource('users', UserController::class);

    Route::resource('fasilitas', FasilitasController::class);
    Route::delete('/fasilitas/gambar/{id}', [FasilitasController::class, 'hapusGambar'])->name('fasilitas.gambar.hapus');

    Route::get('/penyewaan', [AdminPenyewaan::class, 'index'])->name('admin.penyewaan.index');
    Route::post('/penyewaan/konfirmasi-group/{kode}', [AdminPenyewaan::class, 'konfirmasiGroup'])->name('admin.penyewaan.konfirmasi.group');
    Route::post('/penyewaan/tolak-group/{kode}', [AdminPenyewaan::class, 'tolakGroup'])->name('admin.penyewaan.tolak.group');
    Route::delete('/penyewaan/{id}', [AdminPenyewaan::class, 'destroy'])->name('admin.penyewaan.destroy');

    Route::post('/pengembalian/validasi', [AdminPengembalianController::class, 'validasi'])->name('admin.pengembalian.validasi');
    Route::get('/pengembalian', [AdminPengembalianController::class, 'index'])->name('admin.pengembalian');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('admin.profile.update');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan');
    Route::get('/laporan/pdf', [LaporanController::class, 'downloadPDF'])->name('admin.laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'downloadExcel'])->name('admin.laporan.excel');
});

/*
|--------------------------------------------------------------------------
| CALLBACK MIDTRANS
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback',[PembayaranController::class, 'callback'])->name('midtrans.callback');

require __DIR__ . '/auth.php';
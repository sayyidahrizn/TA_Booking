<?php


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

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA & REGISTER
|--------------------------------------------------------------------------
*/

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

// Halaman Depan Publik
Route::get('/', [FasilitasController::class, 'landingPage'])->name('landing');

// Route CRUD Admin
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

    // Dashboard
    Route::get('/user/dashboard', [UserPenyewaan::class, 'dashboard'])
        ->name('user.dashboard');

    // Fasilitas
    Route::get('/user/fasilitas', [FasilitasController::class, 'indexUser'])
        ->name('user.fasilitas.index');

    Route::get('/fasilitas/{id}', [FasilitasController::class, 'show'])
        ->name('fasilitas.show');

    // Penyewaan
    Route::get('/user/penyewaan', [UserPenyewaan::class, 'index'])
        ->name('user.penyewaan.index');

    Route::get('/user/penyewaan/baru', [UserPenyewaan::class, 'create'])
        ->name('user.penyewaan.create');

    Route::post('/user/penyewaan/simpan', [UserPenyewaan::class, 'store'])
        ->name('user.penyewaan.store');

    Route::get('/user/riwayat', [UserPenyewaan::class, 'riwayat'])
        ->name('user.riwayat');

    Route::get('/user/penyewaan/detail/{id}', [UserPenyewaan::class, 'show'])
        ->name('user.penyewaan.show');

    // Pembayaran Midtrans
    Route::get('/user/pembayaran/{id}', [UserPenyewaan::class, 'pembayaran'])
        ->name('user.pembayaran.index');

    Route::post('/user/pembayaran/simpan/{id}', [UserPenyewaan::class, 'pembayaranStore'])
        ->name('user.pembayaran.store');

    // =========================
    // 🔥 PROFILE (SUDAH FIX)
    // =========================
    Route::get('/user/profile', [ProfileController::class, 'edit'])
        ->name('user.profile');

    Route::post('/user/profile/update', [ProfileController::class, 'update'])
        ->name('user.profile.update');

    // Pengembalian
    Route::get('/user/pengembalian', [PengembalianController::class, 'index'])
        ->name('user.pengembalian');

    Route::post('/user/pengembalian/store', [PengembalianController::class, 'store'])
        ->name('user.pengembalian.store');

    Route::get('/pengembalian/bayar-denda/{id}', [App\Http\Controllers\User\PengembalianController::class, 'bayarDenda'])
        ->name('user.pengembalian.bayar');

});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'isAdmin'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [AdminPenyewaan::class, 'dashboard'])
        ->name('admin.dashboard');

    // FITUR PEMBAYARAN (BARU)
    Route::get('/pembayaran', [AdminPenyewaan::class, 'pembayaran'])
        ->name('admin.pembayaran.index');

    // Laporan user
    Route::get('/users/laporan', [UserController::class, 'laporan'])
        ->name('users.laporan');

    // Kelola User & Fasilitas
    Route::resource('users', UserController::class);
    Route::resource('fasilitas', FasilitasController::class);

    // Hapus gambar fasilitas
    Route::delete('/fasilitas/gambar/{id}', [FasilitasController::class, 'hapusGambar'])
        ->name('fasilitas.gambar.hapus');

    // Penyewaan admin
    Route::get('/penyewaan', [AdminPenyewaan::class, 'index'])
        ->name('admin.penyewaan.index');

    Route::post('/penyewaan/konfirmasi-group/{kode}', [AdminPenyewaan::class, 'konfirmasiGroup'])
        ->name('admin.penyewaan.konfirmasi.group');

    Route::post('/penyewaan/tolak-group/{kode}', [AdminPenyewaan::class, 'tolakGroup'])
        ->name('admin.penyewaan.tolak.group');

    Route::delete('/penyewaan/{id}', [AdminPenyewaan::class, 'destroy'])
        ->name('admin.penyewaan.destroy');

    Route::post('/pengembalian/validasi', [AdminPengembalianController::class, 'validasi'])
        ->name('admin.pengembalian.validasi');

    // 🔥 PROFILE ADMIN (FIX JUGA)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('admin.profile');

    Route::post('/profile/update', [ProfileController::class, 'update'])
        ->name('admin.profile.update');

    Route::get('/pengembalian', [AdminPengembalianController::class, 'index'])
        ->name('admin.pengembalian');


});

/*
|--------------------------------------------------------------------------
| CALLBACK MIDTRANS
|--------------------------------------------------------------------------
*/
Route::post('/midtrans/callback', [UserPenyewaan::class, 'callback'])
    ->name('midtrans.callback');

// Route::get('/user/pembayaran/{id}', [PenyewaanController::class, 'pembayaran'])
//     ->name('user.penyewaan.pembayaran');

// Route::post('/midtrans/callback', [PenyewaanController::class, 'callback']);

use App\Http\Controllers\User\PembayaranController;

// Route::get('/user/pembayaran/{id}', [PembayaranController::class, 'index'])
//     ->name('user.penyewaan.pembayaran');

Route::post('/midtrans/callback', [PembayaranController::class, 'callback']);

Route::get('/user/pembayaran/{id}', [PembayaranController::class, 'index'])
    ->name('user.pembayaran.index');
/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
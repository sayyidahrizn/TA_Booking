<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\PenyewaanController as UserPenyewaan;
use App\Http\Controllers\Admin\PenyewaanController as AdminPenyewaan;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA & REGISTER
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('beranda');
});

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

/*
|----------------------------------------------------------
| REDIRECT DASHBOARD SETELAH LOGIN
|----------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if(auth()->user()->role === 'kaur'){
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|----------------------------------------------------------
| USER ROUTES (LENGKAP)
|----------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/user/dashboard', [UserPenyewaan::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/fasilitas', [FasilitasController::class, 'indexUser'])->name('user.fasilitas.index');
    Route::get('/fasilitas/{id}', [FasilitasController::class, 'show'])->name('fasilitas.show');

    // Penyewaan & Riwayat
    Route::get('/user/penyewaan', [UserPenyewaan::class, 'index'])->name('user.penyewaan.index');
    Route::get('/user/penyewaan/baru', [UserPenyewaan::class, 'create'])->name('user.penyewaan.create');
    Route::post('/user/penyewaan/simpan', [UserPenyewaan::class, 'store'])->name('user.penyewaan.store');
    Route::get('/user/riwayat', [UserPenyewaan::class, 'riwayat'])->name('user.riwayat');
    Route::get('/user/penyewaan/detail/{id}', [UserPenyewaan::class, 'show'])->name('user.penyewaan.show');

    // Pembayaran
    Route::get('/user/pembayaran/{id}', [UserPenyewaan::class, 'pembayaran'])->name('user.pembayaran.index');
    Route::post('/user/pembayaran/simpan/{id}', [UserPenyewaan::class, 'pembayaranStore'])->name('user.pembayaran.store');
});

/*
|----------------------------------------------------------
| ADMIN ROUTES
|----------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth','isAdmin'])->group(function () {
    Route::get('/dashboard', [AdminPenyewaan::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('users', UserController::class);
    Route::resource('fasilitas', FasilitasController::class);

    // ROUTE TAMBAHAN UNTUK HAPUS GAMBAR
    Route::delete('/fasilitas/gambar/{id}', [FasilitasController::class, 'hapusGambar'])
        ->name('fasilitas.gambar.hapus');

    Route::get('/penyewaan', [AdminPenyewaan::class, 'index'])->name('admin.penyewaan.index');
    Route::post('/penyewaan/konfirmasi-group/{kode}', [AdminPenyewaan::class, 'konfirmasiGroup'])->name('admin.penyewaan.konfirmasi.group');
    Route::post('/penyewaan/tolak-group/{kode}', [AdminPenyewaan::class, 'tolakGroup'])->name('admin.penyewaan.tolak.group');
    Route::delete('/penyewaan/{id}', [AdminPenyewaan::class, 'destroy'])->name('admin.penyewaan.destroy');
});

require __DIR__.'/auth.php';
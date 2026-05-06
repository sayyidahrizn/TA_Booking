<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    use HasFactory;

    protected $table = 'penyewaan';
    protected $primaryKey = 'id_penyewaan';

    protected $fillable = [
        'kode_booking', 
        'id_user', 
        'id_fasilitas', 
        'jumlah_sewa', 
        'nama_penyewa', 
        'nik', 
        'tgl_mulai', 
        'tgl_selesai', 
        'keterangan', 
        'total_harga',  
        'status_sewa', 
        'status_pengembalian', 
        'tanggal_harus_kembali',
    ];

    /**
     * Relasi ke model Pembayaran (PENTING: Agar error hilang)
     */
    public function pembayaran() {
        // Satu penyewaan bisa memiliki banyak transaksi pembayaran (DP, Pelunasan, Denda)
        return $this->hasMany(Pembayaran::class, 'id_penyewaan', 'id_penyewaan');
    }

    /**
     * Relasi ke model Denda
     */
    public function denda() {
        return $this->hasOne(Denda::class, 'id_penyewaan', 'id_penyewaan');
    }

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function fasilitas() {
        return $this->belongsTo(Fasilitas::class, 'id_fasilitas', 'id_fasilitas');
    }

    public function pengembalian() {
        return $this->hasOne(Pengembalian::class, 'id_penyewaan', 'id_penyewaan');
    }
}
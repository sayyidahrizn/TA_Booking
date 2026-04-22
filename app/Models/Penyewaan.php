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
        'status_pembayaran', 
        'status_sewa', 
        'sisa_pembayaran', 
        'status_pengembalian', 
        'tanggal_harus_kembali',

        // 🔥 TAMBAHAN MIDTRANS
        'snap_token',
        'order_id'
    ];

    /**
     * Relasi ke model User
     */
    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Relasi ke model Fasilitas
     */
    public function fasilitas() {
        return $this->belongsTo(Fasilitas::class, 'id_fasilitas', 'id_fasilitas');
    }

    /**
     * Relasi ke model Pengembalian
     */
    public function pengembalian() {
        return $this->hasOne(Pengembalian::class, 'id_penyewaan', 'id_penyewaan');
    }
}
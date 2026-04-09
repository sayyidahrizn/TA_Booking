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
        'kode_booking', 'id_user', 'id_fasilitas', 'nama_penyewa', 'nik', 
        'tgl_mulai', 'tgl_selesai', 'keterangan', 
        'total_harga', 'status_pembayaran', 'status_sewa'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class, 'id_fasilitas');
    }
}
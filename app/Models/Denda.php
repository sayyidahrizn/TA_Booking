<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';
    protected $primaryKey = 'id_denda';

    protected $fillable = [
        'id_penyewaan',
        'biaya_keterlambatan',
        'biaya_kerusakan',
        'total_denda',
        'keterangan_kerusakan',
        'status_denda', // 'belum_bayar', 'lunas'
        'snap_token',   // Untuk menyimpan token Midtrans
        'tanggal_bayar'
    ];

    /**
     * Relasi ke model Penyewaan.
     * Fungsi ini sangat penting agar kamu bisa memanggil $denda->penyewaan->fasilitas 
     * di dalam view pengembalian.
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }
}
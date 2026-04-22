<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    // Nama tabel sesuai screenshot database Anda
    protected $table = 'pengembalian';

    protected $fillable = [
        'id_penyewaan',
        'tanggal_pengembalian',
        'bukti_pengembalian',
        'status_validasi',
        'denda_telat',
        'denda_rusak',
        'total_denda',
        'catatan_admin',
        'status_pembayaran_denda',
        'snap_token_denda',
    ];

    /**
     * Relasi balik ke Penyewaan
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }
}
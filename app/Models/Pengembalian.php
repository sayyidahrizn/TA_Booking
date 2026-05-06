<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';

    protected $fillable = [
        'id_penyewaan',
        'tanggal_pengembalian',
        'bukti_pengembalian',
        'status_validasi',
        'catatan_admin',
    ];

    /**
     * Relasi balik ke Penyewaan
     */
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }
}
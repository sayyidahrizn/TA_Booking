<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = 'pengembalian';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_penyewaan',
        'tanggal_pengembalian',
        'bukti_pengembalian',
        'status_validasi',
        'catatan_admin',
    ];

    /**
     * Relasi ke Penyewaan
     */
    public function penyewaan()
    {
        return $this->belongsTo(
            Penyewaan::class,
            'id_penyewaan',
            'id_penyewaan'
        );
    }

    public function denda()
    {
        return $this->hasOne(Denda::class, 'id_penyewaan', 'id_penyewaan');
    }
}
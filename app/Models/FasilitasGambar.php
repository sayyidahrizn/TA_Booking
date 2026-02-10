<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasGambar extends Model
{
    protected $table = 'fasilitas_gambar';
    protected $primaryKey = 'id_gambar';

    protected $fillable = [
        'id_fasilitas',
        'file_gambar'
    ];

    // banyak gambar milik satu fasilitas
    public function fasilitas()
    {
        return $this->belongsTo(
            Fasilitas::class,
            'id_fasilitas',
            'id_fasilitas'
        );
    }
}

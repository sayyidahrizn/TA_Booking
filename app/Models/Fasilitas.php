<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    protected $table = 'fasilitas';
    protected $primaryKey = 'id_fasilitas';

    protected $fillable = [
        'nama_fasilitas',
        'deskripsi',
        'harga_sewa',
        'status_fasilitas'
    ];

    // 1 fasilitas punya banyak gambar
    public function gambar()
    {
        return $this->hasMany(
            FasilitasGambar::class,
            'id_fasilitas',
            'id_fasilitas'
        );
    }
}

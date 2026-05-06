<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    
    protected $fillable = [
        'id_penyewaan', 
        'kode_pembayaran', 
        'jenis_pembayaran', 
        'metode_pembayaran', 
        'jumlah_bayar', 
        'bukti_pembayaran', 
        'status_pembayaran', 
        'snap_token', 
        'order_id', 
        'catatan_admin', 
        'tanggal_bayar'
    ];

    /**
     * Relasi balik ke model Penyewaan
     */
    public function penyewaan() {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }
}
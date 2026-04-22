<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Primary key custom
     */
    protected $primaryKey = 'id';

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'name',
        'nik',
        'email',
        'password',
        'photo',
        'role',
        'no_hp',
        'alamat',
  
    ];

    /**
     * Kolom tersembunyi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast atribut
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class, 'id_user', 'id');
    }


}

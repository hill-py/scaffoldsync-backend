<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'no_telepon',
        'status_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status_aktif' => 'boolean',
        ];
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'id_user', 'id_user');
    }

    public function pesananAdmin(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_admin', 'id_user');
    }

    public function jadwalMandor(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'id_mandor', 'id_user');
    }
}
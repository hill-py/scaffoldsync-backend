<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';

    protected $fillable = [
        'no_pesanan',
        'id_pelanggan',
        'id_proyek',
        'id_admin',
        'tgl_mulai_sewa',
        'tgl_selesai_sewa',
        'status',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'id_proyek', 'id_proyek');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin', 'id_user');
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function penawaran(): HasOne
    {
        return $this->hasOne(Penawaran::class, 'id_pesanan', 'id_pesanan');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'id_pesanan', 'id_pesanan');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id_pesanan', 'id_pesanan');
    }
}
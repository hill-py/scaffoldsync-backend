<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_pesanan',
        'id_mandor',
        'jenis',
        'tgl_jadwal',
        'status',
        'catatan',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function mandor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_mandor', 'id_user');
    }

    public function suratJalan(): HasOne
    {
        return $this->hasOne(SuratJalan::class, 'id_jadwal', 'id_jadwal');
    }

    public function inspeksi(): HasOne
    {
        return $this->hasOne(Inspeksi::class, 'id_jadwal', 'id_jadwal');
    }
}
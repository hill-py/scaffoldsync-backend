<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'id_invoice';

    protected $fillable = [
        'no_dokumen',
        'id_pesanan',
        'tgl_terbit',
        'total_sewa',
        'total_klaim',
        'total_tagihan',
        'status',
        'jatuh_tempo',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_invoice', 'id_invoice');
    }
}
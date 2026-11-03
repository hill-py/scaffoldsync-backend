<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_invoice',
        'tgl_bayar',
        'jumlah',
        'metode',
        'catatan',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'id_invoice', 'id_invoice');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penawaran extends Model
{
    protected $table = 'penawaran';
    protected $primaryKey = 'id_penawaran';

    protected $fillable = [
        'no_dokumen',
        'id_pesanan',
        'tgl_terbit',
        'total',
        'status',
        'tgl_persetujuan',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
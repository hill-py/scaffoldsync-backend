<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailInspeksi extends Model
{
    protected $table = 'detail_inspeksi';
    protected $primaryKey = 'id_detail_inspeksi';

    protected $fillable = [
        'id_inspeksi',
        'id_barang',
        'jumlah_baik',
        'jumlah_rusak',
        'jumlah_hilang',
    ];

    public function inspeksi(): BelongsTo
    {
        return $this->belongsTo(Inspeksi::class, 'id_inspeksi', 'id_inspeksi');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
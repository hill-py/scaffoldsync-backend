<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspeksi extends Model
{
    protected $table = 'inspeksi';
    protected $primaryKey = 'id_inspeksi';

    protected $fillable = [
        'id_jadwal',
        'tgl_inspeksi',
        'catatan',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal', 'id_jadwal');
    }

    public function detailInspeksi(): HasMany
    {
        return $this->hasMany(DetailInspeksi::class, 'id_inspeksi', 'id_inspeksi');
    }
}
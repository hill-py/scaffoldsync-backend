<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratJalan extends Model
{
    protected $table = 'surat_jalan';
    protected $primaryKey = 'id_surat_jalan';

    protected $fillable = [
        'no_dokumen',
        'id_jadwal',
        'tgl_terbit',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'id_jadwal', 'id_jadwal');
    }
}
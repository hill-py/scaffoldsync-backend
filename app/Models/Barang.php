<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga_sewa_per_hari',
        'harga_ganti',
        'stok_total',
        'stok_tersedia',
        'stok_disewa',
        'stok_rusak',
    ];

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'id_barang', 'id_barang');
    }

    public function detailInspeksi(): HasMany
    {
        return $this->hasMany(DetailInspeksi::class, 'id_barang', 'id_barang');
    }
}
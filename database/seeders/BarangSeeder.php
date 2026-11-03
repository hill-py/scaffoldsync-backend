<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $barangs = [
            [
                'kode_barang' => 'SCF-170',
                'nama_barang' => 'Main Frame 170 cm',
                'kategori' => 'scaffolding',
                'harga_sewa_per_hari' => 5000.00,
                'harga_ganti' => 150000.00,
                'stok_total' => 500,
                'stok_tersedia' => 500,
                'stok_disewa' => 0,
                'stok_rusak' => 0,
            ],
            [
                'kode_barang' => 'SCF-090',
                'nama_barang' => 'Leader Frame 90 cm',
                'kategori' => 'scaffolding',
                'harga_sewa_per_hari' => 3500.00,
                'harga_ganti' => 110000.00,
                'stok_total' => 300,
                'stok_tersedia' => 300,
                'stok_disewa' => 0,
                'stok_rusak' => 0,
            ],
            [
                'kode_barang' => 'JCB-060',
                'nama_barang' => 'Jackbase 60 cm',
                'kategori' => 'jackbase',
                'harga_sewa_per_hari' => 2000.00,
                'harga_ganti' => 60000.00,
                'stok_total' => 400,
                'stok_tersedia' => 400,
                'stok_disewa' => 0,
                'stok_rusak' => 0,
            ],
            [
                'kode_barang' => 'CLP-SWV',
                'nama_barang' => 'Swivel Clamp 1.5 inch',
                'kategori' => 'clamp',
                'harga_sewa_per_hari' => 1500.00,
                'harga_ganti' => 45000.00,
                'stok_total' => 600,
                'stok_tersedia' => 600,
                'stok_disewa' => 0,
                'stok_rusak' => 0,
            ],
            [
                'kode_barang' => 'CLP-FIX',
                'nama_barang' => 'Fixed Clamp 1.5 inch',
                'kategori' => 'clamp',
                'harga_sewa_per_hari' => 1500.00,
                'harga_ganti' => 40000.00,
                'stok_total' => 600,
                'stok_tersedia' => 600,
                'stok_disewa' => 0,
                'stok_rusak' => 0,
            ],
        ];

        foreach ($barangs as $barang) {
            Barang::create($barang);
        }
    }
}
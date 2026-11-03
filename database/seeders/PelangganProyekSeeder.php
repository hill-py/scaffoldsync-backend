<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\Proyek;
use Illuminate\Database\Seeder;

class PelangganProyekSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = Pelanggan::create([
            'id_user' => 4, // ID user role pelanggan dari UserSeeder
            'nama_pelanggan' => 'PT Karya Utama',
            'perusahaan' => 'PT Karya Utama Konstruksi',
            'alamat' => 'Jl. Jendral Sudirman No. 45, Solo',
            'no_telepon' => '081234567893',
        ]);

        Proyek::create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'nama_proyek' => 'Pembangunan Gedung Wisma 88',
            'alamat_proyek' => 'Jl. Slamet Riyadi No. 100, Solo',
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pemilik (Owner)
        User::create([
            'nama' => 'Pemilik Scaffolding',
            'email' => 'owner@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'pemilik',
            'no_telepon' => '081234567890',
            'status_aktif' => true,
        ]);

        // 2. Admin Gudang
        User::create([
            'nama' => 'Admin Gudang',
            'email' => 'admin@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_telepon' => '081234567891',
            'status_aktif' => true,
        ]);

        // 3. Mandor Lapangan
        User::create([
            'nama' => 'Mandor Lapangan',
            'email' => 'mandor@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'mandor',
            'no_telepon' => '081234567892',
            'status_aktif' => true,
        ]);

        // 4. Pelanggan Demo
        User::create([
            'nama' => 'Budi Kontraktor',
            'email' => 'pelanggan@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
            'no_telepon' => '081234567893',
            'status_aktif' => true,
        ]);
    }
}
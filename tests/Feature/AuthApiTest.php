<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_login_dengan_kredensial_valid(): void
    {
        $user = User::create([
            'nama' => 'Admin Gudang',
            'email' => 'admin@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status_aktif' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@scaffoldsync.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user' => ['id_user', 'nama', 'email', 'role'],
            ]);
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        User::create([
            'nama' => 'Admin Gudang',
            'email' => 'admin@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status_aktif' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@scaffoldsync.com',
            'password' => 'password_salah',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_non_aktif_ditolak_saat_login(): void
    {
        User::create([
            'nama' => 'User Non Aktif',
            'email' => 'blocked@scaffoldsync.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
            'status_aktif' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'blocked@scaffoldsync.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Akun Anda sedang tidak aktif. Silakan hubungi admin.',
            ]);
    }
}
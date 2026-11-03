<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPesanan;
use App\Models\Jadwal;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Proyek;
use App\Models\User;
use App\Services\ConflictValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConflictValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConflictValidationService();
    }

    public function test_mendeteksi_stok_tersedia_jika_tidak_ada_bentrok(): void
    {
        $barang = Barang::create([
            'kode_barang' => 'SCF-170',
            'nama_barang' => 'Main Frame 170',
            'kategori' => 'scaffolding',
            'harga_sewa_per_hari' => 5000,
            'harga_ganti' => 150000,
            'stok_total' => 100,
            'stok_tersedia' => 100,
            'stok_disewa' => 0,
            'stok_rusak' => 0,
        ]);

        $result = $this->service->checkStockAvailability(
            $barang->id_barang,
            50,
            '2026-12-01',
            '2026-12-05'
        );

        $this->assertTrue($result['is_available']);
        $this->assertEquals(100, $result['stok_tersedia']);
    }

    public function test_mendeteksi_bentrok_stok_jika_melebihi_ketersediaan_periode_sewa(): void
    {
        $user = User::create(['nama' => 'Pelanggan 1', 'email' => 'p1@test.com', 'password' => 'secret', 'role' => 'pelanggan']);
        $pelanggan = Pelanggan::create(['id_user' => $user->id_user, 'nama_pelanggan' => 'PT A', 'perusahaan' => 'PT A', 'alamat' => 'Solo', 'no_telepon' => '08123']);
        $proyek = Proyek::create(['id_pelanggan' => $pelanggan->id_pelanggan, 'nama_proyek' => 'Proyek A', 'alamat_proyek' => 'Solo']);

        $barang = Barang::create([
            'kode_barang' => 'SCF-170',
            'nama_barang' => 'Main Frame 170',
            'kategori' => 'scaffolding',
            'harga_sewa_per_hari' => 5000,
            'harga_ganti' => 150000,
            'stok_total' => 50,
            'stok_tersedia' => 50,
            'stok_disewa' => 0,
            'stok_rusak' => 0,
        ]);

        // Pesanan aktif yang mengikat 40 unit stok pada tanggal 10-15 Des 2026
        $pesanan = Pesanan::create([
            'no_pesanan' => 'ORD-TEST-001',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_proyek' => $proyek->id_proyek,
            'tgl_mulai_sewa' => '2026-12-10',
            'tgl_selesai_sewa' => '2026-12-15',
            'status' => 'disetujui',
        ]);

        DetailPesanan::create([
            'id_pesanan' => $pesanan->id_pesanan,
            'id_barang' => $barang->id_barang,
            'jumlah' => 40,
            'harga_satuan' => 5000,
            'subtotal' => 1200000,
        ]);

        // Permintaan 20 unit di tanggal beririsan (12-14 Des 2026) -> Sisa stok hanya 10
        $result = $this->service->checkStockAvailability(
            $barang->id_barang,
            20,
            '2026-12-12',
            '2026-12-14'
        );

        $this->assertFalse($result['is_available']);
        $this->assertEquals(10, $result['stok_tersedia']);
    }

    public function test_mendeteksi_bentrok_jadwal_penugasan_mandor(): void
    {
        $mandor = User::create(['nama' => 'Mandor Budi', 'email' => 'budi@test.com', 'password' => 'secret', 'role' => 'mandor']);
        $pelanggan = Pelanggan::create(['id_user' => $mandor->id_user, 'nama_pelanggan' => 'PT B', 'perusahaan' => 'PT B', 'alamat' => 'Solo', 'no_telepon' => '08124']);
        $proyek = Proyek::create(['id_pelanggan' => $pelanggan->id_pelanggan, 'nama_proyek' => 'Proyek B', 'alamat_proyek' => 'Solo']);
        
        $pesanan = Pesanan::create([
            'no_pesanan' => 'ORD-TEST-002',
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_proyek' => $proyek->id_proyek,
            'tgl_mulai_sewa' => '2026-12-10',
            'tgl_selesai_sewa' => '2026-12-15',
            'status' => 'disetujui',
        ]);

        Jadwal::create([
            'id_pesanan' => $pesanan->id_pesanan,
            'id_mandor' => $mandor->id_user,
            'jenis' => 'pengiriman',
            'tgl_jadwal' => '2026-12-10',
            'status' => 'terjadwal',
        ]);

        // Cek bentrok penugasan mandor pada tanggal yang sama (10 Des 2026)
        $result = $this->service->checkMandorAvailability($mandor->id_user, '2026-12-10');

        $this->assertFalse($result['is_available']);
    }
}
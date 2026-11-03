<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->onDelete('cascade');
            $table->foreignId('id_mandor')->constrained('users', 'id_user')->onDelete('cascade');
            $table->enum('jenis', ['pengiriman', 'pengambilan']);
            $table->date('tgl_jadwal');
            $table->enum('status', ['terjadwal', 'selesai', 'gagal', 'dijadwalkan_ulang'])->default('terjadwal');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};

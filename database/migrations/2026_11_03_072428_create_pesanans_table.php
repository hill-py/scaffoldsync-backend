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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->string('no_pesanan', 30)->unique();
            $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->onDelete('cascade');
            $table->foreignId('id_proyek')->constrained('proyek', 'id_proyek')->onDelete('cascade');
            $table->foreignId('id_admin')->nullable()->constrained('users', 'id_user')->onDelete('set null');
            $table->date('tgl_mulai_sewa');
            $table->date('tgl_selesai_sewa');
            $table->enum('status', ['draft', 'menunggu_persetujuan', 'disetujui', 'dibatalkan', 'dijadwalkan', 'berjalan', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};

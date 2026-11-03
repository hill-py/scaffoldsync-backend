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
        Schema::create('barang', function (Blueprint $table) {
            $table->id('id_barang');
            $table->string('kode_barang', 30)->unique();
            $table->string('nama_barang', 100);
            $table->enum('kategori', ['scaffolding', 'jackbase', 'clamp']);
            $table->decimal('harga_sewa_per_hari', 12, 2);
            $table->decimal('harga_ganti', 12, 2);
            $table->integer('stok_total')->default(0);
            $table->integer('stok_tersedia')->default(0);
            $table->integer('stok_disewa')->default(0);
            $table->integer('stok_rusak')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};

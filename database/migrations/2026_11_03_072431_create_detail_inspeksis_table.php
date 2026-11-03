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
        Schema::create('inspeksi', function (Blueprint $table) {
            $table->id('id_inspeksi');
            $table->foreignId('id_jadwal')->constrained('jadwal', 'id_jadwal')->onDelete('cascade');
            $table->date('tgl_inspeksi');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_inspeksis');
    }
};

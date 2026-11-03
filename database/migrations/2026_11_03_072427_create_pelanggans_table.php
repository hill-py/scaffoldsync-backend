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
    Schema::create('pelanggan', function (Blueprint $table) {
        $table->id('id_pelanggan');
        $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->onDelete('set null');
        $table->string('nama_pelanggan', 100);
        $table->string('perusahaan', 100)->nullable();
        $table->text('alamat');
        $table->string('no_telepon', 20);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};

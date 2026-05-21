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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('nama_meja');
            $table->enum('zona', ['rooftop_a', 'rooftop_b', 'indoor', 'bangku', 'lesehan']);
            $table->integer('kapasitas');
            $table->enum('status', ['tersedia', 'terisi', 'pesanan_masuk', 'perlu_dibersihkan'])->default('tersedia');
            $table->integer('posisi_x')->default(0);
            $table->integer('posisi_y')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};

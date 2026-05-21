<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('nama_promo');
            $table->enum('tipe', ['persen', 'nominal', 'bogo', 'member']);
            $table->integer('nilai');
            $table->boolean('aktif')->default(true);
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};

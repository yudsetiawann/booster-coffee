<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix Inkonsistensi #1 & #2:
     * - order_items.harga_saat_pesan: integer -> decimal(10,2) agar sesuai Model cast
     * - payments.jumlah_bayar: integer -> decimal(10,2) agar sesuai Model cast
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('harga_saat_pesan', 10, 2)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('jumlah_bayar', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('harga_saat_pesan')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('jumlah_bayar')->change();
        });
    }
};

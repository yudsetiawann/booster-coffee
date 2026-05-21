<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('menunggu_pembayaran', 'pending', 'diproses', 'selesai') DEFAULT 'menunggu_pembayaran'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('pending', 'diproses', 'selesai') DEFAULT 'pending'");
    }
};

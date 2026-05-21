<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->integer('dp_amount')->default(0)->after('status');
            $table->enum('dp_status', ['belum_bayar', 'sudah_bayar'])->default('belum_bayar')->after('dp_amount');
            $table->enum('dp_metode', ['tunai', 'transfer', 'qris'])->nullable()->after('dp_status');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['dp_amount', 'dp_status', 'dp_metode']);
        });
    }
};

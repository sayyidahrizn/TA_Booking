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
        Schema::table('pengembalian', function (Blueprint $table) {
            // Menambahkan status pembayaran denda dan snap token untuk Midtrans
            $table->enum('status_pembayaran_denda', ['pending', 'lunas'])->nullable()->after('total_denda');
            $table->string('snap_token_denda')->nullable()->after('status_pembayaran_denda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran_denda', 'snap_token_denda']);
        });
    }
};

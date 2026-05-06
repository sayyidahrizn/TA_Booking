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
            // Menghapus kolom denda yang sudah pindah ke tabel 'denda' dan 'pembayaran'
            $table->dropColumn([
                'denda_telat', 
                'denda_rusak', 
                'total_denda', 
                'status_pembayaran_denda', 
                'snap_token_denda'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            // Untuk mengembalikan jika terjadi kesalahan
            $table->decimal('denda_telat', 12, 2)->nullable();
            $table->decimal('denda_rusak', 12, 2)->nullable();
            $table->decimal('total_denda', 12, 2)->nullable();
            $table->enum('status_pembayaran_denda', ['pending', 'lunas'])->nullable();
            $table->string('snap_token_denda')->nullable();
        });
    }
};

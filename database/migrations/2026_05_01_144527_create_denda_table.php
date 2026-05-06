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
        Schema::create('denda', function (Blueprint $table) {
            $table->id('id_denda');
            $table->unsignedBigInteger('id_penyewaan');
            
            $table->decimal('biaya_keterlambatan', 15, 2)->default(0);
            $table->decimal('biaya_kerusakan', 15, 2)->default(0);
            $table->decimal('total_denda', 15, 2);
            $table->text('keterangan_kerusakan')->nullable();
            $table->enum('status_denda', ['belum_bayar', 'lunas']);
            $table->timestamps();

            // Foreign Key
            $table->foreign('id_penyewaan')->references('id_penyewaan')->on('penyewaan')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denda');
    }
};

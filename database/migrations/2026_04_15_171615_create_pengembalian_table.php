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
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->id();

            // relasi ke tabel penyewaan
            $table->unsignedBigInteger('id_penyewaan');

            $table->foreign('id_penyewaan')
                  ->references('id_penyewaan') // sesuaikan PK tabel penyewaan
                  ->on('penyewaan')
                  ->onDelete('cascade');

            // tanggal pengembalian
            $table->date('tanggal_pengembalian');

            // bukti foto
            $table->string('bukti_pengembalian')->nullable();

            // status validasi admin
            $table->enum('status_validasi', ['pending', 'disetujui', 'ditolak'])
                  ->default('pending');

            // denda
            $table->decimal('denda_telat', 12, 2)->default(0);
            $table->decimal('denda_rusak', 12, 2)->default(0);
            $table->decimal('total_denda', 12, 2)->default(0);

            // catatan admin
            $table->text('catatan_admin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian');
    }
};
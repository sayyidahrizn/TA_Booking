<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewaan', function (Blueprint $table) {
            $table->id('id_penyewaan');
            
            // Menghubungkan ke tabel Users (Penyewa)
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            
            // Menghubungkan ke tabel Fasilitas
            $table->unsignedBigInteger('id_fasilitas');
            $table->foreign('id_fasilitas')->references('id_fasilitas')->on('fasilitas')->onDelete('cascade');

            $table->string('nama_penyewa');
            $table->string('nik', 16)->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->text('keterangan')->nullable();
            $table->integer('total_harga')->nullable();
            
            // Status untuk alur otomatisasi kita
            $table->enum('status_pembayaran', ['pending', 'lunas', 'batal'])->default('pending');
            $table->enum('status_sewa', ['prosess', 'disetujui', 'selesai', 'batal'])->default('prosess');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyewaan');
    }
};
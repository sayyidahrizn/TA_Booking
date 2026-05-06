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
    Schema::create('pembayaran', function (Blueprint $table) {
        $table->id('id_pembayaran');
        // Menghubungkan ke primary key 'id_penyewaan' di tabel penyewaan
        $table->unsignedBigInteger('id_penyewaan');
        
        $table->string('kode_pembayaran')->unique();
        $table->enum('jenis_pembayaran', ['dp', 'pelunasan', 'denda']);
        $table->enum('metode_pembayaran', ['midtrans', 'tunai']);
        $table->decimal('jumlah_bayar', 15, 2);
        $table->string('bukti_pembayaran')->nullable(); // Untuk upload struk tunai
        $table->enum('status_pembayaran', ['pending', 'berhasil', 'gagal', 'diverifikasi']);
        
        // Kolom khusus Midtrans
        $table->string('snap_token')->nullable();
        $table->string('order_id')->nullable();
        
        $table->text('catatan_admin')->nullable();
        $table->timestamp('tanggal_bayar')->nullable();
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
        Schema::dropIfExists('pembayaran');
    }
};

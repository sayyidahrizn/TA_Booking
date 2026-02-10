<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fasilitas_gambar', function (Blueprint $table) {
            $table->id('id_gambar');

            $table->foreignId('id_fasilitas')
                  ->constrained('fasilitas', 'id_fasilitas')
                  ->cascadeOnDelete();

            $table->string('file_gambar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas_gambar');
    }
};

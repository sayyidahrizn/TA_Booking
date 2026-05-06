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
        Schema::table('penyewaan', function (Blueprint $table) {
            // Cek satu per satu sebelum dihapus
            if (Schema::hasColumn('penyewaan', 'status_pembayaran')) {
                $table->dropColumn('status_pembayaran');
            }
            if (Schema::hasColumn('penyewaan', 'snap_token')) {
                $table->dropColumn('snap_token');
            }
            if (Schema::hasColumn('penyewaan', 'order_id')) {
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('penyewaan', 'sisa_pembayaran')) {
                $table->dropColumn('sisa_pembayaran');
            }
            if (Schema::hasColumn('penyewaan', 'status_pengembalian')) {
                $table->dropColumn('status_pengembalian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyewaan', function (Blueprint $table) {
            // Balikkan kolom jika rollback (opsional)
            $table->string('status_pembayaran')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('order_id')->nullable();
            $table->integer('sisa_pembayaran')->nullable();
            $table->string('status_pengembalian')->nullable();
        });
    }
};

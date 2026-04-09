<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penyewaan', function (Blueprint $table) {
            $table->string('kode_booking')->nullable()->after('id_penyewaan');
        });
    }

    public function down()
    {
        Schema::table('penyewaan', function (Blueprint $table) {
            $table->dropColumn('kode_booking');
        });
    }
};
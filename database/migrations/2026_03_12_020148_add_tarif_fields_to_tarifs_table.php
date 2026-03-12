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
        Schema::table('tarifs', function (Blueprint $table) {
            $table->integer('tarif_per_menit')->default(50)->after('jenis_kendaraan');
            $table->integer('tarif_akumulasi_menit')->nullable()->after('tarif_per_jam');
            $table->integer('tarif_akumulasi_jam')->nullable()->after('tarif_akumulasi_menit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarifs', function (Blueprint $table) {
            $table->dropColumn(['tarif_per_menit', 'tarif_akumulasi_menit', 'tarif_akumulasi_jam']);
        });
    }
};

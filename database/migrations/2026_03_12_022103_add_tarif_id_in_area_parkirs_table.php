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
        Schema::table('area_parkirs', function (Blueprint $table) {
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->foreign('tarif_id')->references('id')->on('tarifs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_parkirs', function (Blueprint $table) {
            $table->dropForeign(['tarif_id']);
            $table->dropColumn('tarif_id');
        });
    }
};

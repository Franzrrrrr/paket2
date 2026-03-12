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
        Schema::create('area_parkir_tarif', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_parkir_id')->constrained('area_parkirs')->onDelete('cascade');
            $table->foreignId('tarif_id')->constrained('tarifs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['area_parkir_id', 'tarif_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_parkir_tarif');
    }
};

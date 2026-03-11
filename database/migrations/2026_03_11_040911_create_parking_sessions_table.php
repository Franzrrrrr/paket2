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
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('vehicle_id')->constrained('kendaraans');
            $table->foreignId('parking_area_id')->constrained('area_parkirs');
            $table->dateTime('entry_time');
            $table->dateTime('exit_time')->nullable();
            $table->integer('duration')->nullable(); // in minutes
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['ticket_code', 'status']);
            $table->index(['parking_area_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};

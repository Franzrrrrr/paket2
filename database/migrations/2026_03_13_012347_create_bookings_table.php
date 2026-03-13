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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parking_area_id')->constrained('area_parkirs')->onDelete('cascade');
            $table->string('vehicle_type'); // Mobil, Motor
            $table->string('vehicle_plate');
            $table->integer('estimated_duration')->nullable(); // in minutes
            $table->timestamp('booking_time');
            $table->timestamp('check_in_time')->nullable();
            $table->string('status')->default('BOOKED'); // BOOKED, CHECKED_IN, CANCELLED, EXPIRED
            $table->string('ticket_code')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['parking_area_id', 'status']);
            $table->index(['ticket_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

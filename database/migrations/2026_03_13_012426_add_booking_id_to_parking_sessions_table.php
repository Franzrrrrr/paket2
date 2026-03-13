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
        Schema::table('parking_sessions', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type')->nullable()->after('vehicle_id'); // Mobil, Motor
            $table->string('vehicle_plate')->nullable()->after('vehicle_type');

            $table->index(['booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_sessions', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['booking_id', 'vehicle_type', 'vehicle_plate']);
        });
    }
};

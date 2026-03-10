<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('ticket_code')->unique();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->foreignId('parking_area_id')->constrained()->cascadeOnDelete();

            $table->timestamp('entry_time');

            $table->timestamp('exit_time')->nullable();

            $table->integer('duration_minutes')->nullable();

            $table->decimal('total_price', 10, 2)->nullable();

            $table->enum('status', [
                'active',
                'completed',
                'cancelled'
            ])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};

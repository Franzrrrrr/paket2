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
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->string('activity_type')->nullable()->after('aktivitas');
            $table->string('ip_address', 45)->nullable()->after('user_id');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('context')->nullable()->after('user_agent');
            $table->enum('log_level', ['debug', 'info', 'warning', 'error', 'critical'])->default('info')->after('context');
            $table->string('session_id')->nullable()->after('log_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropColumn(['activity_type', 'ip_address', 'user_agent', 'context', 'log_level', 'session_id']);
        });
    }
};

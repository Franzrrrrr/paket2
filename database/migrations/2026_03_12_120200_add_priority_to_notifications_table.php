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
        Schema::table('notifications', function (Blueprint $table) {
            // Check if priority column exists before adding
            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('data');
            }
            
            // Check if expires_at column exists before adding
            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('notifications', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};

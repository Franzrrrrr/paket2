<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, change the column to accommodate new values
        DB::statement("ALTER TABLE transaksis MODIFY status ENUM('masuk', 'keluar', 'aktif', 'selesai', 'dibatalkan') DEFAULT 'masuk'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaksis MODIFY status ENUM('masuk', 'keluar') DEFAULT 'masuk'");
    }
};

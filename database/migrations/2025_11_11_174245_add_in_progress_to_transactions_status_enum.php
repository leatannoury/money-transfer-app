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
        // Alter the ENUM column to include 'in_progress'
        // MySQL requires recreating the ENUM with all values
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` ENUM('completed', 'failed', 'pending_agent', 'in_progress') DEFAULT 'completed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values (remove 'in_progress')
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` ENUM('completed', 'failed', 'pending_agent') DEFAULT 'completed'");
    }
};

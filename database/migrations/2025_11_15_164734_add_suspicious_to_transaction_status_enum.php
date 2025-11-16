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
        Schema::table('transactions', function (Blueprint $table) {
             DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('completed','failed','pending_agent','in_progress','suspicious') NOT NULL DEFAULT 'completed'");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('completed','failed','pending_agent','in_progress') NOT NULL DEFAULT 'completed'");

        });
    }
};

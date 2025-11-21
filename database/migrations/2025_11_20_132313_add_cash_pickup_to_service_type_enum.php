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
        // For MySQL, you need to modify the ENUM type
        DB::statement("ALTER TABLE transactions MODIFY COLUMN service_type ENUM('wallet_to_wallet', 'transfer_via_agent', 'cash_pickup', 'cash_in', 'cash_out') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original ENUM values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN service_type ENUM('wallet_to_wallet', 'transfer_via_agent', 'cash_in', 'cash_out') NULL");
    }
};
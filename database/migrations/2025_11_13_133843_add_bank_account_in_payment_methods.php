<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `payment_methods` MODIFY `type` ENUM('credit_card','paypal','bank_account') NOT NULL DEFAULT 'credit_card'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `payment_methods` MODIFY `type` ENUM('credit_card','paypal') NOT NULL DEFAULT 'credit_card'");
    }
};

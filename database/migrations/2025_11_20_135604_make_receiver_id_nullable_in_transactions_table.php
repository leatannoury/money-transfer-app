<?php

// database/migrations/XXXXXXXX_XX_XX_make_receiver_id_nullable_in_transactions_table.php

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
        Schema::table('transactions', function (Blueprint $table) {
            // Change the receiver_id column to allow NULL values
            $table->foreignId('receiver_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert to NOT NULL if you ever need to roll back
            // NOTE: This will fail if there are existing NULLs in the column.
            $table->foreignId('receiver_id')->change(); // This assumes it was NOT NULL and UNSIGNED previously
        });
    }
};
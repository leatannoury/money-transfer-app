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
        Schema::table('transfer_services', function (Blueprint $table) {
            // Change the column definition to allow NULL values
            $table->string('source_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_services', function (Blueprint $table) {
            // Revert the column definition to NOT NULL if needed (check your original migration)
            // You might need to specify the default value if you want to drop the NOT NULL constraint
            $table->string('source_type')->nullable(false)->change();
        });
    }
};
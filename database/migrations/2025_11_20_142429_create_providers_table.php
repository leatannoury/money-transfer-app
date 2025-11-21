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
Schema::create('providers', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('country_code', 3);
    $table->string('service_type')->default('cash_pickup'); // e.g., 'cash_pickup' or 'bank_transfer'
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};

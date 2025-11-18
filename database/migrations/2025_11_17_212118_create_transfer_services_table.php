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
Schema::create('transfer_services', function (Blueprint $table) {
    $table->id();
    $table->string('name');                        // "Wallet → Wallet"
    $table->string('source_type');                 // wallet, bank, card
    $table->string('destination_type');            // wallet, bank, cash
    $table->string('destination_country');

    $table->string('speed');                       // instant, hours, days
    $table->decimal('fee', 8, 2);                  // service fee
    $table->decimal('exchange_rate', 10, 2);       // USD → foreign currency rate

    $table->boolean('promotion_active')->default(false);
    $table->string('promotion_text')->nullable();  // "First transfer free!"

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_services');
    }
};

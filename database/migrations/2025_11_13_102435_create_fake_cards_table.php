<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fake_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number')->unique();   // digits-only PAN for dev
            $table->string('provider')->nullable();
            $table->string('cardholder_name')->nullable();
            $table->string('expiry')->nullable();      // MM/YY
            $table->string('cvv')->nullable();         // dev-only (do NOT use in prod)
            $table->decimal('balance', 12, 2)->default(1000.00); // issuer balance for simulation
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fake_cards');
    }
};

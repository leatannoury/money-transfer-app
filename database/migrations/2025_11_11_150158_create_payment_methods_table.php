<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nickname')->nullable();
            $table->enum('type', ['credit_card','paypal'])->default('credit_card');
            $table->string('provider')->nullable();     // Visa, MasterCard...
            $table->string('card_mask')->nullable();    // **** **** **** 1234
            $table->string('last4')->nullable();
            $table->string('cardholder_name')->nullable();
            $table->string('expiry')->nullable();       // MM/YY
            $table->json('details')->nullable();        // optional extra data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

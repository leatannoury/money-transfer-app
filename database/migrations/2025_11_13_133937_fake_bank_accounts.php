<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fake_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number')->unique();
            $table->string('routing')->nullable();
            $table->string('account_holder');
            $table->string('account_type')->nullable(); // checking/savings
            $table->decimal('balance', 12, 2)->default(1000.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fake_bank_accounts');
    }
};

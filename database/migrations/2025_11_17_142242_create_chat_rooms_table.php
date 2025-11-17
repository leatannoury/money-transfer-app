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
        Schema::create('chat_rooms', function (Blueprint $table) {
           $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // user who starts the chat
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // admin assigned
            $table->enum('status', ['open', 'closed'])->default('open'); // chat status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};

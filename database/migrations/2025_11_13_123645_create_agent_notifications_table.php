<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');      // user id of the agent
            $table->unsignedBigInteger('transaction_id'); // related transaction
            $table->string('title');
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_notifications');
    }
};

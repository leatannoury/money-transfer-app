<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('transfer_services', function (Blueprint $table) {
        $table->string('destination_currency')->default('LBP');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_services', function (Blueprint $table) {
            //
        });
    }
};


<?php

// database/migrations/XXXXXXXX_XX_XX_add_recipient_details_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('recipient_name', 255)->nullable()->after('receiver_id');
            $table->string('recipient_phone', 50)->nullable()->after('recipient_name');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['recipient_phone', 'recipient_name']);
        });
    }
};
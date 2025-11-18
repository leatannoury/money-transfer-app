<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'amount_usd')) {
                $table->decimal('amount_usd', 12, 2)->nullable()->after('amount');
            }

            if (!Schema::hasColumn('transactions', 'fee_percent')) {
                $table->decimal('fee_percent', 5, 2)->nullable()->after('amount_usd');
            }

            if (!Schema::hasColumn('transactions', 'fee_amount_usd')) {
                $table->decimal('fee_amount_usd', 12, 2)->default(0)->after('fee_percent');
            }
        });

        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'completed',
                'failed',
                'pending_agent',
                'in_progress',
                'suspicious',
                'disputed',
                'refunded'
            ) NOT NULL DEFAULT 'completed'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'fee_amount_usd')) {
                $table->dropColumn('fee_amount_usd');
            }
            if (Schema::hasColumn('transactions', 'fee_percent')) {
                $table->dropColumn('fee_percent');
            }
            if (Schema::hasColumn('transactions', 'amount_usd')) {
                $table->dropColumn('amount_usd');
            }
        });

        DB::statement("
            ALTER TABLE transactions
            MODIFY COLUMN status ENUM(
                'completed',
                'failed',
                'pending_agent',
                'in_progress',
                'suspicious'
            ) NOT NULL DEFAULT 'completed'
        ");
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // track whether agent manually set themselves available (optional)
            $table->boolean('is_available')->default(true);

            // working hours stored as time (HH:MM:SS)
            // default business hours 08:00 -> 17:00
            $table->time('work_start_time')->nullable()->after('is_available')->default('08:00:00');
            $table->time('work_end_time')->nullable()->after('work_start_time')->default('17:00:00');

            // optional timezone if you support multiple timezones (nullable)
            $table->string('timezone')->nullable()->after('work_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_available', 'work_start_time', 'work_end_time', 'timezone']);
        });
    }
};

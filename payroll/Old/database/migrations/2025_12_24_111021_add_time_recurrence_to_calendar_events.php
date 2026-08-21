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
        Schema::table('company_calendar_events', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('end_time')->nullable()->after('end_date');
            $table->enum('recurrence_type', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none')->after('event_class');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_calendar_events', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'recurrence_type', 'recurrence_end_date']);
        });
    }
};

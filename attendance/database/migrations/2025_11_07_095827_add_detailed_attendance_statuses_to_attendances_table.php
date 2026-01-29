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
        Schema::table('attendances', function (Blueprint $table) {
            // Add detailed attendance status fields
            $table->boolean('is_late_arrival')->default(false)->after('status');
            $table->boolean('is_early_arrival')->default(false)->after('is_late_arrival');
            $table->boolean('is_late_departure')->default(false)->after('is_early_arrival');
            $table->boolean('is_early_departure')->default(false)->after('is_late_departure');
            $table->boolean('is_overtime')->default(false)->after('is_early_departure');
            $table->integer('late_arrival_minutes')->default(0)->after('is_overtime');
            $table->integer('early_departure_minutes')->default(0)->after('late_arrival_minutes');
            $table->decimal('overtime_hours', 5, 2)->default(0)->after('early_departure_minutes');
            $table->time('scheduled_start_time')->nullable()->after('overtime_hours');
            $table->time('scheduled_end_time')->nullable()->after('scheduled_start_time');
            $table->string('attendance_category')->default('regular')->after('scheduled_end_time'); // regular, weekend, holiday
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'is_late_arrival',
                'is_early_arrival',
                'is_late_departure',
                'is_early_departure',
                'is_overtime',
                'late_arrival_minutes',
                'early_departure_minutes',
                'overtime_hours',
                'scheduled_start_time',
                'scheduled_end_time',
                'attendance_category'
            ]);
        });
    }
};

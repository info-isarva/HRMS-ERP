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
        Schema::table('attendance_records', function (Blueprint $table) {
            // Link to biometric attendance record
            $table->unsignedBigInteger('attendance_id')->nullable()->after('public_holiday_id');
            
            // Biometric attendance data
            $table->time('check_in_time')->nullable()->after('attendance_id');
            $table->time('check_out_time')->nullable()->after('check_in_time');
            $table->decimal('total_hours', 5, 2)->nullable()->after('check_out_time');
            $table->decimal('late_arrival_minutes', 5, 2)->default(0)->after('total_hours');
            $table->decimal('early_departure_minutes', 5, 2)->default(0)->after('late_arrival_minutes');
            $table->decimal('overtime_hours', 5, 2)->default(0)->after('early_departure_minutes');
            
            // Flags for special cases
            $table->boolean('worked_on_holiday')->default(false)->after('overtime_hours');
            $table->boolean('worked_on_weekend')->default(false)->after('worked_on_holiday');
            $table->boolean('worked_on_leave')->default(false)->after('worked_on_weekend');
            $table->boolean('has_biometric_data')->default(false)->after('worked_on_leave');
            
            // Source tracking
            $table->enum('data_source', ['manual', 'biometric', 'hybrid'])->default('manual')->after('has_biometric_data');
            
            // Foreign key
            $table->foreign('attendance_id')->references('id')->on('attendances')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropColumn([
                'attendance_id',
                'check_in_time',
                'check_out_time',
                'total_hours',
                'late_arrival_minutes',
                'early_departure_minutes',
                'overtime_hours',
                'worked_on_holiday',
                'worked_on_weekend',
                'worked_on_leave',
                'has_biometric_data',
                'data_source'
            ]);
        });
    }
};

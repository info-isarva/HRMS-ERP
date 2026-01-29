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
            // Shift information
            $table->unsignedBigInteger('shift_id')->nullable()->after('data_source');
            $table->time('scheduled_start_time')->nullable()->after('shift_id');
            $table->time('scheduled_end_time')->nullable()->after('scheduled_start_time');
            $table->decimal('undertime_hours', 5, 2)->default(0)->after('overtime_hours');
            
            // Foreign key
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'shift_id',
                'scheduled_start_time',
                'scheduled_end_time',
                'undertime_hours'
            ]);
        });
    }
};

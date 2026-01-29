<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Drop the old unique constraint on (user_id, date) if exists
            DB::statement('ALTER TABLE attendance_records DROP INDEX IF EXISTS attendance_records_user_id_date_unique');
            
            // Add new unique constraint on (payroll_id, date)
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS attendance_records_payroll_id_date_unique ON attendance_records (payroll_id, date)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['payroll_id', 'date']);
            
            // Restore the old unique constraint on (user_id, date)
            $table->unique(['user_id', 'date'], 'attendance_records_user_id_date_unique');
        });
    }
};
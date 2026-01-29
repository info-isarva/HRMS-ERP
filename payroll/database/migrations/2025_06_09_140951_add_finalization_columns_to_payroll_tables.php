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
        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable();

            // Manually add foreign key with a short custom name
            $table->foreign('finalized_by', 'fk_epapms_finalized_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('employee_payroll_attendances', function (Blueprint $table) {
            $table->boolean('is_finalized')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_tables', function (Blueprint $table) {
            //
        });
    }
};

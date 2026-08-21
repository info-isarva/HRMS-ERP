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
        if (! Schema::hasTable('employee_payroll_attendance_payout_month_statuses')) {
            return;
        }

        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_payroll_attendance_payout_month_statuses', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('payout_year');
            }
            $table->foreign('location_id', 'epapms_location_fk')->references('id')->on('locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employee_payroll_attendance_payout_month_statuses')) {
            return;
        }

        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payroll_attendance_payout_month_statuses', 'location_id')) {
                $table->dropForeign('epapms_location_fk');
                $table->dropColumn('location_id');
            }
        });
    }
};

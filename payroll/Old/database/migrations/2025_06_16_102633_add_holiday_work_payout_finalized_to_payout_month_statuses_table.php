<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            $table->boolean('holiday_work_payout_finalized')->default(false)->after('incentive_finalized');
        });
    }
    
    public function down()
    {
        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            $table->dropColumn(['holiday_work_payout_finalized']);
        });
    }
};

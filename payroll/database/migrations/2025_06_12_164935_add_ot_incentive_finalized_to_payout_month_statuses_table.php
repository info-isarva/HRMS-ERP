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
            $table->boolean('ot_finalized')->default(false)->after('status');
            $table->boolean('incentive_finalized')->default(false)->after('ot_finalized');
        });
    }
    
    public function down()
    {
        Schema::table('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            $table->dropColumn(['ot_finalized', 'incentive_finalized']);
        });
    }
};

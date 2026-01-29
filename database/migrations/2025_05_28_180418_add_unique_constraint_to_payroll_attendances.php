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
        Schema::table('employee_payroll_attendances', function (Blueprint $table) {
            // Add unique constraint
            $table->unique(['emp_id', 'payout_month_id'], 'unique_employee_payout');
        });
    }

    public function down()
    {
        Schema::table('employee_payroll_attendances', function (Blueprint $table) {
            $table->dropUnique('unique_employee_payout');
        });
    }
};

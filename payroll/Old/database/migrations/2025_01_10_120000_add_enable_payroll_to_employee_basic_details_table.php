<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnablePayrollToEmployeeBasicDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->boolean('enable_payroll')->default(false)->after('enable_self_portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->dropColumn('enable_payroll');
        });
    }
}
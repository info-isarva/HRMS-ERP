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
        Schema::table('employee_payroll_attendances', function (Blueprint $table) {
            $table->json('earnings')->nullable()->after('gross_pay');
            $table->json('deductions')->nullable()->after('gross_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll_attendances', function (Blueprint $table) {
            //
        });
    }
};

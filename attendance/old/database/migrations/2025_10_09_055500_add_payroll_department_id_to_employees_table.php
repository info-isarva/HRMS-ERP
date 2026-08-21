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
        Schema::table('employees', function (Blueprint $table) {
            // Add payroll_department_id column to store API department ID
            if (!Schema::hasColumn('employees', 'payroll_department_id')) {
                $table->string('payroll_department_id')->nullable()->after('department_id');
                $table->index('payroll_department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['payroll_department_id']);
            $table->dropColumn('payroll_department_id');
        });
    }
};
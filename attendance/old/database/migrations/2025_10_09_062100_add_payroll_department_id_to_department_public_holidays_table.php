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
        Schema::table('department_public_holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('department_public_holidays', 'payroll_department_id')) {
                $table->integer('payroll_department_id')->nullable()->after('department_id');
                $table->index(['payroll_department_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('department_public_holidays', function (Blueprint $table) {
            $table->dropIndex(['payroll_department_id']);
            $table->dropColumn('payroll_department_id');
        });
    }
};
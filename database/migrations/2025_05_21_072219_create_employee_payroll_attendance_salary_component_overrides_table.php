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
        Schema::create('employee_payroll_attendance_salary_component_overrides', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('emp_id');
            $table->bigInteger('payroll_attendance_id');
            $table->bigInteger('salary_component_id');
            $table->double('default_value', 15, 2);
            $table->double('override_value', 15, 2);
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_attendance_salary_component_overrides');
    }
};

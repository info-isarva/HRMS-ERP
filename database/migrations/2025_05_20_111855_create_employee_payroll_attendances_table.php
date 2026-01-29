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
        Schema::create('employee_payroll_attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('emp_id');
            $table->string('payout_month_id');
            $table->double('total_working_days', 6, 2)->nullable();
            $table->double('employee_worked_days', 6, 2)->nullable();
            $table->double('gross_pay', 15, 2)->nullable();
            $table->double('total_deduction', 15, 2)->nullable();
            $table->double('total_payable', 15, 2)->nullable();
            $table->bigInteger('manual_override')->nullable();
            $table->timestamps();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_attendances');
    }
};

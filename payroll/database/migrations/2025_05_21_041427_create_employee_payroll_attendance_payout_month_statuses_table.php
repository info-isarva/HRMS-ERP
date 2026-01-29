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
        Schema::create('employee_payroll_attendance_payout_month_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('payout_month');
            $table->string('payout_year');
            $table->enum('status', ['pending', 'progress', 'completed'])->default('pending');
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
        Schema::dropIfExists('employee_payroll_attendance_payout_month_statuses');
    }
};

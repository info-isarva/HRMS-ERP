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
        Schema::create('employee_exit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emp_id')->constrained('employee_basic_details')->onDelete('cascade');
            $table->enum('exit_type', ['Resignation', 'Termination', 'Absconding', 'Retirement', 'Other']);
            $table->date('resignation_date');
            $table->date('last_working_day');
            $table->text('reason');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Completed'])->default('Pending');
            $table->integer('notice_period_days')->nullable();
            $table->boolean('exit_interview_conducted')->default(0);
            $table->text('exit_interview_notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_exit_details');
    }
};

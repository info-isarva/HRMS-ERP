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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // From payroll system
            $table->integer('payroll_id')->unsigned()->nullable(); // Payroll primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->string('designation')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('financial_year')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_resignation')->nullable();
            $table->integer('reporting_manager_payroll_id')->unsigned()->nullable(); // Manager's payroll ID
            $table->json('additional_data')->nullable(); // For any extra payroll data
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['employee_id', 'payroll_id']);
            $table->index(['department_id']);
            $table->index(['status']);
        });
        
        // Add foreign key constraint separately if departments table exists
        if (Schema::hasTable('departments')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

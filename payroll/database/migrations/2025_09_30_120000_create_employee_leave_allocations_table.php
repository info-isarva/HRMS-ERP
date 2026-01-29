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
        Schema::create('employee_leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_id');
            $table->unsignedInteger('attendance_leave_type_id'); // ID from attendance system
            $table->string('leave_type_name');
            $table->string('leave_type_code');
            $table->decimal('allocated_days', 8, 2); // Original allocation from attendance system
            $table->decimal('override_days', 8, 2)->nullable(); // Manual override value
            $table->decimal('effective_days', 8, 2); // Final calculated days (allocated or override)
            $table->boolean('is_pro_rated')->default(false); // If calculated for mid-year joining
            $table->decimal('pro_rated_factor', 5, 4)->nullable(); // Factor used for pro-rating (e.g., 0.75 for 9/12 months)
            $table->string('financial_year'); // Financial year string like "2025-2026"
            $table->boolean('is_manual_override')->default(false);
            $table->json('department_assignment')->nullable(); // Store department assignment info from API
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('emp_id')->references('id')->on('employee_basic_details')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['emp_id', 'financial_year']);
            $table->index('attendance_leave_type_id');
            $table->index('financial_year');
            
            // Unique constraint to prevent duplicate allocations
            $table->unique(['emp_id', 'attendance_leave_type_id', 'financial_year'], 'unique_emp_leave_allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_allocations');
    }
};
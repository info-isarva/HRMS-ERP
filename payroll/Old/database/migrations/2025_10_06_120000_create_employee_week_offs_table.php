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
        Schema::create('employee_week_offs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->json('week_off_days'); // JSON array of day numbers (0=Sunday, 1=Monday, etc.)
            $table->string('week_off_pattern')->nullable(); // Human-readable pattern like "Sunday, Saturday"
            $table->integer('working_days_per_week')->default(6); // Calculated working days
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('employee_id')->references('id')->on('employee_basic_details')->onDelete('cascade');
            
            // Unique constraint to ensure one record per employee
            $table->unique('employee_id');
            
            // Index for performance
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_week_offs');
    }
};
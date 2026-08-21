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
    Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->string('employee_payroll_id');
            $table->integer('month'); // 1-12
            $table->integer('year'); // e.g., 2025
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_payroll_id', 'month', 'year']); // One record per employee per month
            $table->index(['month', 'year', 'is_locked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('overtimes');
    }
};

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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('employee_payroll_id');
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_departure', 'half_day', 'overtime'])->default('present');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->json('raw_data')->nullable(); // Store original biometric data
            $table->timestamp('processed_at')->nullable();
            $table->string('source')->default('biometric_excel'); // Source of the data
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');

            $table->index(['employee_payroll_id', 'date']); // Index for performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

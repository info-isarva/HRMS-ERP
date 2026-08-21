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
        Schema::create('manual_punches', function (Blueprint $table) {
            $table->id();
            $table->string('employee_payroll_id')->index();
            $table->string('employee_id')->nullable();
            $table->string('employee_email')->nullable();
            $table->date('date')->index();
            $table->time('punch_in_time')->nullable();
            $table->time('punch_out_time')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['employee_payroll_id', 'date']);
            $table->index('status');
            
            // Foreign keys
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_punches');
    }
};

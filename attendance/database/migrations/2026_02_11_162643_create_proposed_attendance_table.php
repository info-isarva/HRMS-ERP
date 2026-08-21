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
        Schema::create('proposed_attendance', function (Blueprint $table) {
            $table->id();
            $table->string('employee_payroll_id');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->string('status'); // present, absent, compoff, late, etc.
            $table->string('source_status')->nullable(); // status before override
            $table->boolean('is_overridden')->default(false);
            $table->string('overridden_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('month_year'); // YYYY-MM for easier filtering
            $table->timestamps();
            
            $table->unique(['employee_payroll_id', 'date'], 'emp_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposed_attendance');
    }
};

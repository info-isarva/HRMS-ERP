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
        Schema::create('duty_rosters', function (Blueprint $table) {
            $table->id();
            $table->string('employee_payroll_id');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->date('date');
            $table->timestamps();

            $table->unique(['employee_payroll_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duty_rosters');
    }
};

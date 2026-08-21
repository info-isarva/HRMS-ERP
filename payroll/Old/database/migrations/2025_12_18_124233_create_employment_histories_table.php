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
        Schema::create('employment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emp_id')->constrained('employee_basic_details')->onDelete('cascade');
            $table->date('previous_joining_date');
            $table->date('previous_exit_date');
            $table->enum('exit_type', ['Resignation', 'Termination', 'Absconding', 'Retirement', 'Other'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_histories');
    }
};

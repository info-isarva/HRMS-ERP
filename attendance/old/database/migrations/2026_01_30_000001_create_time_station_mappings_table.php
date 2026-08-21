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
        Schema::create('time_station_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('ts_user_id')->unique(); // ID from TimeStation
            $table->string('ts_name')->nullable();
            $table->string('ts_department')->nullable();
            
            // Map to HRMS Employee
            $table->integer('employee_payroll_id')->unsigned()->nullable();
            
            $table->boolean('is_ignored')->default(false);
            $table->timestamps();

            $table->index('employee_payroll_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_station_mappings');
    }
};

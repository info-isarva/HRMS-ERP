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
        Schema::create('time_station_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ts_activity_id')->unique(); // Unique event ID from TimeStation
            $table->string('ts_user_id');
            
            // Mapped ID (filled if mapping exists)
            $table->integer('employee_payroll_id')->unsigned()->nullable();
            
            $table->dateTime('timestamp');
            $table->string('activity_type'); // CheckIn / CheckOut
            $table->string('device_id')->nullable();
            $table->string('gps_location')->nullable();
            
            $table->json('raw_response')->nullable(); // Store full payload for debugging
            
            $table->string('sync_status')->default('pending'); // pending, processed, ignored
            $table->string('sync_error')->nullable();
            
            $table->timestamps();

            $table->index(['ts_user_id', 'timestamp']);
            $table->index('employee_payroll_id');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_station_logs');
    }
};

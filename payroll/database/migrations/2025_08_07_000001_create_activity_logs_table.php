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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 50)->nullable();
            $table->string('user_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('role_name')->nullable();
            $table->string('activity_type', 100); // CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.
            $table->string('module', 100); // USER_MANAGEMENT, EMPLOYEE, PAYROLL, etc.
            $table->text('description'); // Detailed description of the activity
            $table->json('old_data')->nullable(); // Previous data before change
            $table->json('new_data')->nullable(); // New data after change
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('activity_timestamp');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'activity_timestamp']);
            $table->index(['activity_type', 'activity_timestamp']);
            $table->index(['module', 'activity_timestamp']);
            $table->index('activity_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

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
        Schema::create('cached_leave_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('attendance_leave_type_id')->unique();
            $table->string('leave_type_name');
            $table->string('leave_type_code');
            $table->integer('days_allowed');
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('financial_year');
            $table->json('assigned_departments'); // Store array of department objects from API
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index('attendance_leave_type_id');
            $table->index('financial_year');
            $table->index('last_synced_at');
            $table->index(['financial_year', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cached_leave_types');
    }
};
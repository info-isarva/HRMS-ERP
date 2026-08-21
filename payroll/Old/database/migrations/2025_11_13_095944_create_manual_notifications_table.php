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
        Schema::create('manual_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed', 'cancelled'])->default('draft');
            
            // Targeting
            $table->enum('target_type', ['all', 'department', 'specific_employees'])->default('all');
            $table->json('target_departments')->nullable(); // Array of department IDs/names
            $table->json('target_employees')->nullable(); // Array of employee IDs
            
            // Scheduling
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->enum('recurrence_type', ['once', 'daily', 'weekly', 'monthly', 'date_range'])->default('once');
            $table->integer('recurrence_interval')->default(1); // Every X days/weeks/months
            $table->json('recurrence_days')->nullable(); // For weekly: ['monday', 'friday']
            $table->date('recurrence_end_date')->nullable();
            
            // Display settings
            $table->boolean('show_in_header')->default(true);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->string('icon')->default('fa-info-circle');
            $table->string('color')->default('primary');
            
            // Metadata
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'start_date', 'end_date']);
            $table->index(['target_type', 'priority']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_notifications');
    }
};

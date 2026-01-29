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
        Schema::create('leave_application_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_application_id')->constrained()->onDelete('cascade');
            $table->date('leave_date');
            $table->enum('day_type', ['full_day', 'first_half', 'second_half'])->default('full_day');
            $table->decimal('days_count', 3, 1)->default(1.0); // 1.0 for full day, 0.5 for half day
            $table->boolean('is_public_holiday')->default(false);
            $table->boolean('exclude_from_calculation')->default(false); // For public holidays
            $table->string('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['leave_application_id', 'leave_date']);
            $table->index(['leave_application_id', 'leave_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_application_days');
    }
};
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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('status'); // 'present', 'absent', 'leave', 'public_holiday', etc.
            $table->foreignId('leave_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leave_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('public_holiday_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_override')->default(false);
            $table->string('original_status')->nullable();
            $table->unsignedBigInteger('original_leave_type_id')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamps();

            // Indexes for faster querying
            $table->index(['user_id', 'date']);
            $table->index(['month', 'year']);
            $table->index('is_locked');

            // Foreign keys
            $table->foreign('modified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('original_leave_type_id')->references('id')->on('leave_types')->nullOnDelete();
            
            // Unique constraint to prevent duplicate entries for the same user and date
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};

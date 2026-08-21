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
        Schema::table('leave_applications', function (Blueprint $table) {
            // Replace simple status with detailed status fields
            $table->dropColumn('status');
            
            // Add new status columns
            $table->enum('status', ['pending', 'approved_by_manager', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('manager_approved_by')->nullable();
            $table->timestamp('manager_approved_at')->nullable();
            $table->unsignedBigInteger('hr_approved_by')->nullable();
            $table->timestamp('hr_approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Foreign keys
            $table->foreign('manager_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('hr_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['manager_approved_by']);
            $table->dropForeign(['hr_approved_by']);
            $table->dropForeign(['rejected_by']);
            
            $table->dropColumn([
                'manager_approved_by',
                'manager_approved_at',
                'hr_approved_by',
                'hr_approved_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason'
            ]);
            
            // Restore original status column
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        });
    }
};

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
            // Update status enum to include forwarded_to_manager
            $table->dropColumn('status');
            $table->enum('status', [
                'pending', 
                'forwarded_to_manager', 
                'approved_by_manager', 
                'approved', 
                'rejected',
                'cancelled'
            ])->default('pending');
            
            // Add forwarding fields
            $table->unsignedBigInteger('forwarded_by')->nullable();
            $table->timestamp('forwarded_at')->nullable();
            $table->text('forwarding_note')->nullable();
            
            // Foreign key for forwarded_by
            $table->foreign('forwarded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['forwarded_by']);
            
            $table->dropColumn([
                'forwarded_by',
                'forwarded_at', 
                'forwarding_note'
            ]);
            
            // Restore original status enum
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'approved_by_manager', 'approved', 'rejected'])->default('pending');
        });
    }
};
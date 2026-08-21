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
        Schema::create('leave_type_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('sync_type', ['manual', 'scheduled', 'on_demand'])->default('manual');
            $table->string('financial_year');
            $table->integer('total_synced')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('errors')->nullable();
            $table->json('sync_details')->nullable(); // Store additional sync information
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('started_at');
            $table->index('financial_year');
            $table->index(['status', 'financial_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_type_sync_logs');
    }
};
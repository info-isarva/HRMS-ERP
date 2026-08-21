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
        Schema::create('attendance_batches', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->string('status')->default('processing'); // 'processing', 'completed', 'failed'
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            // Indexes for faster querying
            $table->index(['month', 'year']);
            $table->index('status');
        });

        // Add batch_id to attendance_records table
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->constrained('attendance_batches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
        
        Schema::dropIfExists('attendance_batches');
    }
};

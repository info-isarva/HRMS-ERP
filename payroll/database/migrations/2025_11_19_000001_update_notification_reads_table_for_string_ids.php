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
        // Drop the existing table if it exists
        Schema::dropIfExists('notification_reads');
        
        // Recreate with string notification_id
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id', 255);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate reads
            $table->unique(['notification_id', 'user_id'], 'notification_user_unique');
            
            // Indexes
            $table->index('notification_id');
            $table->index('user_id');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop and recreate with bigInteger
        Schema::dropIfExists('notification_reads');
        
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate reads
            $table->unique(['notification_id', 'user_id']);
            
            // Indexes
            $table->index('notification_id');
            $table->index('user_id');
            $table->index('read_at');
        });
    }
};

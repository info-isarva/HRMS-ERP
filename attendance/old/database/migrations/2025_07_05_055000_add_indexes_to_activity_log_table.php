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
        Schema::table('activity_log', function (Blueprint $table) {
            // The following indexes may already exist, so we skip adding them to avoid duplicate key errors.
            // $table->index(['created_at']);
            // $table->index(['causer_id']);
            // $table->index(['log_name']);
            // $table->index(['event']);
            // $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // The following dropIndex calls are commented out to avoid errors if indexes do not exist.
            // $table->dropIndex(['created_at']);
            // $table->dropIndex(['causer_id']);
            // $table->dropIndex(['log_name']);
            // $table->dropIndex(['event']);
            // $table->dropIndex(['subject_type', 'subject_id']);
        });
    }
};

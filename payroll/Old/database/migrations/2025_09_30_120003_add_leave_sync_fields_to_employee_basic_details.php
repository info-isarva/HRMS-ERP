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
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->timestamp('leave_allocations_synced_at')->nullable()->after('updated_at');
            $table->string('leave_sync_financial_year')->nullable()->after('leave_allocations_synced_at');
            
            // Index for leave sync tracking
            $table->index('leave_sync_financial_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->dropIndex(['leave_sync_financial_year']);
            $table->dropColumn(['leave_allocations_synced_at', 'leave_sync_financial_year']);
        });
    }
};
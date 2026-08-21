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
            // Check if column doesn't exist before adding
            if (! Schema::hasColumn('employee_basic_details', 'reporting_manager_id')) {
                $table->unsignedBigInteger('reporting_manager_id')->nullable()->after('employee_id');
                $table->foreign('reporting_manager_id')->references('id')->on('employee_basic_details')->onDelete('set null');
                $table->index('reporting_manager_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employee_basic_details') || ! Schema::hasColumn('employee_basic_details', 'reporting_manager_id')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropIndex(['reporting_manager_id']);
            $table->dropColumn('reporting_manager_id');
        });
    }
};

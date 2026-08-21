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
            if (! Schema::hasColumn('employee_basic_details', 'annual_ctc')) {
                $table->decimal('annual_ctc', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('employee_basic_details', 'monthly_ctc')) {
                $table->decimal('monthly_ctc', 10, 2)->nullable();
            }
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
            $table->dropColumn(['annual_ctc', 'monthly_ctc']);
        });
    }
};

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
            if (! Schema::hasColumn('employee_basic_details', 'pf_calculation_type')) {
                $table->enum('pf_calculation_type', ['restrict_15k', 'actual_12', 'manual'])->default('actual_12');
            }
            if (! Schema::hasColumn('employee_basic_details', 'pf_include_employer_share')) {
                $table->boolean('pf_include_employer_share')->default(true); // true = Full 24% logic, false = 12% logic
            }
            if (! Schema::hasColumn('employee_basic_details', 'pf_manual_amount')) {
                $table->decimal('pf_manual_amount', 10, 2)->nullable();
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
            $table->dropColumn(['pf_calculation_type', 'pf_include_employer_share', 'pf_manual_amount']);
        });
    }
};

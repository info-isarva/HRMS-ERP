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
        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->enum('pf_calculation_type', ['restrict_15k', 'actual_12', 'manual'])->default('actual_12')->after('monthly_ctc');
            $table->boolean('pf_include_employer_share')->default(true)->after('pf_calculation_type'); // true = Full 24% logic, false = 12% logic
            $table->decimal('pf_manual_amount', 10, 2)->nullable()->after('pf_include_employer_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->dropColumn(['pf_calculation_type', 'pf_include_employer_share', 'pf_manual_amount']);
        });
    }
};

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
        Schema::table('employee_statutory_components', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_statutory_components', 'tds_regime')) { 
                $table->string('tds_regime')->nullable()->after('full_amount_deduct_from_ctc');
            }
            if (!Schema::hasColumn('employee_statutory_components', 'tds_option')) {
                $table->string('tds_option')->nullable()->default('auto')->after('tds_regime');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_statutory_components', function (Blueprint $table) {
            $table->dropColumn(['tds_regime', 'tds_option']);
        });
    }
};

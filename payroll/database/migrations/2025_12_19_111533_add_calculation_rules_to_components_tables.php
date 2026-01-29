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
        Schema::table('salary_components', function (Blueprint $table) {
            $table->string('calculation_type')->default('calc_fixed_amount')->after('status'); 
             // Types: flat_amount, percentage_ctc, percentage_basic, residual
            $table->decimal('calculation_value', 10, 2)->default(0)->after('calculation_type');
            $table->boolean('is_residual')->default(false)->after('calculation_value');
        });

        Schema::table('statutory_components', function (Blueprint $table) {
            $table->string('calculation_type')->default('flat_amount')->after('status'); 
            // Types: flat_amount, percentage_gross, percentage_basic
            $table->decimal('calculation_value', 10, 2)->default(0)->after('calculation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'calculation_value', 'is_residual']);
        });

        Schema::table('statutory_components', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'calculation_value']);
        });
    }
};

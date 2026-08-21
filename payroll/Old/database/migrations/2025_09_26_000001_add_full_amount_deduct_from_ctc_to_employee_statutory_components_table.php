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
            $table->boolean('full_amount_deduct_from_ctc')->default(false)->after('epf_option')->comment('When true, deducts both employee and employer EPF portions (24% total) from employee CTC');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_statutory_components', function (Blueprint $table) {
            $table->dropColumn('full_amount_deduct_from_ctc');
        });
    }
};
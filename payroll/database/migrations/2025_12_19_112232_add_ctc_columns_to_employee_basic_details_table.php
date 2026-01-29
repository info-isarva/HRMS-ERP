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
            $table->decimal('annual_ctc', 10, 2)->nullable()->after('enable_payroll');
            $table->decimal('monthly_ctc', 10, 2)->nullable()->after('annual_ctc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->dropColumn(['annual_ctc', 'monthly_ctc']);
        });
    }
};

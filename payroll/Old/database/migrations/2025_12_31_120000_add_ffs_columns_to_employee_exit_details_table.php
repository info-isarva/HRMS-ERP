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
        Schema::table('employee_exit_details', function (Blueprint $table) {
            $table->enum('settlement_mode', ['immediate', 'payroll'])->nullable()->after('remarks');
            $table->date('settlement_date')->nullable()->after('settlement_mode');
            $table->decimal('settlement_amount', 10, 2)->nullable()->after('settlement_date');
            $table->decimal('pending_advance', 10, 2)->nullable()->after('settlement_amount');
            $table->text('settlement_notes')->nullable()->after('pending_advance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_exit_details', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_mode',
                'settlement_date',
                'settlement_amount',
                'pending_advance',
                'settlement_notes'
            ]);
        });
    }
};

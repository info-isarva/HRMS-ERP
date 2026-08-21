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
            // Leave Encashment
            $table->decimal('leave_encashment_days_calculated', 8, 2)->nullable()->after('settlement_notes');
            $table->decimal('leave_encashment_days_override', 8, 2)->nullable()->after('leave_encashment_days_calculated');
            $table->decimal('leave_encashment_amount_calculated', 12, 2)->nullable()->after('leave_encashment_days_override');
            $table->decimal('leave_encashment_amount_override', 12, 2)->nullable()->after('leave_encashment_amount_calculated');

            // Notice Pay
            $table->integer('notice_period_shortfall_days')->nullable()->after('leave_encashment_amount_override');
            $table->decimal('notice_pay_amount_calculated', 12, 2)->nullable()->after('notice_period_shortfall_days');
            $table->decimal('notice_pay_amount_override', 12, 2)->nullable()->after('notice_pay_amount_calculated');

            // Gratuity
            $table->decimal('gratuity_tenure_years_calculated', 8, 2)->nullable()->after('notice_pay_amount_override');
            $table->decimal('gratuity_tenure_years_override', 8, 2)->nullable()->after('gratuity_tenure_years_calculated');
            $table->decimal('gratuity_amount_calculated', 12, 2)->nullable()->after('gratuity_tenure_years_override');
            $table->decimal('gratuity_amount_override', 12, 2)->nullable()->after('gratuity_amount_calculated');

            // Bonus
            $table->decimal('bonus_amount_calculated', 12, 2)->nullable()->after('gratuity_amount_override');
            $table->decimal('bonus_amount_override', 12, 2)->nullable()->after('bonus_amount_calculated');

            // General / Others
            $table->decimal('other_earnings', 12, 2)->nullable()->after('bonus_amount_override');
            $table->decimal('other_deductions', 12, 2)->nullable()->after('other_earnings');
            
            // Salary Breakdown Snapshot
            $table->decimal('prorated_salary_amount', 12, 2)->nullable()->after('other_deductions');
            $table->decimal('prorated_statutory_credit', 12, 2)->nullable()->after('prorated_salary_amount'); // Earnings from statutory
            $table->decimal('prorated_statutory_debit', 12, 2)->nullable()->after('prorated_statutory_credit'); // Deductions from statutory
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_exit_details', function (Blueprint $table) {
            $table->dropColumn([
                'leave_encashment_days_calculated',
                'leave_encashment_days_override',
                'leave_encashment_amount_calculated',
                'leave_encashment_amount_override',
                'notice_period_shortfall_days',
                'notice_pay_amount_calculated',
                'notice_pay_amount_override',
                'gratuity_tenure_years_calculated',
                'gratuity_tenure_years_override',
                'gratuity_amount_calculated',
                'gratuity_amount_override',
                'bonus_amount_calculated',
                'bonus_amount_override',
                'other_earnings',
                'other_deductions',
                'prorated_salary_amount',
                'prorated_statutory_credit',
                'prorated_statutory_debit',
            ]);
        });
    }
};

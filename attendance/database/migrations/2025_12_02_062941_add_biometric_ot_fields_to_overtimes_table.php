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
        Schema::table('overtimes', function (Blueprint $table) {
            // Calculated OT hours from biometric attendance (auto-calculated)
            $table->decimal('calculated_ot_hours', 5, 2)->default(0)->after('overtime_hours');
            
            // Approved OT hours (can be manually overridden by HR)
            $table->decimal('approved_ot_hours', 5, 2)->nullable()->after('calculated_ot_hours');
            
            // Track if manually overridden
            $table->boolean('is_manually_overridden')->default(false)->after('approved_ot_hours');
            
            // Original calculated value before override (for audit trail)
            $table->decimal('original_calculated_hours', 5, 2)->nullable()->after('is_manually_overridden');
            
            // Who overrode and when
            $table->unsignedBigInteger('overridden_by')->nullable()->after('original_calculated_hours');
            $table->timestamp('overridden_at')->nullable()->after('overridden_by');
            
            // Approval status
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('overridden_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Notes/remarks for override or approval
            $table->text('remarks')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn([
                'calculated_ot_hours',
                'approved_ot_hours',
                'is_manually_overridden',
                'original_calculated_hours',
                'overridden_by',
                'overridden_at',
                'approval_status',
                'approved_by',
                'approved_at',
                'remarks'
            ]);
        });
    }
};

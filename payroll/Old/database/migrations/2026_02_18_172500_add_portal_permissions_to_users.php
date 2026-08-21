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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'enable_self_portal')) {
                $table->boolean('enable_self_portal')->default(0)->after('enable_crm');
            }
            if (!Schema::hasColumn('users', 'enable_payroll')) {
                $table->boolean('enable_payroll')->default(0)->after('enable_self_portal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'enable_self_portal')) {
                $table->dropColumn('enable_self_portal');
            }
            if (Schema::hasColumn('users', 'enable_payroll')) {
                $table->dropColumn('enable_payroll');
            }
        });
    }
};

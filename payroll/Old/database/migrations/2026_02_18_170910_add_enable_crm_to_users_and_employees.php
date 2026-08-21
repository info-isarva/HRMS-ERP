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
            if (!Schema::hasColumn('users', 'enable_crm')) {
                $table->boolean('enable_crm')->default(0)->after('status');
            }
        });

        if (Schema::hasTable('employee_basic_details')) {
            Schema::table('employee_basic_details', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_basic_details', 'enable_crm')) {
                    $table->boolean('enable_crm')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'enable_crm')) {
                $table->dropColumn('enable_crm');
            }
        });

        if (Schema::hasTable('employee_basic_details')) {
            Schema::table('employee_basic_details', function (Blueprint $table) {
                if (Schema::hasColumn('employee_basic_details', 'enable_crm')) {
                    $table->dropColumn('enable_crm');
                }
            });
        }
    }
};

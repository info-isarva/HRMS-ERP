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
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            $table->bigInteger('attendance_method')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employee_basic_details')) {
            return;
        }

        Schema::table('employee_basic_details', function (Blueprint $table) {
            //
        });
    }
};

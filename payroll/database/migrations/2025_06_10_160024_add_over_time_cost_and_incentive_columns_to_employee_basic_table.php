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
            $table->enum('ot_status', ['no', 'yes'])->default('no')->after('attendance_method');
            $table->double('ot_per_hour', 15, 2)->nullable()->after('ot_status');
            $table->enum('incentive_status', ['no', 'yes'])->default('no')->after('ot_per_hour');
            $table->double('incentive_per_month', 15, 2)->nullable()->after('incentive_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_basic_details', function (Blueprint $table) {
            //
        });
    }
};

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
        Schema::table('employee_personal_details', function (Blueprint $table) {
            $table->string('temporary_address')->nullable()->after('address');
            $table->string('emergency_contact_name')->nullable()->after('blood_group');
            $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_personal_details', function (Blueprint $table) {
            //
        });
    }
};

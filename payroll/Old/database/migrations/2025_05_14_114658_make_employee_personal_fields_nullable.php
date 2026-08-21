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
            $table->string('address')->nullable()->change();
            $table->string('father_name')->nullable()->change();
            $table->string('mother_name')->nullable()->change();
            $table->bigInteger('blood_group')->nullable()->change();
            $table->string('aadhaar_number')->nullable()->change();
            $table->string('pan_number')->nullable()->change();
            $table->string('pf_account_number')->nullable()->change();
            $table->string('esic_number')->nullable()->change();
            $table->string('uploaded_documents')->nullable()->change();
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

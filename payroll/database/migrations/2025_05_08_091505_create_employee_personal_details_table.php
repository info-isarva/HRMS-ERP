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
        Schema::create('employee_personal_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('emp_id');
            $table->string('address');
            $table->string('father_name');
            $table->string('mother_name');
            $table->bigInteger('blood_group');
            $table->string('aadhaar_number');
            $table->string('pan_number');
            $table->string('pf_account_number');
            $table->string('esic_number');
            $table->string('uploaded_documents');
            $table->timestamps();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_personal_details');
    }
};

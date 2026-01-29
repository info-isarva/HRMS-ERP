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
        Schema::create('employee_basic_details', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('contact_number');
            $table->date('date_of_birth');
            $table->bigInteger('gender');
            $table->bigInteger('marital_status');
            $table->string('designation');
            $table->string('department');
            $table->date('date_of_joining');
            $table->bigInteger('status');
            $table->bigInteger('role');
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
        Schema::dropIfExists('employee_basic_details');
    }
};

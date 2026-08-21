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
        Schema::create('posh_icc_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->string('icc_role'); // Presiding Officer, Internal Member, External Member, Member Secretary, etc.
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employee_basic_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posh_icc_members');
    }
};

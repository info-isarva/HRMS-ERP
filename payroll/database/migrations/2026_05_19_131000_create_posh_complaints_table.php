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
        Schema::create('posh_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->unsignedBigInteger('employee_id')->nullable(); // Complainant (nullable for anonymization on DB layer if preferred, but we will store it and mask in view)
            $table->string('complainant_name')->nullable();
            $table->string('complainant_email')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->date('incident_date');
            $table->string('incident_location');
            $table->string('respondent_name');
            $table->string('respondent_department')->nullable();
            $table->text('description');
            $table->string('status')->default('pending'); // pending, under_investigation, resolved, dismissed
            $table->text('resolution_summary')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employee_basic_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posh_complaints');
    }
};

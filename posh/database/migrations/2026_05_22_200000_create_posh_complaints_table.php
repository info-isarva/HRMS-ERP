<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posh_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('case_number', 32)->index();
            $table->foreignId('filed_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('complainant_name')->nullable();
            $table->string('complainant_email')->nullable();
            $table->string('employee_code', 64)->nullable();
            $table->string('department', 128)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('filed_by_relation', 32)->default('self');

            $table->string('respondent_name');
            $table->string('respondent_type', 32)->default('employee');
            $table->string('respondent_department', 128)->nullable();
            $table->boolean('vs_employer')->default(false);

            $table->date('incident_date');
            $table->string('incident_location')->nullable();
            $table->text('description');

            $table->string('routed_to', 8)->default('IC');
            $table->string('status', 64)->default('Submitted');
            $table->unsignedTinyInteger('operate_step')->default(0);
            $table->timestamp('inquiry_started_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->json('case_data')->nullable();

            $table->timestamps();

            $table->unique(['organization_id', 'case_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posh_complaints');
    }
};

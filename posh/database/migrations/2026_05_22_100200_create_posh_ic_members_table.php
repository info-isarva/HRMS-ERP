<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posh_ic_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('employee_code', 64)->nullable();
            $table->string('department', 128)->nullable();
            $table->string('designation', 128)->nullable();
            $table->string('ic_role'); // presiding_officer, internal_member, external_member, member_secretary
            $table->string('contact_number', 32)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_woman')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posh_ic_members');
    }
};

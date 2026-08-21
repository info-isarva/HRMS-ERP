<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('company_code', 32)->unique();
            $table->string('name');
            $table->string('workspace_domain')->unique();
            $table->string('payroll_domain')->nullable()->unique();
            $table->string('attendance_domain')->nullable()->unique();
            $table->string('crm_domain')->nullable()->unique();
            $table->string('workspace_database');
            $table->string('payroll_database')->nullable();
            $table->string('attendance_database')->nullable();
            $table->string('crm_database')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive, provisioning
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenants');
    }
};

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
        Schema::create('employee_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status_name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert default data
        DB::table('employee_statuses')->insert([
            ['status_name' => 'Active', 'short_name' => 'ACT', 'description' => 'Active employee', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'Probation Period', 'short_name' => 'PRB', 'description' => 'Employee in probation period', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'Left', 'short_name' => 'LFT', 'description' => 'Employee has left the company', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['status_name' => 'Onboard', 'short_name' => 'ONB', 'description' => 'Employee onboarding process', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_statuses');
    }
};

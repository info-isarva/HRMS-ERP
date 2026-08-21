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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert default data
        DB::table('roles')->insert([
            ['role_name' => 'Admin', 'short_name' => 'ADM', 'description' => 'Administrator with full access', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_name' => 'Employee', 'short_name' => 'EMP', 'description' => 'Regular employee user', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_name' => 'HR Manager', 'short_name' => 'HRM', 'description' => 'Human Resources Manager', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['role_name' => 'Finance Manager', 'short_name' => 'FNM', 'description' => 'Finance and Accounts Manager', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

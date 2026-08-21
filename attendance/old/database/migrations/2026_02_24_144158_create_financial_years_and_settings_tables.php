<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Financial Years table
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "2024-25"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
            
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
        });

        // 2. Create System Settings table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Seed initial settings
        DB::table('system_settings')->insert([
            [
                'key' => 'fy_start_month',
                'value' => '4', // Default to April
                'description' => 'Starting month of the financial year (1-12)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        
        // 4. Create initial financial years based on April start
        // Current Year (2025-26 as per system date Feb 2026)
        DB::table('financial_years')->insert([
            [
                'name' => '2024-25',
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'is_active' => false,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '2025-26',
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_active' => true,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
        Schema::dropIfExists('system_settings');
    }
};

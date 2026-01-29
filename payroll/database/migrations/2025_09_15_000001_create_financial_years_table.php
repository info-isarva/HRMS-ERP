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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "2024-25", "2025-26"
            $table->date('start_date'); // Financial year start date
            $table->date('end_date'); // Financial year end date
            $table->boolean('is_current')->default(false); // Current active financial year
            $table->boolean('is_closed')->default(false); // Whether FY is closed
            $table->timestamp('closed_at')->nullable(); // When it was closed
            $table->json('closing_summary')->nullable(); // Summary data at closing
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
            
            // Indexes
            $table->index(['is_current', 'is_closed']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};

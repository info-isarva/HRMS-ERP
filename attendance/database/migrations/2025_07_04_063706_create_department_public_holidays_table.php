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
        Schema::create('department_public_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('public_holiday_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['department_id', 'public_holiday_id'], 'dept_holiday_unique');
            $table->index(['department_id']);
            $table->index(['public_holiday_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_public_holidays');
    }
};

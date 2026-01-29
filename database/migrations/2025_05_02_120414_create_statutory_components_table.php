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
        Schema::create('statutory_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');         
            // Add enum for type: earning or deduction
            $table->enum('type', ['earning', 'deduction']);
            // Add status field (active/inactive)
            $table->boolean('status')->default(true); // true = active, false = inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_components');
    }
};

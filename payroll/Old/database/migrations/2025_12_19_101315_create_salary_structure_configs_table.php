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
        Schema::create('salary_structure_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_component_id')->constrained()->onDelete('cascade');
            $table->enum('calculation_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2); // The percentage amount or fixed amount
            $table->enum('percentage_of', ['ctc', 'basic'])->default('ctc')->comment('Base for calculation');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structure_configs');
    }
};

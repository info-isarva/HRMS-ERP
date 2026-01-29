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
        Schema::create('held_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('hold_type'); // 'month', 'indefinite'
            $table->integer('payout_month')->nullable(); // The month the salary was meant for
            $table->integer('payout_year')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('active'); // 'active', 'released'
            $table->timestamp('released_at')->nullable();
            $table->unsignedBigInteger('released_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
            $table->index(['payout_month', 'payout_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('held_salaries');
    }
};

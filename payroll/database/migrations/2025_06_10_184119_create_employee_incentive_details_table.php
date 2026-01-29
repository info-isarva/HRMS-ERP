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
        Schema::create('employee_incentive_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emp_id');
            $table->integer('payout_month');
            $table->integer('payout_year');
            $table->integer('incentive_days');
            $table->decimal('incentive_rate', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        
            $table->foreign('emp_id')->references('id')->on('employee_basic_details');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_incentive_details');
    }
};

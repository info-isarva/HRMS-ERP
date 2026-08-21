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
        Schema::create('ot_month_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('payout_month');
            $table->unsignedSmallInteger('payout_year');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ot_month_statuses');
    }
};

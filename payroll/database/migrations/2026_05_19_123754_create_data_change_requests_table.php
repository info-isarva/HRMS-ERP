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
        Schema::create('data_change_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_email')->index();
            $table->string('request_type');
            $table->text('details');
            $table->string('status')->default('pending'); // pending, resolved, rejected
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->string('source_system')->default('payroll'); // payroll or attendance
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_change_requests');
    }
};

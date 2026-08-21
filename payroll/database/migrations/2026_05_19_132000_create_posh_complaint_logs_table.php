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
        Schema::create('posh_complaint_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->unsignedBigInteger('action_by_user_id');
            $table->string('action_type'); // status_change, investigation_note, meeting_minutes, document_upload
            $table->text('notes')->nullable();
            $table->text('minutes_of_meeting')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('posh_complaints')->onDelete('cascade');
            $table->foreign('action_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posh_complaint_logs');
    }
};

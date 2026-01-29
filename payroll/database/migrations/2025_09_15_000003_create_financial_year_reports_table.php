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
        Schema::create('financial_year_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->string('report_type'); // 'annual_summary', 'payroll_summary', 'tax_summary', etc.
            $table->string('report_name');
            $table->json('report_data'); // Actual report data
            $table->string('file_path')->nullable(); // Path to generated file (PDF/Excel)
            $table->string('file_type')->nullable(); // 'pdf', 'excel', 'csv'
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->bigInteger('file_size')->nullable(); // File size in bytes
            $table->string('status')->default('completed'); // 'pending', 'processing', 'completed', 'failed'
            $table->text('error_message')->nullable(); // Error details if failed
            $table->timestamps();
            
            // Indexes
            $table->index(['financial_year_id', 'report_type']);
            $table->index('generated_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_year_reports');
    }
};

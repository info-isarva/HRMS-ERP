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
        Schema::create('employee_increments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('type', ['increment', 'promotion', 'both'])->default('increment');
            $table->unsignedBigInteger('previous_designation_id')->nullable();
            $table->unsignedBigInteger('new_designation_id')->nullable();
            $table->decimal('previous_ctc', 15, 2)->default(0);
            $table->decimal('new_ctc', 15, 2)->default(0);
            $table->decimal('increment_amount', 15, 2)->default(0);
            $table->decimal('increment_percentage', 5, 2)->default(0);
            $table->json('current_salary_structure')->nullable();
            $table->json('new_salary_structure')->nullable();
            $table->date('effective_date');
            $table->enum('status', ['pending', 'approved', 'processed', 'rejected'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employee_basic_details')->onDelete('cascade');
            // Assuming PositionType table name is 'position_types' or similar, strict checking might fail if I guess wrong, but let's assume loose check for now or skip FK for designation to be safe if table name uncertainty exists.
            // Actually, from previous context: 'PositionType.php' exists. Table usually 'position_types'.
            // Let's stick to simple columns for now to avoid FK issues if table name differs.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_increments');
    }
};

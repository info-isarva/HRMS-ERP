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
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'flexible'])->default('fixed')->after('financial_year');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');
            $table->text('description')->nullable()->after('name');
            $table->boolean('is_national')->default(true)->after('status');
            $table->string('color', 7)->default('#1f2937')->after('is_national'); // Hex color for calendar display
            $table->unsignedBigInteger('created_by')->nullable()->after('color');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Add foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'type', 
                'status', 
                'description', 
                'is_national', 
                'color', 
                'created_by', 
                'updated_by'
            ]);
        });
    }
};

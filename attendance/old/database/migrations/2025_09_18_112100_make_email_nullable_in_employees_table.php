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
        Schema::table('employees', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique('employees_email_unique');
            
            // Modify the email column to be nullable
            $table->string('email')->nullable()->change();
            
            // Add back unique constraint but only for non-null values
            $table->unique('email', 'employees_email_unique_non_null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('employees_email_unique_non_null');
            
            // Change email back to not nullable and unique
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};

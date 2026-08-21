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
        Schema::table('departments', function (Blueprint $table) {
            // First check if api_id exists before renaming
            if (Schema::hasColumn('departments', 'api_id')) {
                $table->renameColumn('api_id', 'api_department_id');
            } else {
                // If api_id doesn't exist, create api_department_id
                $table->string('api_department_id')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'api_department_id')) {
                $table->renameColumn('api_department_id', 'api_id');
            }
        });
    }
};

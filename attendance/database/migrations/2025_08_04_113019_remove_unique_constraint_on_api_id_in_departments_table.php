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
        // Check if indexes exist before dropping
        $indexes = DB::select("SHOW INDEX FROM departments WHERE Key_name = 'departments_api_id_unique'");
        if (!empty($indexes)) {
            DB::statement('ALTER TABLE departments DROP INDEX departments_api_id_unique');
        }
        
        $indexes = DB::select("SHOW INDEX FROM departments WHERE Key_name = 'departments_api_department_id_unique'");
        if (!empty($indexes)) {
            DB::statement('ALTER TABLE departments DROP INDEX departments_api_department_id_unique');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // We don't restore the unique constraint in down method
            // as it could cause problems if data was already synced
        });
    }
};

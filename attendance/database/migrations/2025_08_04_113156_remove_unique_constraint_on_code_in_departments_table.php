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
        // Check if index exists before dropping
        $indexes = DB::select("SHOW INDEX FROM departments WHERE Key_name = 'departments_code_unique'");
        if (!empty($indexes)) {
            DB::statement('ALTER TABLE departments DROP INDEX departments_code_unique');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't restore the unique constraint as it could cause problems
    }
};

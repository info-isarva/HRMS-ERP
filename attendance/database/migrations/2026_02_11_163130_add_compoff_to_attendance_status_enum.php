<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we can use change() if using doctrine/dbal or raw SQL
        // Given it's an enum, raw SQL is often more reliable
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'early_departure', 'half_day', 'overtime', 'compoff') DEFAULT 'present'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'early_departure', 'half_day', 'overtime') DEFAULT 'present'");
    }
};

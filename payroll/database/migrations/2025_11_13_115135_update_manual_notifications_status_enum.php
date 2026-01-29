<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'inactive' and update any 'completed' to 'inactive'
        DB::statement("ALTER TABLE manual_notifications MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'inactive', 'completed', 'cancelled') DEFAULT 'draft'");
        DB::statement("UPDATE manual_notifications SET status = 'inactive' WHERE status = 'completed'");
        DB::statement("ALTER TABLE manual_notifications MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'inactive', 'cancelled') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE manual_notifications SET status = 'completed' WHERE status = 'inactive'");
        DB::statement("ALTER TABLE manual_notifications MODIFY COLUMN status ENUM('draft', 'scheduled', 'active', 'completed', 'cancelled') DEFAULT 'draft'");
    }
};

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
        Schema::table('attendance_batches', function (Blueprint $table) {
            $table->string('mode')->default('timestation')->after('year'); // timestation, biometric, general
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('mode')->default('timestation')->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_batches', function (Blueprint $table) {
            $table->dropColumn('mode');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};

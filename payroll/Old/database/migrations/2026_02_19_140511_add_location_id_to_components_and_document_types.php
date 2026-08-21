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
        Schema::table('statutory_components', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->after('status');
        });

        Schema::table('salary_components', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->after('status');
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statutory_components', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });

        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });
    }
};

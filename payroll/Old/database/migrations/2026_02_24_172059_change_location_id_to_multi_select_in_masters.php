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
            $table->text('location_id')->nullable()->change();
        });

        Schema::table('position_types', function (Blueprint $table) {
            $table->text('location_id')->nullable()->change();
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->text('location_id')->nullable()->change();
        });

        Schema::table('statutory_components', function (Blueprint $table) {
            $table->text('location_id')->nullable()->change();
        });

        Schema::table('salary_components', function (Blueprint $table) {
            $table->text('location_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->change();
        });

        Schema::table('position_types', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->change();
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->change();
        });

        Schema::table('statutory_components', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->change();
        });

        Schema::table('salary_components', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->default(0)->change();
        });
    }
};

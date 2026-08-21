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
        Schema::table('employee_statutory_components', function (Blueprint $table) {
            $table->enum('epf_option', ['restrict_15000', '12_percent', 'manual_value'])->nullable()->after('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_statutory_components', function (Blueprint $table) {
            $table->dropColumn('epf_option');
        });
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('id');
            $table->string('designation')->nullable()->after('name');
            $table->date('date_of_joining')->nullable()->after('financial_year');
            $table->date('date_of_resignation')->nullable()->after('date_of_joining');
            $table->unsignedBigInteger('reporting_manager_id')->nullable()->after('date_of_resignation');

            // Add a foreign key constraint for reporting manager
            $table->foreign('reporting_manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropColumn([
                'employee_id',
                'designation',
                'date_of_joining',
                'date_of_resignation',
                'reporting_manager_id'
            ]);
        });
    }
};

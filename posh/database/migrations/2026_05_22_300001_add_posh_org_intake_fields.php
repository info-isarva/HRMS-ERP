<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'intake_key')) {
                $table->string('intake_key', 32)->nullable()->unique()->after('name');
            }
        });

        Schema::table('posh_complaints', function (Blueprint $table) {
            $table->string('intake_channel', 32)->default('portal')->after('is_anonymous');
        });
    }

    public function down(): void
    {
        Schema::table('posh_complaints', function (Blueprint $table) {
            $table->dropColumn('intake_channel');
        });
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'intake_key')) {
                $table->dropColumn('intake_key');
            }
        });
    }
};

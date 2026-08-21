<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedBigInteger('hub_user_id')->nullable()->after('organization_id');
            $table->string('employee_code', 64)->nullable()->after('email');
            $table->string('department', 128)->nullable();
            $table->string('designation', 128)->nullable();
            $table->string('posh_role', 32)->default('employee')->after('password');
            $table->index(['organization_id', 'hub_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn([
                'organization_id',
                'hub_user_id',
                'employee_code',
                'department',
                'designation',
                'posh_role',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (! Schema::hasColumn('organizations', 'employee_source')) {
                $table->string('employee_source', 16)->default('payroll')->after('hub_tenant_key');
            }
            if (! Schema::hasColumn('organizations', 'auth_mode')) {
                $table->string('auth_mode', 16)->default('sso')->after('employee_source');
            }
            if (! Schema::hasColumn('organizations', 'payroll_synced_at')) {
                $table->timestamp('payroll_synced_at')->nullable()->after('auth_mode');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_source')) {
                $table->string('user_source', 16)->default('payroll')->after('posh_role');
            }
        });

        Schema::table('posh_ic_members', function (Blueprint $table) {
            if (! Schema::hasColumn('posh_ic_members', 'member_origin')) {
                $table->string('member_origin', 16)->default('internal')->after('ic_role');
            }
            if (! Schema::hasColumn('posh_ic_members', 'employee_directory_id')) {
                $table->unsignedBigInteger('employee_directory_id')->nullable()->after('member_origin');
            }
        });

        if (! Schema::hasTable('posh_employee_directory')) {
            Schema::create('posh_employee_directory', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('employee_code', 64)->nullable();
                $table->string('department', 128)->nullable();
                $table->string('designation', 128)->nullable();
                $table->string('source', 16)->default('posh');
                $table->unsignedBigInteger('payroll_ref')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['organization_id', 'source']);
                $table->index(['organization_id', 'email']);
            });
        }

        if (Schema::hasColumn('posh_ic_members', 'employee_directory_id')) {
            Schema::table('posh_ic_members', function (Blueprint $table) {
                $table->foreign('employee_directory_id')
                    ->references('id')
                    ->on('posh_employee_directory')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('posh_ic_members', function (Blueprint $table) {
            if (Schema::hasColumn('posh_ic_members', 'employee_directory_id')) {
                $table->dropForeign(['employee_directory_id']);
                $table->dropColumn(['member_origin', 'employee_directory_id']);
            }
        });

        Schema::dropIfExists('posh_employee_directory');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_source')) {
                $table->dropColumn('user_source');
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            $cols = array_filter(['employee_source', 'auth_mode', 'payroll_synced_at'], fn ($c) => Schema::hasColumn('organizations', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};

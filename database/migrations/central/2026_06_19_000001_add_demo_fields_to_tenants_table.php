<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->table('tenants', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('status');
            $table->timestamp('demo_expires_at')->nullable()->after('is_demo');
            $table->string('demo_admin_email')->nullable()->after('demo_expires_at');
            $table->string('seed_profile', 32)->nullable()->after('demo_admin_email');
            $table->string('contact_name')->nullable()->after('seed_profile');
            $table->text('internal_notes')->nullable()->after('contact_name');

            $table->index('is_demo');
            $table->index('demo_expires_at');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('tenants', function (Blueprint $table) {
            $table->dropIndex(['is_demo']);
            $table->dropIndex(['demo_expires_at']);
            $table->dropColumn([
                'is_demo',
                'demo_expires_at',
                'demo_admin_email',
                'seed_profile',
                'contact_name',
                'internal_notes',
            ]);
        });
    }
};

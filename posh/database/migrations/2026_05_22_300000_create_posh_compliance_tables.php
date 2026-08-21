<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posh_employer_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('duty_key', 64);
            $table->string('duty_text');
            $table->boolean('is_done')->default(false);
            $table->date('done_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'duty_key']);
        });

        Schema::create('posh_prevention_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 32)->default('workshop');
            $table->string('title');
            $table->date('held_on');
            $table->unsignedInteger('attendee_count')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('posh_annual_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('report_year');
            $table->json('report_data');
            $table->timestamp('generated_at');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['organization_id', 'report_year']);
        });

        Schema::table('posh_complaints', function (Blueprint $table) {
            $table->timestamp('report_due_at')->nullable()->after('inquiry_started_at');
            $table->timestamp('management_action_due_at')->nullable()->after('report_due_at');
            $table->boolean('filing_within_deadline')->default(true)->after('incident_date');
            $table->text('extension_reason')->nullable()->after('filing_within_deadline');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
        Schema::table('posh_complaints', function (Blueprint $table) {
            $table->dropColumn(['report_due_at', 'management_action_due_at', 'filing_within_deadline', 'extension_reason']);
        });
        Schema::dropIfExists('posh_annual_reports');
        Schema::dropIfExists('posh_prevention_events');
        Schema::dropIfExists('posh_employer_duties');
    }
};

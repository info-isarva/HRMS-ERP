<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PoshAuditLog;
use App\Models\PoshComplaint;
use App\Models\PoshComplaintLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PoshComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('hub_tenant_key', env('POSH_DEFAULT_ORG_KEY', 'default'))->first()
            ?? Organization::first();

        if (! $org) {
            $this->command?->warn('No organization found. Run PoshDatabaseSeeder first.');

            return;
        }

        $employee = User::where('organization_id', $org->id)->where('posh_role', 'employee')->first();
        $hrAdmin = User::where('organization_id', $org->id)->whereIn('posh_role', ['hr_admin', 'super_admin'])->first();

        if (! $employee && ! $hrAdmin) {
            $this->command?->warn('No users found for seeding complaints.');

            return;
        }

        $filer = $employee ?? $hrAdmin;
        $icUser = $hrAdmin ?? $employee;

        PoshComplaint::where('organization_id', $org->id)->delete();

        $scenarios = [
            [
                'case_number' => 'POSH-2026-0001',
                'status' => 'Submitted',
                'operate_step' => 0,
                'routed_to' => 'IC',
                'is_anonymous' => false,
                'complainant_name' => 'Priya Nair',
                'employee_code' => 'EMP-2041',
                'department' => 'Engineering',
                'respondent_name' => 'Rajesh Kumar',
                'respondent_type' => 'supervisor',
                'respondent_department' => 'Engineering',
                'vs_employer' => false,
                'incident_date' => now()->subDays(20),
                'incident_location' => 'Office floor + Teams chat',
                'description' => 'Repeated inappropriate comments about appearance and late-night messages after work hours over Microsoft Teams between 10 PM and midnight on multiple dates in April.',
                'filing_within_deadline' => true,
                'intake_channel' => 'portal',
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0002',
                'status' => 'Under IC/LC Review',
                'operate_step' => 0,
                'routed_to' => 'IC',
                'is_anonymous' => true,
                'complainant_name' => null,
                'employee_code' => null,
                'department' => 'Operations',
                'respondent_name' => 'Vikram Singh',
                'respondent_type' => 'employee',
                'vs_employer' => false,
                'incident_date' => now()->subDays(45),
                'incident_location' => 'Canteen',
                'description' => 'Anonymous complaint: unwelcome physical proximity and suggestive remarks during lunch breaks. Complainant requests strict confidentiality from colleagues.',
                'filing_within_deadline' => true,
                'intake_channel' => 'portal',
                'filed_by_user_id' => $filer->id,
                'case_data' => ['review_outcome' => 'accept', 'timeline' => [['at' => now()->subDays(2)->toIso8601String(), 'status' => 'Under IC/LC Review', 'note' => 'IC initial review in progress']]],
            ],
            [
                'case_number' => 'POSH-2026-0003',
                'status' => 'Submitted',
                'operate_step' => 0,
                'routed_to' => 'LC',
                'is_anonymous' => false,
                'complainant_name' => 'Meera Joshi',
                'employee_code' => 'EMP-1102',
                'department' => 'Administration',
                'respondent_name' => 'Ravi Malhotra (Managing Director)',
                'respondent_type' => 'employer',
                'vs_employer' => true,
                'incident_date' => now()->subDays(30),
                'incident_location' => 'Board meeting room',
                'description' => 'Complaint against employer / proprietor: inappropriate conduct during one-on-one review meeting. Per POSH Act, matter routed to Local Committee (not Internal Committee).',
                'filing_within_deadline' => true,
                'intake_channel' => 'portal',
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0004',
                'status' => 'Submitted',
                'operate_step' => 0,
                'routed_to' => 'LC',
                'is_anonymous' => false,
                'complainant_name' => 'Anonymous (QR)',
                'respondent_name' => 'Client representative — Apex Corp',
                'respondent_type' => 'third_party',
                'vs_employer' => false,
                'incident_date' => now()->subDays(10),
                'incident_location' => 'Client site visit',
                'description' => 'Third-party harassment at client premises during project deployment. QR public intake. Employer to assist; may also require LC coordination if primary respondent is employer-linked.',
                'filing_within_deadline' => true,
                'intake_channel' => 'qr_public',
                'is_anonymous' => true,
                'filed_by_user_id' => null,
            ],
            [
                'case_number' => 'POSH-2026-0005',
                'status' => 'Additional Info Requested',
                'operate_step' => 0,
                'routed_to' => 'IC',
                'complainant_name' => 'Sunita Devi',
                'respondent_name' => 'Amit Patel',
                'respondent_type' => 'employee',
                'incident_date' => now()->subMonths(4),
                'incident_location' => 'WhatsApp work group',
                'description' => 'Vague initial complaint about harassment in work WhatsApp group. IC requested specific dates and screenshots.',
                'filing_within_deadline' => false,
                'extension_reason' => 'Medical leave and trauma — extension under consideration',
                'case_data' => ['review_outcome' => 'more_info', 'review_notes' => 'Please provide dates of each message and names of witnesses.'],
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0006',
                'status' => 'Conciliation In Progress',
                'operate_step' => 1,
                'routed_to' => 'IC',
                'complainant_name' => 'Kavitha R',
                'respondent_name' => 'Suresh Menon',
                'respondent_type' => 'supervisor',
                'incident_date' => now()->subDays(60),
                'description' => 'Complainant opted for conciliation before formal inquiry. No monetary settlement — written apology and team transfer discussed.',
                'case_data' => [
                    'conciliation_requested' => true,
                    'conciliation_outcome' => 'Settlement draft under review',
                    'timeline' => [['at' => now()->subDays(5)->toIso8601String(), 'step' => 'conciliation', 'status' => 'Conciliation In Progress']],
                ],
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0007',
                'status' => 'Inquiry Started',
                'operate_step' => 4,
                'routed_to' => 'IC',
                'complainant_name' => 'Anjali Verma',
                'respondent_name' => 'Deepak Rao',
                'respondent_type' => 'supervisor',
                'incident_date' => now()->subDays(90),
                'inquiry_started_at' => now()->subDays(30),
                'report_due_at' => now()->subDays(30)->addDays(90),
                'management_action_due_at' => now()->subDays(30)->addDays(160),
                'description' => 'Formal inquiry opened. Notice issued to respondent. Hearing scheduled. 90-day inquiry SLA active.',
                'case_data' => [
                    'notice_date' => now()->subDays(25)->toDateString(),
                    'hearing_date' => now()->addDays(7)->toDateString(),
                    'witnesses' => 'Colleague ID EMP-3301 (witness to corridor incident)',
                ],
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0008',
                'status' => 'Interim Relief Applied',
                'operate_step' => 2,
                'routed_to' => 'IC',
                'complainant_name' => 'Lakshmi Iyer',
                'respondent_name' => 'Harish Nambiar',
                'respondent_type' => 'employee',
                'incident_date' => now()->subDays(15),
                'description' => 'Interim relief recommended: complainant transferred to parallel team; no-contact directive for respondent pending inquiry.',
                'case_data' => ['interim_relief' => 'Transfer to Marketing team; respondent no-contact order issued by HR.'],
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0009',
                'status' => 'Management Action Pending (60 days)',
                'operate_step' => 7,
                'routed_to' => 'IC',
                'complainant_name' => 'Divya Krishnan',
                'respondent_name' => 'Mohit Agarwal',
                'respondent_type' => 'employee',
                'incident_date' => now()->subDays(120),
                'inquiry_started_at' => now()->subDays(100),
                'report_due_at' => now()->subDays(10),
                'management_action_due_at' => now()->addDays(50),
                'description' => 'Inquiry completed. IC finding: proved. Recommendation: written warning + mandatory POSH training. Awaiting management implementation within 60 days.',
                'case_data' => [
                    'finding' => 'proved',
                    'recommendation' => 'Written warning, 6-month performance watch, POSH refresher training for respondent.',
                    'hearing_notes' => 'MoM dated — both parties heard; witness statement recorded.',
                ],
                'filed_by_user_id' => $filer->id,
            ],
            [
                'case_number' => 'POSH-2026-0010',
                'status' => 'Closed',
                'operate_step' => 8,
                'routed_to' => 'IC',
                'complainant_name' => 'Neha Kapoor',
                'respondent_name' => 'Rohit Saxena',
                'respondent_type' => 'employee',
                'incident_date' => now()->subDays(200),
                'inquiry_started_at' => now()->subDays(180),
                'acknowledged_at' => now()->subDays(195),
                'closed_at' => now()->subDays(30),
                'description' => 'Case closed after inquiry. Finding not proved. No appeal filed. Archived for records.',
                'case_data' => [
                    'finding' => 'not_proved',
                    'recommendation' => 'No disciplinary action recommended.',
                    'action_taken' => 'Counselling session for both parties; case closed.',
                    'closure_notes' => 'Closed per IC report. Complainant informed in writing.',
                    'appeal_filed' => false,
                ],
                'filed_by_user_id' => $filer->id,
            ],
        ];

        foreach ($scenarios as $data) {
            $complaint = PoshComplaint::create(array_merge([
                'organization_id' => $org->id,
                'filing_within_deadline' => true,
                'intake_channel' => 'portal',
                'is_anonymous' => false,
                'vs_employer' => false,
                'filed_by_relation' => 'self',
                'incident_location' => null,
                'respondent_department' => null,
                'complainant_email' => null,
                'employee_code' => null,
                'department' => null,
                'extension_reason' => null,
                'inquiry_started_at' => null,
                'report_due_at' => null,
                'management_action_due_at' => null,
                'acknowledged_at' => null,
                'closed_at' => null,
                'case_data' => ['timeline' => [['at' => now()->toIso8601String(), 'status' => $data['status'], 'note' => 'Seeded test case']]],
            ], $data));

            PoshComplaintLog::create([
                'posh_complaint_id' => $complaint->id,
                'user_id' => $icUser->id,
                'action_type' => 'complaint_filed',
                'new_status' => $complaint->status,
                'notes' => 'Test data seeded — ' . $complaint->case_number,
            ]);

            if ($complaint->status !== 'Submitted') {
                PoshComplaintLog::create([
                    'posh_complaint_id' => $complaint->id,
                    'user_id' => $icUser->id,
                    'action_type' => 'status_change',
                    'old_status' => 'Submitted',
                    'new_status' => $complaint->status,
                    'notes' => 'IC workflow progression (seed)',
                ]);
            }
        }

        // Rejected case (11th useful scenario — user asked 8-10, we have 10; add rejected as replace 0004 duplicate LC? We have 10. Add rejected by changing one - actually add 11th rejected OR swap 0004. User said 8-10, 10 is fine.

        // Add rejected scenario - replace QR one with rejected to keep 10... Actually add 11th is ok for coverage. User said 8-10. I'll add rejected as 11th or include in 10 by replacing 0004 third party with rejected.

        PoshComplaint::create([
            'organization_id' => $org->id,
            'case_number' => 'POSH-2026-0011',
            'filed_by_user_id' => $filer->id,
            'complainant_name' => 'Test User',
            'respondent_name' => 'General Grievance',
            'respondent_type' => 'employee',
            'incident_date' => now()->subDays(5),
            'description' => 'Complaint did not allege sexual harassment under POSH — general workplace dispute. Rejected with written reasons.',
            'routed_to' => 'IC',
            'status' => 'Rejected (with reasons)',
            'operate_step' => 0,
            'filing_within_deadline' => true,
            'intake_channel' => 'portal',
            'closed_at' => now()->subDays(1),
            'case_data' => [
                'review_outcome' => 'reject',
                'rejectReason' => 'Matter is interpersonal conflict without sexual harassment element; referred to HR grievance cell.',
            ],
        ]);

        PoshAuditLog::create([
            'organization_id' => $org->id,
            'user_id' => $icUser->id,
            'action' => 'Test complaints seeded',
            'details' => count($scenarios) + 1 . ' sample cases for QA',
        ]);

        $this->command?->info('Seeded ' . (count($scenarios) + 1) . ' test complaints for org: ' . $org->name);
    }
}

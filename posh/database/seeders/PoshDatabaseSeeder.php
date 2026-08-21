<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PoshIcMember;
use App\Models\PoshPolicy;
use App\Models\User;
use App\Services\PoshPayrollSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PoshDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['hub_tenant_key' => env('POSH_DEFAULT_ORG_KEY', 'default')],
            [
                'name' => env('POSH_DEFAULT_ORG_NAME', 'Demo Workplace'),
                'employee_count' => 50,
                'employee_source' => 'payroll',
                'auth_mode' => 'sso',
                'is_active' => true,
            ]
        );

        $org->update([
            'employee_source' => 'payroll',
            'auth_mode' => 'sso',
        ]);

        User::updateOrCreate(
            ['email' => 'posh.admin@example.com'],
            [
                'name' => 'POSH HR Admin',
                'password' => Hash::make('password'),
                'organization_id' => $org->id,
                'posh_role' => 'hr_admin',
                'user_source' => 'posh',
                'status' => 1,
            ]
        );

        PoshIcMember::firstOrCreate(
            [
                'organization_id' => $org->id,
                'email' => 'presiding@example.com',
            ],
            [
                'name' => 'Anita Sharma',
                'employee_code' => 'EMP-103',
                'department' => 'Human Resources',
                'designation' => 'VP - HR',
                'ic_role' => 'presiding_officer',
                'member_origin' => 'internal',
                'contact_number' => '9876543210',
                'is_woman' => true,
                'sort_order' => 1,
            ]
        );

        PoshIcMember::firstOrCreate(
            [
                'organization_id' => $org->id,
                'email' => 'external.ngo@demo.local',
            ],
            [
                'name' => 'Dr. Meera Krishnan',
                'designation' => 'NGO Representative',
                'ic_role' => 'external_member',
                'member_origin' => 'external',
                'contact_number' => '9876500000',
                'is_woman' => true,
                'sort_order' => 4,
            ]
        );

        PoshPolicy::firstOrCreate(
            [
                'organization_id' => $org->id,
                'version' => 'v2026.1',
            ],
            [
                'title' => 'POSH Workplace Policy',
                'content' => $this->defaultPolicyContent(),
                'is_active' => true,
                'published_at' => now(),
            ]
        );

        app(PoshPayrollSyncService::class)->sync($org);
    }

    protected function defaultPolicyContent(): string
    {
        return <<<'HTML'
<h2>Prevention of Sexual Harassment Policy</h2>
<p>This organization is committed to a workplace free from sexual harassment under the Sexual Harassment of Women at Workplace (Prevention, Prohibition and Redressal) Act, 2013.</p>
<ul>
<li>All employees have the right to file a complaint with the Internal Committee (IC).</li>
<li>Complaints are handled confidentially.</li>
<li>Retaliation against complainants is prohibited and treated as misconduct.</li>
</ul>
<p>Contact your IC for assistance or use the portal to acknowledge this policy.</p>
HTML;
    }
}

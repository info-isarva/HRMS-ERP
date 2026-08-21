<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PoshEmployeeDirectory;
use App\Models\PoshIcMember;
use App\Models\User;
use App\Services\PoshComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWTAuth;

class SSOAuthController extends Controller
{
    public function __construct(protected JWTAuth $jwt)
    {
    }

    public function authenticate(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $token = $request->query('token');
            $payload = $this->jwt->setToken($token)->getPayload();

            if (now()->timestamp > $payload->get('exp')) {
                throw new \Exception('Token expired');
            }

            $userData = $payload->get('user');
            $expectedHmac = hash_hmac(
                'sha256',
                $userData['id'] . $userData['email'],
                env('JWT_HMAC_SECRET')
            );

            if (! hash_equals($expectedHmac, $userData['hmac'])) {
                throw new \Exception('Invalid token signature');
            }

            $organization = Organization::firstOrCreate(
                ['hub_tenant_key' => env('POSH_DEFAULT_ORG_KEY', 'default')],
                [
                    'name' => env('POSH_DEFAULT_ORG_NAME', 'Organization'),
                    'employee_count' => (int) env('POSH_DEFAULT_EMPLOYEE_COUNT', 10),
                    'is_active' => true,
                    'intake_key' => Str::lower(Str::random(12)),
                ]
            );
            if (! $organization->intake_key) {
                $organization->update(['intake_key' => Str::lower(Str::random(12))]);
            }
            app(PoshComplianceService::class)->seedDutiesForOrganization($organization->id);

            $poshRole = $this->resolvePoshRole($userData['email'], $organization->id);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'] ?? 'Employee',
                    'password' => Hash::make(Str::random(24)),
                    'hub_user_id' => $userData['id'],
                    'organization_id' => $organization->id,
                    'posh_role' => $poshRole,
                    'user_source' => $organization->usesPayrollEmployees() ? 'payroll' : 'posh',
                    'status' => 1,
                ]
            );

            if ($organization->usesPayrollEmployees()) {
                PoshEmployeeDirectory::updateOrCreate(
                    ['organization_id' => $organization->id, 'email' => $userData['email']],
                    [
                        'name' => $userData['name'] ?? 'Employee',
                        'source' => 'payroll',
                        'payroll_ref' => $userData['id'] ?? null,
                        'user_id' => $user->id,
                        'is_active' => true,
                    ]
                );
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('POSH SSO failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return redirect()->to(config('posh.workspace_url') . '/posh-access')
                ->with('error', 'POSH sign-in failed: ' . $e->getMessage());
        }
    }

    public function passiveLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    protected function resolvePoshRole(string $email, int $organizationId): string
    {
        $bootstrapAdmins = array_filter(array_map('trim', explode(',', (string) env('POSH_BOOTSTRAP_ADMIN_EMAILS', ''))));
        if (in_array($email, $bootstrapAdmins, true)) {
            return 'hr_admin';
        }

        $icMember = PoshIcMember::where('organization_id', $organizationId)
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if (! $icMember) {
            return 'employee';
        }

        return match ($icMember->ic_role) {
            'presiding_officer' => 'presiding_officer',
            'external_member' => 'external_member',
            default => 'ic_member',
        };
    }
}

<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Services\DemoTenantProvisioner;
use App\Services\DemoTenantCredentialService;
use App\Services\DemoTenantUsageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DemoTenantController extends Controller
{
    public function __construct(
        private DemoTenantProvisioner $provisioner,
        private DemoTenantUsageService $usageService,
        private DemoTenantCredentialService $credentialService,
    ) {
    }

    public function index(): View
    {
        $tenants = Tenant::query()
            ->where('is_demo', true)
            ->orderByRaw("CASE WHEN demo_expires_at IS NULL THEN 1 ELSE 0 END")
            ->orderBy('demo_expires_at')
            ->get()
            ->map(function (Tenant $tenant) {
                $usage = $this->usageService->analyze($tenant);

                return [
                    'tenant' => $tenant,
                    'usage' => $usage,
                    'days' => $tenant->demoDaysRemaining(),
                    'status' => $tenant->demoStatusLabel(),
                    'has_credentials' => $this->credentialService->resolve($tenant) !== null,
                ];
            });

        $stats = [
            'total' => $tenants->count(),
            'active' => $tenants->filter(fn ($row) => $row['tenant']->isActive() && ! $row['tenant']->isDemoExpired())->count(),
            'ending_soon' => $tenants->filter(fn ($row) => $row['days'] !== null && $row['days'] >= 0 && $row['days'] <= 3)->count(),
            'expired' => $tenants->filter(fn ($row) => $row['tenant']->isDemoExpired() || $row['tenant']->status === 'inactive')->count(),
            'avg_usage' => $tenants->count() > 0
                ? (int) round($tenants->avg(fn ($row) => $row['usage']['score']))
                : 0,
        ];

        return view('central.demo-tenants.index', compact('tenants', 'stats'));
    }

    public function create(): View
    {
        return view('central.demo-tenants.create', [
            'defaultDays' => config('platform.default_demo_days', 15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_code' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'demo_days' => ['required_without:expires_at', 'nullable', 'integer', 'min:1', 'max:90'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'seed_profile' => ['required', 'in:none,standard'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $code = strtoupper($validated['company_code']);

        if (Tenant::query()->where('company_code', $code)->where('status', 'active')->exists()) {
            return back()->withInput()->withErrors([
                'company_code' => "Company code [{$code}] is already registered.",
            ]);
        }

        try {
            $result = $this->provisioner->provision([
                'company_code' => $code,
                'name' => $validated['name'],
                'demo_days' => (int) ($validated['demo_days'] ?? config('platform.default_demo_days', 15)),
                'expires_at' => $validated['expires_at'] ?? null,
                'admin_email' => $validated['admin_email'],
                'admin_name' => $validated['admin_name'] ?? null,
                'seed_profile' => $validated['seed_profile'],
                'contact_name' => $validated['contact_name'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'provision' => $e->getMessage(),
            ]);
        }

        $this->credentialService->store(
            $result['tenant'],
            $result['password'],
            $validated['admin_name'] ?? ($validated['name'].' Admin')
        );

        return redirect()
            ->route('platform.demo-tenants.show', $result['tenant'])
            ->with('provision_result', [
                'password' => $result['password'],
                'steps' => $result['steps'],
                'warnings' => $result['warnings'],
            ]);
    }

    public function show(Tenant $tenant): View
    {
        abort_unless($tenant->is_demo, 404);

        $usage = $this->usageService->analyze($tenant);

        return view('central.demo-tenants.show', [
            'tenant' => $tenant,
            'usage' => $usage,
            'credentials' => $this->credentialService->resolve($tenant),
            'shareMessage' => $this->credentialService->shareMessage($tenant),
            'loginUrl' => config('platform.login_url'),
            'payrollUrl' => config('platform.payroll_url'),
            'attendanceUrl' => config('platform.attendance_url'),
        ]);
    }

    public function extend(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->is_demo, 404);

        $validated = $request->validate([
            'extra_days' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $this->provisioner->extend($tenant, (int) $validated['extra_days']);

        return back()->with('status', "Extended demo by {$validated['extra_days']} days.");
    }

    public function deactivate(Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->is_demo, 404);

        $this->provisioner->deactivate($tenant);

        return back()->with('status', "Demo [{$tenant->company_code}] deactivated.");
    }

    public function refreshUsage(Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->is_demo, 404);

        $snapshot = $this->usageService->snapshot($tenant);
        $tenant->update([
            'meta' => array_merge($tenant->meta ?? [], ['last_usage_snapshot' => $snapshot]),
        ]);

        return back()->with('status', 'Usage stats refreshed.');
    }

    public static function canAccess(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        $allowed = in_array(
            strtolower($email),
            array_map('strtolower', config('platform.admin_emails', [])),
            true
        );

        if (! $allowed) {
            return false;
        }

        $internalCode = strtoupper((string) config('platform.internal_company_code', 'ISARVADEV'));
        $sessionCode = strtoupper((string) session('company_code', ''));

        return $sessionCode === $internalCode;
    }

    /** @deprecated Use canAccess() */
    public static function isPlatformAdmin(?string $email): bool
    {
        return self::canAccess($email);
    }

    public static function suggestCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', Str::upper($name)));

        return substr($base, 0, 12) ?: 'DEMO'.now()->format('ymd');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\TenantSsoService;
use App\Services\TenantLoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Cookie;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private TenantSsoService $tenantSso,
        private TenantLoginService $tenantLogin,
    ) {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            // Recover from stale authenticated sessions across tenant switches.
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->tenantLogin->clearSession();
        }

        $request->authenticate();

        $user = Auth::user();

        // Check if the user has accepted the DPDP policy
        $hasAcceptedDpdp = $user->consents()
            ->where('policy_type', 'dpdp_act')
            ->where('is_accepted', true)
            ->exists();

        if (! $hasAcceptedDpdp) {
            // Keep the authenticated session alive while completing consent flow.
            session([
                'pending_dpdp_user_id' => $user->id,
                'pending_tenant_id' => session('tenant_id'),
                'pending_company_code' => session('company_code'),
            ]);

            return redirect()->route('compliance.dpdp.policy');
        }

        // Check if the user has accepted the POSH policy
        $hasAcceptedPosh = $user->consents()
            ->where('policy_type', 'posh_policy')
            ->where('is_accepted', true)
            ->exists();

        if (! $hasAcceptedPosh) {
            // Keep the authenticated session alive while completing consent flow.
            session([
                'pending_posh_user_id' => $user->id,
                'pending_tenant_id' => session('tenant_id'),
                'pending_company_code' => session('company_code'),
            ]);

            return redirect()->route('compliance.posh.policy');
        }

        $request->session()->regenerate();
        // Ensure tenant context is re-persisted after session ID rotation.
        $tenant = $this->tenantLogin->findActiveByCompanyCode((string) $request->input('company_code'));
        $this->tenantLogin->apply($tenant, persistSession: true);

        $this->tenantSso->storeHubTokens($user);

        $redirectUrl = $request->input('redirect') ?? session()->pull('sso_redirect_after_login');
        if ($redirectUrl) {
            if (str_contains($redirectUrl, 'attendance')) {
                return redirect()->route('attendance.redirect');
            } elseif (str_contains($redirectUrl, 'payroll')) {
                return redirect()->route('payroll.sso');
            } elseif (str_contains($redirectUrl, 'crm')) {
                return redirect()->route('crm.sso');
            } elseif (str_contains($redirectUrl, 'posh')) {
                return redirect()->route('posh.sso');
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();	
        $request->session()->forget([
            'payroll_token',
            'attendance_token',
        ]);
        $this->tenantLogin->clearSession();
        $logoutUrls = $this->passiveLogoutUrls();
        $domain = config('session.domain');

    return redirect()->route('logout.hub', ['urls' => $logoutUrls])
        ->withCookie(Cookie::forget(config('session.cookie'), '/', $domain)) 
        ->withCookie(Cookie::forget('attendance_token', '/', $domain))
        ->withCookie(Cookie::forget('dev_payroll_session', '/', $domain))
        ->withCookie(Cookie::forget('dev_attendance_session', '/', $domain));
    }

    public function ssoLogout(Request $request): RedirectResponse
    {
      
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget([
            'payroll_token',
            'attendance_token',
        ]);

        $logoutUrls = $this->passiveLogoutUrls();
        $domain = config('session.domain');

    return redirect()->route('logout.hub', ['urls' => $logoutUrls])
        ->withCookie(Cookie::forget(config('session.cookie'), '/', $domain)) 
        ->withCookie(Cookie::forget('attendance_token', '/', $domain))
        ->withCookie(Cookie::forget('dev_payroll_session', '/', $domain))
        ->withCookie(Cookie::forget('dev_attendance_session', '/', $domain));
    }

    /**
     * @return list<string>
     */
    private function passiveLogoutUrls(): array
    {
        return [
            $this->tenantSso->moduleUrl('payroll').'/sso-passive-logout',
            $this->tenantSso->moduleUrl('attendance').'/sso-passive-logout',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserConsent;
use Carbon\Carbon;
use App\Services\TenantSsoService;
use App\Services\TenantLoginService;

class ComplianceController extends Controller
{
    public function __construct(
        private TenantSsoService $tenantSso,
        private TenantLoginService $tenantLogin,
    ) {
    }

    private function applyPendingTenantContext(): void
    {
        $tenant = $this->tenantLogin->resolvePendingCompanyCode()
            ?? ($this->tenantLogin->resolveFromSession());

        if ($tenant) {
            $this->tenantLogin->apply($tenant);
        }
    }

    /**
     * Show the DPDP policy consent page
     */
    public function showDpdpPolicy(Request $request)
    {
        // If they are already logged in and accepted, send them home
        if (Auth::check()) {
            $hasAccepted = Auth::user()->consents()
                ->where('policy_type', 'dpdp_act')
                ->where('is_accepted', true)
                ->exists();
                
            if ($hasAccepted) {
                return redirect()->route('dashboard');
            }
        }
        
        // If they are not logged in and don't have a pending consent session, send to login
        if (!Auth::check() && !session()->has('pending_dpdp_user_id')) {
            return redirect()->route('login');
        }
        
        return view('compliance.dpdp-policy');
    }

    /**
     * Process the DPDP policy acceptance or rejection
     */
    public function acceptDpdpPolicy(Request $request)
    {
        if ($request->has('reject')) {
            session()->forget('pending_dpdp_user_id');
            return redirect()->route('login')->withErrors(['email' => 'You have rejected the privacy policy. Unable to login.']);
        }

        $request->validate([
            'accept_terms' => 'required',
        ]);

        $userId = session('pending_dpdp_user_id') ?? Auth::id();

        if (!$userId) {
            return redirect()->route('login');
        }

        $this->applyPendingTenantContext();

        $user = \App\Models\User::find($userId);

        if (!$user) {
            session()->forget('pending_dpdp_user_id');
            return redirect()->route('login');
        }

        // Record the consent
        UserConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'dpdp_act',
            'is_accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => Carbon::now(),
        ]);

        // If they were in the pre-login flow, log them in now and generate SSO tokens
        if (session()->has('pending_dpdp_user_id')) {
            $hasAcceptedPosh = $user->consents()
                ->where('policy_type', 'posh_policy')
                ->where('is_accepted', true)
                ->exists();

            if (!$hasAcceptedPosh) {
                session(['pending_posh_user_id' => $user->id]);
                session()->forget('pending_dpdp_user_id');
                return redirect()->route('compliance.posh.policy');
            }

            Auth::login($user);
            
            session()->regenerate();

            $tenant = $this->tenantLogin->resolvePendingCompanyCode();
            if ($tenant) {
                $this->tenantLogin->persistSession($tenant);
            }

            $this->tenantSso->storeHubTokens($user);

            session()->forget(['pending_dpdp_user_id', 'pending_tenant_id', 'pending_company_code']);
        }

        // Redirect back to intended page or dashboard
        return redirect()->intended(route('dashboard', absolute: false))->with('status', 'Thank you for accepting the Privacy Policy.');
    }

    /**
     * Show the POSH policy consent page
     */
    public function showPoshPolicy(Request $request)
    {
        // If they are already logged in and accepted, send them home
        if (Auth::check()) {
            $hasAccepted = Auth::user()->consents()
                ->where('policy_type', 'posh_policy')
                ->where('is_accepted', true)
                ->exists();
                
            if ($hasAccepted) {
                return redirect()->route('dashboard');
            }
        }
        
        // If they are not logged in and don't have a pending consent session, send to login
        if (!Auth::check() && !session()->has('pending_posh_user_id')) {
            return redirect()->route('login');
        }
        
        return view('compliance.posh-policy');
    }

    /**
     * Process the POSH policy acceptance or rejection
     */
    public function acceptPoshPolicy(Request $request)
    {
        if ($request->has('reject')) {
            session()->forget('pending_posh_user_id');
            return redirect()->route('login')->withErrors(['email' => 'You have rejected the POSH policy. Unable to login.']);
        }

        $request->validate([
            'accept_terms' => 'required',
        ]);

        $userId = session('pending_posh_user_id') ?? Auth::id();

        if (!$userId) {
            return redirect()->route('login');
        }

        $this->applyPendingTenantContext();

        $user = \App\Models\User::find($userId);

        if (!$user) {
            session()->forget('pending_posh_user_id');
            return redirect()->route('login');
        }

        // Record the consent
        UserConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'posh_policy',
            'is_accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => Carbon::now(),
        ]);

        // If they were in the pre-login flow, log them in now and generate SSO tokens
        if (session()->has('pending_posh_user_id')) {
            Auth::login($user);
            
            session()->regenerate();

            $tenant = $this->tenantLogin->resolvePendingCompanyCode();
            if ($tenant) {
                $this->tenantLogin->persistSession($tenant);
            }

            $this->tenantSso->storeHubTokens($user);

            session()->forget(['pending_posh_user_id', 'pending_tenant_id', 'pending_company_code']);
        }

        // Redirect back to intended page or dashboard
        return redirect()->intended(route('dashboard', absolute: false))->with('status', 'Thank you for accepting the POSH Policy.');
    }
}

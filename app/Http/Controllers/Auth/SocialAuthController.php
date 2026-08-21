<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Services\TenantLoginService;
use App\Services\TenantSsoService;

class SocialAuthController extends Controller
{
    public function __construct(
        private TenantSsoService $tenantSso,
        private TenantLoginService $tenantLogin,
    ) {
    }

    public function redirectToGoogle(Request $request)
    {
        $tenant = $this->tenantLogin->findActiveByCompanyCode((string) $request->query('company_code', ''));
        $this->tenantLogin->storePendingCompanyCode($tenant);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $tenant = $this->tenantLogin->resolvePendingCompanyCode();

            if (! $tenant) {
                return redirect()->route('login')->withErrors([
                    'company_code' => 'Company code session expired. Please sign in again.',
                ]);
            }

            $this->tenantLogin->apply($tenant);

            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                if (! $user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                return redirect()->route('login')->withErrors(['email' => 'Access denied. Your email is not registered in our system.']);
            }

            Auth::login($user);

            $hasAcceptedDpdp = $user->consents()
                ->where('policy_type', 'dpdp_act')
                ->where('is_accepted', true)
                ->exists();

            if (! $hasAcceptedDpdp) {
                Auth::guard('web')->logout();
                session()->invalidate();
                session()->regenerateToken();
                session([
                    'pending_dpdp_user_id' => $user->id,
                    'pending_tenant_id' => $tenant->id,
                    'pending_company_code' => strtoupper($tenant->company_code),
                ]);

                return redirect()->route('compliance.dpdp.policy');
            }

            $this->tenantLogin->persistSession($tenant);
            $this->tenantSso->storeHubTokens($user);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to login with Google.']);
        }
    }
}

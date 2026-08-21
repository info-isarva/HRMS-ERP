<?php

namespace App\Http\Controllers;

use App\Services\TenantSsoService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class PayrollSSOController extends Controller
{
    public function __construct(
        private TenantSsoService $tenantSso,
        private \App\Services\TenantContext $tenantContext
    ) {
    }

    public function redirectToPayroll()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $token = session('payroll_token', null);
        $resolvedTenantId = $this->tenantContext->tenant()?->id;

        if (! $token) {
            $token = $this->generatePayrollToken($user);
        } else {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                $tokenTenantId = $payload->get('tenant_id');
                
                if (! $tokenTenantId || $tokenTenantId !== $resolvedTenantId) {
                    $token = $this->generatePayrollToken($user);
                }
            } catch (TokenExpiredException $e) {
                $token = $this->generatePayrollToken($user);
            }
        }

        $redirectTo = $this->tenantSso->ssoAuthenticateUrl('payroll').'?token='.urlencode($token);

        return redirect()->away($redirectTo);
    }

    protected function generatePayrollToken(JWTSubject $user): string
    {
        $token = $this->tenantSso->tokenForUser($user);
        session(['payroll_token' => $token]);

        return $token;
    }
}

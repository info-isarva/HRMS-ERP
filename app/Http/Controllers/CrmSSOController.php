<?php

namespace App\Http\Controllers;

use App\Services\TenantSsoService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CrmSSOController extends Controller
{
    public function __construct(private TenantSsoService $tenantSso)
    {
    }

    public function redirectToCrm()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $token = session('crm_token', null);

        if (! $token) {
            $token = $this->generateCrmToken($user);
        } else {
            try {
                JWTAuth::setToken($token)->getPayload();
            } catch (TokenExpiredException $e) {
                $token = $this->generateCrmToken($user);
            }
        }

        $redirectTo = $this->tenantSso->ssoAuthenticateUrl('crm').'?token='.urlencode($token);

        return redirect()->away($redirectTo);
    }

    protected function generateCrmToken(JWTSubject $user): string
    {
        $token = $this->tenantSso->tokenForUser($user, ['name' => $user->name]);
        session(['crm_token' => $token]);

        return $token;
    }
}

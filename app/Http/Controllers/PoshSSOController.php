<?php

namespace App\Http\Controllers;

use App\Services\TenantSsoService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class PoshSSOController extends Controller
{
    public function __construct(private TenantSsoService $tenantSso)
    {
    }

    public function redirectToPosh()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $poshUrl = $this->tenantSso->moduleUrl('posh');
        if (! $poshUrl) {
            return redirect()->route('posh.coming-soon')
                ->with('error', 'POSH module URL is not configured. Set POSH_URL in .env');
        }

        $user = Auth::user();
        $token = session('posh_token');

        if (! $token) {
            $token = $this->generatePoshToken($user);
        } else {
            try {
                JWTAuth::setToken($token)->getPayload();
            } catch (TokenExpiredException $e) {
                $token = $this->generatePoshToken($user);
            }
        }

        $redirectTo = $poshUrl.'/sso-authenticate?token='.urlencode($token);

        return redirect()->away($redirectTo);
    }

    protected function generatePoshToken(JWTSubject $user): string
    {
        $token = $this->tenantSso->tokenForUser($user, ['name' => $user->name]);
        session(['posh_token' => $token]);

        return $token;
    }
}

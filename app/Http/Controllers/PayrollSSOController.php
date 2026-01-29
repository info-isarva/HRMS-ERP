<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class PayrollSSOController extends Controller
{
    public function redirectToPayroll()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Retrieve token from session (if any)
        $token = session('payroll_token', null);

        // If no token or it’s expired, regenerate
        if (! $token) {
            $token = $this->generatePayrollToken($user);
        } else {
            try {
                // this will throw TokenExpiredException if expired
                JWTAuth::setToken($token)->getPayload();
            } catch (TokenExpiredException $e) {
                // regenerate a fresh token
                $token = $this->generatePayrollToken($user);
            }
        }

        // Build Payroll URL with token query-string
        $baseUrl    = config('services.payroll.url') . '/sso-authenticate';
        $redirectTo = $baseUrl . '?token=' . urlencode($token);

        return redirect()->away($redirectTo);
    }

    protected function generatePayrollToken(JWTSubject $user): string
    {
        $claims = [
            'exp'  => now()->addMinutes(5)->timestamp,
            'jti'  => Str::uuid(),
            'user' => [
                'id'    => $user->getAuthIdentifier(),
                'email' => $user->email,
                'hmac'  => hash_hmac(
                    'sha256',
                    $user->getAuthIdentifier() . $user->email,
                    env('JWT_HMAC_SECRET')
                ),
            ],
        ];

        $token = JWTAuth::customClaims($claims)
                        ->fromUser($user);

        // store for next time
        session(['payroll_token' => $token]);

        return $token;
    }
}

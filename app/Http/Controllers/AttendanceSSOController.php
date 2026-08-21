<?php

namespace App\Http\Controllers;

use App\Services\TenantSsoService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AttendanceSSOController extends Controller
{
    public function __construct(
        private TenantSsoService $tenantSso,
        private \App\Services\TenantContext $tenantContext
    ) {
    }

    public function redirectToAttendance()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $token = session('attendance_token', null);
        $resolvedTenantId = $this->tenantContext->tenant()?->id;

        \Illuminate\Support\Facades\Log::debug('SSO redirect debug', [
            'user_id' => $user->id,
            'session_all' => session()->all(),
            'resolved_tenant_id' => $resolvedTenantId,
            'has_token' => !empty($token),
        ]);

        if (! $token) {
            $token = $this->generateAttendanceToken($user);
        } else {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                $tokenTenantId = $payload->get('tenant_id');
                
                if (! $tokenTenantId || $tokenTenantId !== $resolvedTenantId) {
                    $token = $this->generateAttendanceToken($user);
                }
            } catch (TokenExpiredException $e) {
                $token = $this->generateAttendanceToken($user);
            }
        }

        $redirectTo = $this->tenantSso->ssoAuthenticateUrl('attendance').'?token='.urlencode($token);

        return redirect()->away($redirectTo);
    }

    protected function generateAttendanceToken(JWTSubject $user): string
    {
        $token = $this->tenantSso->tokenForUser($user);
        session(['attendance_token' => $token]);

        return $token;
    }
}

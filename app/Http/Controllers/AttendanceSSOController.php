<?php
namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AttendanceSSOController extends Controller
{
    public function redirectToAttendance()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $token = session('attendance_token', null);

        if (! $token) {
            $token = $this->generateAttendanceToken($user);
        } else {
            try {
                JWTAuth::setToken($token)->getPayload();
            } catch (TokenExpiredException $e) {
                $token = $this->generateAttendanceToken($user);
            }
        }

        $baseUrl    = config('services.attendance.url') . '/sso-authenticate';
        $redirectTo = $baseUrl . '?token=' . urlencode($token);
        //print_R($redirectTo);exit;
        return redirect()->away($redirectTo);
    }

    protected function generateAttendanceToken(JWTSubject $user): string
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

        session(['attendance_token' => $token]);

        return $token;
    }
}

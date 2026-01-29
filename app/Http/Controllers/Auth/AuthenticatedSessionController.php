<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Cookie;

class AuthenticatedSessionController extends Controller
{
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
        $request->authenticate();

        $request->session()->regenerate();

        $token = JWTAuth::customClaims([
            'exp' => now()->addMinutes(5)->timestamp,
            'jti' => Str::uuid(),
            'user' => [
                'id' => Auth::id(),
                'email' => Auth::user()->email,
                'hmac' => hash_hmac('sha256', Auth::id().Auth::user()->email, env('JWT_HMAC_SECRET'))
            ]
        ])->fromUser(Auth::user());

        session(['payroll_token' => $token,'attendance_token' => $token]);

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
        $logoutUrls = [
            env('PAYROLL_URL') . '/sso-passive-logout',
            env('ATTENDANCE_URL') . '/sso-passive-logout',
        ];
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

        $logoutUrls = [
            env('PAYROLL_URL') . '/sso-passive-logout',
            env('ATTENDANCE_URL') . '/sso-passive-logout',
        ];
        $domain = config('session.domain');

    return redirect()->route('logout.hub', ['urls' => $logoutUrls])
        ->withCookie(Cookie::forget(config('session.cookie'), '/', $domain)) 
        ->withCookie(Cookie::forget('attendance_token', '/', $domain))
        ->withCookie(Cookie::forget('dev_payroll_session', '/', $domain))
        ->withCookie(Cookie::forget('dev_attendance_session', '/', $domain));
    }
}

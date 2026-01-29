<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // Check if SSO is enabled - redirect to SSO workspace URL
        $ssoWorkspaceUrl = env('SSO_WORKSPACE_URL');
        if ($ssoWorkspaceUrl) {
            // Add redirect parameter to tell SSO where to send user back
            $attendanceUrl = env('APP_URL', 'https://attendancedev.isarva.in');
            $redirectUrl = $ssoWorkspaceUrl . '?redirect=' . urlencode($attendanceUrl);
            return redirect()->away($redirectUrl);
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Log successful login
            ActivityLogger::logLogin(Auth::user(), 'email');
            
            return redirect()->route('dashboard');
        }

        // Log failed login attempt
        ActivityLogger::logFailedLogin($credentials['email']);

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        $user = User::updateOrCreate(
            ['email' => $googleUser->email],
            [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'financial_year' => $this->getCurrentFinancialYear(),
            ]
        );

        Auth::login($user);
        
        // Log successful Google login
        ActivityLogger::logLogin($user, 'google');
        
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout before actually logging out
        if ($user) {
            ActivityLogger::logLogout($user);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Check if SSO is enabled - redirect to SSO logout
        $ssoWorkspaceUrl = env('SSO_WORKSPACE_URL');
        if ($ssoWorkspaceUrl) {
            $domain = config('session.domain');
            $cookies = [
                \Illuminate\Support\Facades\Cookie::forget(config('session.cookie'), '/', $domain),
                \Illuminate\Support\Facades\Cookie::forget('attendance_session', '/', $domain),
            ];
            
            return redirect()
                ->away($ssoWorkspaceUrl . '/sso-logout')
                ->withCookies($cookies);
        }
        
        return redirect('/login');
    }

    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }
}
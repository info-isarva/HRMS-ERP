<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

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
        // Validate credentials but do NOT log in yet
        $validated = $request->validated();
        $email = $validated['email'];
        $password = $validated['password'];
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // Check if the user status is 0
        if ($user->status === 0) {
            return back()->withErrors(['email' => 'Your account is inactive. Please contact support.']);
        }

        // If user has enabled 2FA, send code and redirect to verification
        if ($user->{"2fa_enabled"}) {
            $code = random_int(100000, 999999);
            session(['2fa:user:id' => $user->id]);
            session(['2fa:code' => $code]);
            session(['2fa:expires' => now()->addMinutes(10)]);

            // Send code via email
            Mail::raw("Your 2FA code is: $code", function($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Two-Factor Authentication Code');
            });

            // Log login attempt (pre-2FA)
            ActivityLogger::log([
                'type' => 'login',
                'module' => 'User',
                'action' => 'login',
                'user_id' => $user->id,
                'note' => '2FA code sent to email',
            ]);

            // Redirect to 2FA code entry page
            return redirect()->route('auth.2fa.verify');
        } else {
            // Log user in directly
            if ($user->status !== 1) {
                return redirect()->route('login')->withErrors(['email' => 'Your account is inactive.']);
            }

            Auth::login($user);
            ActivityLogger::log([
                'type' => 'login',
                'module' => 'User',
                'action' => 'login',
                'user_id' => $user->id,
                'note' => 'Logged in without 2FA',
            ]);
            // Check compliance consent for admin/superadmin
            if (in_array($user->crm_role_type, [0,1])) {
                $now = now();
                $lastAgreed = $user->compliance_consent_agreed_at;
                $showForm = !$user->compliance_consent_agreed || !$lastAgreed || $now->format('Y-m') !== optional($lastAgreed)->format('Y-m');
                if ($showForm) {
                    return redirect()->route('compliance.consent.form');
                }
            }
            // Always redirect to the dashboard
            return redirect('/dashboard');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Log logout activity
        ActivityLogger::log([
            'type' => 'logout',
            'module' => 'User',
            'action' => 'logout',
            'user_id' => $userId,
        ]);

        return redirect('/');
    }
}

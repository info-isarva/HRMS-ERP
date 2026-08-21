<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->stateless()->user();

        // Check if user exists by email
        $user = User::where('email', $googleUser->getEmail())->first();
        if (!$user) {
            // If not found, redirect back with error
            return redirect('/login')->withErrors(['email' => 'Your email is not registered. Please contact admin.']);
        }

        // Optionally update Google ID and avatar
        $user->update([
            'google_id' => $googleUser->getId(),
            // 'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        // 2FA check
        if ($user->{"2fa_enabled"}) {
            $code = random_int(100000, 999999);
            session(['2fa:user:id' => $user->id]);
            session(['2fa:code' => $code]);
            session(['2fa:expires' => now()->addMinutes(10)]);
            \Mail::raw("Your 2FA code is: $code", function($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Two-Factor Authentication Code');
            });
            // Log login attempt (pre-2FA)
            \App\Helpers\ActivityLogger::log([
                'type' => 'login',
                'module' => 'User',
                'action' => 'login',
                'user_id' => $user->id,
                'note' => '2FA code sent to email (Google login)',
            ]);
            // Do not log in yet, redirect to 2FA code entry page
            return redirect()->route('auth.2fa.verify');
        } else {
            if ($user->status === 0) {
                return redirect()->route('login')->withErrors(['email' => 'Your account is inactive.']);
            }

            Auth::login($user);
            \App\Helpers\ActivityLogger::log([
                'type' => 'login',
                'module' => 'User',
                'action' => 'login',
                'user_id' => $user->id,
                'note' => 'Logged in without 2FA (Google login)',
            ]);
            return redirect('/dashboard');
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                ]);
            }

            Auth::login($user);

            // Generate JWT Token for SSO
            $token = JWTAuth::customClaims([
                'exp' => now()->addMinutes(5)->timestamp,
                'jti' => Str::uuid(),
                'user' => [
                    'id' => Auth::id(),
                    'email' => Auth::user()->email,
                    'hmac' => hash_hmac('sha256', Auth::id().Auth::user()->email, env('JWT_HMAC_SECRET'))
                ]
            ])->fromUser(Auth::user());
    
            session(['payroll_token' => $token, 'attendance_token' => $token]);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to login with Google.']);
        }
    }
}

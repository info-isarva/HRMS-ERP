<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\ActivityLogService;

class SSOAuthController extends Controller
{
    public function authenticate(Request $request)
    {

        $request->validate(['token' => 'required|string']);

        try {
            $token = $request->query('token');
            $payload = JWTAuth::setToken($token)->getPayload();
            // Verify token expiration
            if (now()->timestamp > $payload->get('exp')) {
                throw new \Exception('Token expired');
            }

            $userData = $payload->get('user');

            // Verify HMAC
            $expectedHmac = hash_hmac(
                'sha256',
                $userData['id'] . $userData['email'],
                env('JWT_HMAC_SECRET')
            );

            if (!hash_equals($expectedHmac, $userData['hmac'])) {
                throw new \Exception('Invalid token signature');
            }

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['email'],
                    'password' => Hash::make(Str::random(16)),
                    'status' => 'Active',
                    // Add other required fields
                ]
            );

            // Verify user is active
            if ($user->status !== 'Active') {
                throw new \Exception('User account is inactive');
            }

            Auth::login($user, true);
            //$request->session()->regenerate();
            // Set session data
            Session::put($this->prepareUserSessionData($user));

            // Update last login
            $user->update(['last_login' => now()]);

            // Log successful SSO login with detailed information
            ActivityLogService::logLogin($user->user_id, $user->name, $user->email, [
                'login_method' => 'SSO Login'
            ]);

            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            Log::error('SSO Authentication Failed: ' . $e->getMessage());
            
            // Log failed SSO login attempt
            ActivityLogService::logFailedLogin($userData['email'] ?? 'unknown', 'SSO failed: ' . $e->getMessage());
            
            return redirect()->route('login')->withErrors([
                'sso' => 'SSO failed: ' . $e->getMessage()
            ]);
        }
    }

    /** Prepare User Session Data */
    private function prepareUserSessionData($user)
    {
        return [
            'name'                => $user->name,
            'email'               => $user->email,
            'user_id'             => $user->user_id,
            'join_date'           => $user->join_date,
            'phone_number'        => $user->phone_number,
            'status'              => $user->status,
            'role_name'           => $user->role_name,
            'avatar'              => $user->avatar,
            'position'            => $user->position,
            'department'          => $user->department,
            'line_manager'        => $user->line_manager,
            'second_line_manager' => $user->second_line_manager,
        ];
    }
}

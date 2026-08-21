<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle a login request to the application (Mobile API).
     */
    public function login(Request $request)
    {
        // If Google token is present, use Google login
        if ($request->has('google_token')) {
            try {
                $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->google_token);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token',
                ], 401);
            }

            $user = User::where('email', $googleUser->getEmail())->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your email is not registered. Please contact admin.',
                ], 404);
            }

            $user->update([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful (Google)',
                'token' => $token,
                'user' => $user,
            ]);
        }

        // Otherwise, use email/password login
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
}

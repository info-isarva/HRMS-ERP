<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtHmac
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$request->has('token')) {
                throw new \Exception('Missing authentication token');
            }

            $token = $request->input('token');
            $payload = JWTAuth::setToken($token)->getPayload();
            $userData = $payload->get('user');

            // Verify HMAC signature
            $expectedHmac = hash_hmac(
                'sha256',
                $userData['id'] . $userData['email'],
                env('JWT_HMAC_SECRET')
            );

            if (!hash_equals($expectedHmac, $userData['hmac'])) {
                throw new \Exception('Invalid token signature');
            }

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'SSO Authentication Failed: ' . $e->getMessage()
            ], 401);
        }
    }
}

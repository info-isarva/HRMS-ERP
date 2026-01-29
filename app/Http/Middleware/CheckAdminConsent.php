<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckAdminConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is Admin or Super Admin
            $isAdmin = in_array($user->role_name, ['Admin', 'Super Admin']);

            if ($isAdmin) {
                // Check if consent is needed for this month
                $lastConsent = $user->last_consent_date; // cast to datetime in model
                $startOfMonth = Carbon::now()->startOfMonth();

                // If never consented or consented before this month
                if (is_null($lastConsent) || $lastConsent->lt($startOfMonth)) {
                    
                    // Allow access to the consent route itself and logout to prevent infinite loop
                    if (!$request->routeIs('admin.consent.show') && 
                        !$request->routeIs('admin.consent.store') && 
                        !$request->routeIs('logout') &&
                        !$request->is('logout') &&
                        !$request->routeIs('sso.logout')) {
                        
                        return redirect()->route('admin.consent.show');
                    }
                }
            }
        }

        return $next($request);
    }
}

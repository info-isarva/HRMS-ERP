<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            // For AJAX requests, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Unauthorized access. You do not have permission to perform this action.',
                    'permission_required' => $permission
                ], 403);
            }
            
            // For regular requests, show 403 error page
            abort(403, 'You are not authorized to access this resource. Required permission: ' . $permission);
        }

        return $next($request);
    }
}

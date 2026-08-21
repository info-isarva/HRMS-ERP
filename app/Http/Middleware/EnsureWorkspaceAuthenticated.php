<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('redirect')) {
            session(['sso_redirect_after_login' => $request->query('redirect')]);
        }

        if (! Auth::check()) {
            return redirect()->route('login', array_filter(['redirect' => $request->query('redirect')]));
        }

        return $next($request);
    }
}

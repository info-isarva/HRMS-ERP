<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class Email2FAController extends Controller
{
    public function showForm(Request $request)
    {
        // Only show if 2FA session exists
        if (!Session::has('2fa:code')) {
            return redirect()->route('login');
        }
        return view('auth.2fa-email');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);
        $code = Session::get('2fa:code');
        $expires = Session::get('2fa:expires');
        if (!$code || now()->gt($expires)) {
            return back()->withErrors(['code' => 'The code has expired. Please login again.']);
        }
        if ($request->input('code') != $code) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }
        // 2FA success: log the user in and clear session
        $userId = Session::get('2fa:user:id');
        $user = \App\Models\User::find($userId);
        if ($user && $user->status === '0') {
            return redirect()->route('login')->withErrors(['email' => 'Your account is inactive.']);
        }

        Auth::login($user);
        Session::forget(['2fa:code', '2fa:expires', '2fa:user:id']);
        Session::put('2fa:passed', true);
        return redirect()->intended(route('dashboard'));
    }
}

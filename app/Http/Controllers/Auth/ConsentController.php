<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ConsentController extends Controller
{
    /**
     * Display the consent form.
     */
    public function show()
    {
        return view('auth.consent');
    }

    /**
     * Handle the consent submission.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $user->last_consent_date = Carbon::now();
        $user->save();

        return redirect()->route('dashboard');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (! array_key_exists($locale, config('posh.locales'))) {
            abort(404);
        }
        session(['posh_locale' => $locale]);

        return back();
    }
}

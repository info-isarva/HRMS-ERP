<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * HRMS hub placeholder for the POSH product module (Phase 1+).
 */
class PoshModuleController extends Controller
{
    public function comingSoon(): View
    {
        return view('posh.coming-soon', [
            'legacyEnabled' => (bool) config('posh.legacy_enabled'),
            'showPrototypeLink' => (bool) config('posh.show_prototype_link'),
            'prototypeUrl' => config('posh.prototype_url'),
        ]);
    }
}

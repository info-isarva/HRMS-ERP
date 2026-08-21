<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $org = Organization::findOrFail($request->user()->organization_id);

        if (! $org->intake_key) {
            $org->update(['intake_key' => Str::lower(Str::random(12))]);
            $org->refresh();
        }

        $intakeUrl = route('intake.show', $org->intake_key);

        return view('settings.organization', compact('org', 'intakeUrl'));
    }

    public function update(Request $request)
    {
        $org = Organization::findOrFail($request->user()->organization_id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_count' => 'nullable|integer|min:1',
            'locale_default' => 'nullable|in:en,hi',
            'whatsapp_number' => 'nullable|string|max:20',
            'deployment_mode' => 'nullable|in:erp,standalone',
        ]);

        if (! empty($validated['deployment_mode'])) {
            $mode = config('posh.deployment_modes.' . $validated['deployment_mode']);
            $org->update([
                'employee_source' => $mode['employee_source'],
                'auth_mode' => $mode['auth_mode'],
            ]);
            $sync = app(\App\Services\PoshPayrollSyncService::class);
            if ($mode['employee_source'] === 'payroll') {
                $sync->sync($org);
            } else {
                $sync->seedStandaloneEmployees($org);
            }
            unset($validated['deployment_mode']);
        }

        $settings = array_merge($org->settings ?? [], [
            'locale_default' => $validated['locale_default'] ?? 'en',
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
        ]);
        $org->update([
            'name' => $validated['name'],
            'employee_count' => $validated['employee_count'] ?? $org->employee_count,
            'settings' => $settings,
        ]);
        if (! $org->intake_key) {
            $org->update(['intake_key' => Str::lower(Str::random(12))]);
        }

        return back()->with('success', 'Organization settings saved.');
    }

    public function regenerateIntakeKey(Request $request)
    {
        $org = Organization::findOrFail($request->user()->organization_id);
        $org->update(['intake_key' => Str::lower(Str::random(12))]);

        return back()->with('success', 'QR intake link regenerated.');
    }
}

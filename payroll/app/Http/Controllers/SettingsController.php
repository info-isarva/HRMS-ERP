<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display a listing of the settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Group settings by their group for easier display
        $settingGroups = Setting::orderBy('group')
            ->orderBy('display_order')
            ->get()
            ->groupBy('group');
        
        return view('settings.master-settings', compact('settingGroups'));
    }

    /**
     * Update settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        // Get all boolean settings to handle unchecked checkboxes
        $booleanSettings = Setting::where('type', 'boolean')->pluck('key');
        
        // Handle boolean settings - if not present in request, it means they were unchecked
        foreach ($booleanSettings as $key) {
            if (!array_key_exists($key, $settings)) {
                // Checkbox was unchecked, set to false
                Setting::setValue($key, false);
            }
        }
        
        // Handle all submitted settings
        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }
        
        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}

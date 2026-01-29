<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmailSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    /**
     * Display email settings.
     */
    public function index()
    {
        // Get current email settings from database
        $settings = DB::table('email_settings')->first();

        // If no settings exist, create default settings
        if (!$settings) {
            $settings = $this->createDefaultSettings();
        }

        return view('email-settings.index', compact('settings'));
    }

    /**
     * Update email settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'emails_enabled' => 'boolean',
        ]);

        DB::table('email_settings')->updateOrInsert(
            ['id' => 1], // Assuming single row settings
            [
                'emails_enabled' => $request->boolean('emails_enabled'),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Email settings updated successfully!');
    }

    /**
     * Create default email settings.
     */
    private function createDefaultSettings()
    {
        $defaultSettings = [
            'id' => 1,
            'emails_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('email_settings')->insert($defaultSettings);

        return (object) $defaultSettings;
    }

    /**
     * Check if emails are enabled globally.
     */
    public static function areEmailsEnabled()
    {
        $settings = DB::table('email_settings')->first();

        if (!$settings) {
            return true; // Default to enabled if no settings exist
        }

        return $settings->emails_enabled;
    }
}
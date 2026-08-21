<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function application()
    {
        $user = auth()->user();
        // Adjust this check to match your admin logic (e.g., role type or permission)
        // if (!$user || ($user->crm_role_type ?? null) !== '0') {
        //     abort(403, 'Unauthorized. Admins only.');
        // }
        $logPath = storage_path('logs/laravel.log');
        $logs = File::exists($logPath) ? File::get($logPath) : 'No log file found.';
        // Optionally, limit log size for display
        if (strlen($logs) > 100000) {
            $logs = substr($logs, -100000); // Show last 100KB
        }
        return view('logs.application', compact('logs'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Models\Employee;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page
     */
    public function show()
    {
        $user = Auth::user();
        
        // Get employee details if available
        $employee = Employee::where('email', $user->email)->first();
        
        return view('profile.show', compact('user', 'employee'));
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.letters' => 'The password must contain at least one letter.',
        ]);

        $user = Auth::user();

        try {
            // Update the password in attendance system
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            Log::info('Password updated successfully in attendance system', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Sync password to payroll system
            $this->syncPasswordToPayroll($user->email, $request->password);

            return redirect()->route('profile.show')->with('success', 'Password updated successfully and synced across systems!');

        } catch (\Exception $e) {
            Log::error('Failed to update password', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('profile.show')->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Sync password change to payroll system
     */
    private function syncPasswordToPayroll($userEmail, $newPassword)
    {
        try {
            $payrollUrl = env('PAYROLL_SYNC_URL', 'https://payrolldev.isarva.in');
            $syncToken = env('PAYROLL_SYNC_TOKEN', 'default-token');
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($payrollUrl . '/api/sync/password/from-attendance', [
                'user_email' => $userEmail,
                'new_password' => $newPassword,
                'sync_token' => $syncToken,
                'synced_from' => 'attendance',
                'synced_at' => now()->toISOString()
            ]);

            if ($response->successful()) {
                Log::info('Password successfully synced to payroll system', [
                    'user_email' => $userEmail,
                    'response' => $response->json()
                ]);
            } else {
                Log::warning('Failed to sync password to payroll system', [
                    'user_email' => $userEmail,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing password to payroll system', [
                'user_email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
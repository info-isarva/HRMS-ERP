<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserConsent;
use App\Models\EmployeeBasicDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    /**
     * Show the DPDP policy consent page
     */
    public function showDpdpPolicy(Request $request)
    {
        // If they are already logged in and accepted, send them home
        if (Auth::check()) {
            $hasAccepted = Auth::user()->consents()
                ->where('policy_type', 'dpdp_act')
                ->where('is_accepted', true)
                ->exists();
                
            if ($hasAccepted) {
                return redirect()->route('home');
            }
        }
        
        // If they are not logged in and don't have a pending consent session, send to login
        if (!Auth::check() && !session()->has('pending_dpdp_user_id')) {
            return redirect('login');
        }
        
        return view('compliance.dpdp-policy');
    }

    /**
     * Process the DPDP policy acceptance or rejection
     */
    public function acceptDpdpPolicy(Request $request)
    {
        if ($request->has('reject')) {
            session()->forget('pending_dpdp_user_id');
            return redirect('login')->with('error', 'You have rejected the policy. Unable to login.');
        }

        $request->validate([
            'accept_terms' => 'required',
        ]);

        $userId = session('pending_dpdp_user_id') ?? Auth::id();

        if (!$userId) {
            return redirect('login');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            session()->forget('pending_dpdp_user_id');
            return redirect('login');
        }

        // Record the consent
        UserConsent::create([
            'user_id' => $user->id,
            'policy_type' => 'dpdp_act',
            'is_accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => Carbon::now(),
        ]);

        // If they were in the pre-login flow, log them in now
        if (session()->has('pending_dpdp_user_id')) {
            Auth::login($user);
            
            // Set up standard session data for the app
            $sessionData = [
                'name'                => $user->name,
                'email'               => $user->email,
                'user_id'             => $user->user_id,
                'join_date'           => $user->join_date,
                'phone_number'        => $user->phone_number,
                'status'              => $user->status,
                'role_name'           => $user->role_name,
                'avatar'              => $user->avatar,
                'position'            => $user->position,
                'department'          => $user->department,
                'line_manager'        => $user->line_manager,
                'second_line_manager' => $user->second_line_manager,
            ];
            session()->put($sessionData);
            
            // Update last login
            $user->update(['last_login' => Carbon::now()]);
            
            // Log successful login with detailed information
            \App\Services\ActivityLogService::logLogin($user->user_id, $user->name, $user->email, [
                'login_method' => 'Standard Login (Post-Consent)'
            ]);

            session()->forget('pending_dpdp_user_id');
        }

        // Redirect back to intended page or dashboard
        return redirect()->intended('home')->with('success', 'Thank you for accepting the Privacy Policy.');
    }

    /**
     * Show the DPDP data dashboard for the user
     */
    public function dpdpDashboard()
    {
        $user = Auth::user();
        $consent = $user->consents()->where('policy_type', 'dpdp_act')->where('is_accepted', true)->latest()->first();
        
        return view('compliance.dpdp-dashboard', compact('user', 'consent'));
    }

    /**
     * Handle user request to export their data
     */
    public function requestDataExport(Request $request)
    {
        // In a real application, you would dispatch a job here to compile the data
        // For example: dispatch(new ExportUserDataJob(Auth::user()));
        
        return back()->with('success', 'Your data export request has been received. You will receive an email with a download link shortly.');
    }

    /**
     * Tracking Dashboard for DPDP Policy Distribution
     */
    public function policyDistribution(Request $request)
    {
        // Access control check
        $user = Auth::user();
        if ($user->role_name !== 'Super Admin' && $user->role_name !== 'Admin') {
            abort(403, 'Access Denied: Only Administrators are authorized to access this secure portal.');
        }

        // Get all active employees
        $activeEmployeesQuery = EmployeeBasicDetail::active()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $activeEmployeesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('employee_id', 'like', '%' . $search . '%');
            });
        }

        $allEmployees = $activeEmployeesQuery->get();

        // Get DPDP consents
        $consents = UserConsent::where('policy_type', 'dpdp_act')
            ->where('is_accepted', true)
            ->get()
            ->keyBy('user_id');

        $data = $allEmployees->map(function($employee) use ($consents) {
            $user = $employee->user;
            $consent = $user ? $consents->get($user->id) : null;
            return (object) [
                'user_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'status' => $consent ? 'Acknowledged' : 'Pending',
                'accepted_at' => $consent ? $consent->accepted_at : null,
                'ip_address' => $consent ? $consent->ip_address : null,
            ];
        });

        if ($request->filled('status')) {
            $status = $request->status;
            $data = $data->filter(function($item) use ($status) {
                return $item->status === $status;
            });
        }

        // Calculate statistics
        $totalEmployees = $allEmployees->count();
        $acknowledgedCount = $data->filter(function($item) { return $item->status === 'Acknowledged'; })->count();
        $pendingCount = $totalEmployees - $acknowledgedCount;

        $filters = $request->all();

        return view('compliance.dpdp.policy_distribution', compact('data', 'totalEmployees', 'acknowledgedCount', 'pendingCount', 'filters'));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Auth;
use DB;
use Illuminate\Support\Facades\Cookie;
use App\Services\ActivityLogService;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'locked', 'unlock', 'loginHelp']);
    }
    
    /** Display login help page */
    public function loginHelp()
    {
        return view('auth.login_help');
    }

    /** Display the login page */
    public function login()
    {
        $baseUrl = env('SSO_WORKSPACE_URL');
        
        // Add fallback and error handling
        if (empty($baseUrl)) {
            \Log::error('SSO_WORKSPACE_URL is not set or empty in environment variables');
            
            // Fallback to a default URL or show error
            $baseUrl = 'https://hrmsdev.isarva.in'; // fallback URL
            
            // Or you could return to login form instead:
            // flash()->error('SSO configuration error. Please contact administrator.');
            // return view('auth.login');
        }
        
        return redirect()->away($baseUrl);
        // return view('auth.login');
    }

    /** Authenticate user and redirect */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email or Employee ID is required',
        ]);

        try {
            // Check if input is email or employee ID
            $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_id';
            
            // Set credentials based on the determined field
            $credentials = [
                $loginField => $request->email,
                'password' => $request->password,
                'status' => 'Active'
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Check Notice Period Restriction
                if (\App\Models\Setting::getValue('restrict_portal_on_notice', false)) {
                     $employee = \App\Models\EmployeeBasicDetail::where('employee_id', $user->user_id)->first();
                     if ($employee && $employee->date_of_resignation) {
                         Auth::logout();
                         flash()->error('Login restricted during notice period.');
                         return redirect('login');
                     }
                }

                Session::put($this->getUserSessionData($user));

                // Update last login
                $user->update(['last_login' => Carbon::now()]);
                
                // Log successful login with detailed information
                ActivityLogService::logLogin($user->user_id, $user->name, $user->email, [
                    'login_method' => 'Standard Login'
                ]);

                flash()->success('Login successfully :)');
                return redirect()->intended('home');
            }

            // Log failed login attempt
            ActivityLogService::logFailedLogin($request->email, 'Invalid credentials');

            flash()->error('Wrong Username or Password');
            return redirect('login');
        } catch (\Exception $e) {
            \Log::info($e);
            
            // Log failed login attempt due to exception
            ActivityLogService::logFailedLogin($request->email, 'System error: ' . $e->getMessage());
            
            flash()->error('Login failed. Please try again.');
            return redirect()->back();
        }
    }

    /** Prepare User Session Data */
    private function getUserSessionData($user)
    {
        return [
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
    }

    /** Logout and clear session */
    public function logout(Request $request)
    {
        // Log logout before clearing session
        $user = Auth::user();
        if ($user) {
            ActivityLogService::logLogout($user->user_id, $user->name);
        }
        
        $request->session()->flush();
        Auth::logout();
        //flash()->success('Logout successfully :)');
        // return redirect('login');
        $domain = config('session.domain');
        $cookies = [
            Cookie::forget(config('session.cookie'), '/', $domain), // Main session
            Cookie::forget('dri_timesheet_session', '/', $domain),    
        ];
    	return redirect()
        ->away(env('SSO_WORKSPACE_URL') . '/sso-logout')
        ->withCookies($cookies);

    }
    public function ssoPassiveLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
      	$domain = config('session.domain');
        $cookies = [
            Cookie::forget(config('session.cookie'), '/', $domain), // Main session
            Cookie::forget('dri_timesheet_session', '/', $domain),     
        ];
    	return response()->make('', 200)
        ->withCookies($cookies);
    }
}
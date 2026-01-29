<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $payrollApiUrl;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->payrollApiUrl = env('PAYROLL_API_BASE_URL', env('PAYROLL_API_URL', 'https://payrolldev.isarva.in/api'));
    }
    
    /**
     * Get all notifications from payroll system
     */
    public function getNotifications()
    {
        try {
            $user = Auth::user();
            
            // Fetch notifications from payroll system API
            $response = Http::timeout(10)->get($this->payrollApiUrl . '/notifications/user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'employee_id' => $user->employee_id ?? null,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'notifications' => $data['data']['notifications'] ?? [],
                    'unread_count' => $data['data']['unread_count'] ?? 0,
                ]);
            }
            
            Log::warning('Failed to fetch notifications from payroll API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            // Fallback to empty notifications if API fails
            return response()->json([
                'success' => true,
                'notifications' => [],
                'unread_count' => 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->input('notification_id');
            $user = Auth::user();
            
            // Call payroll API to mark as read
            $response = Http::timeout(10)->post($this->payrollApiUrl . '/notifications/mark-read', [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'employee_id' => $user->employee_id ?? null,
                'email' => $user->email,
            ]);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read',
                ]);
            }
            
            Log::warning('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'status' => $response->status()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            // Call payroll API to mark all as read
            $response = Http::timeout(10)->post($this->payrollApiUrl . '/notifications/mark-all-read', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id ?? null,
                'email' => $user->email,
            ]);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'All notifications marked as read',
                ]);
            }
            
            Log::warning('Failed to mark all notifications as read', [
                'status' => $response->status()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * View all notifications page
     */
    public function viewAll()
    {
        try {
            $user = Auth::user();
            
            // Fetch notifications from payroll system API
            $response = Http::timeout(10)->get($this->payrollApiUrl . '/notifications/user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'employee_id' => $user->employee_id ?? null,
            ]);
            
            $notifications = [];
            
            if ($response->successful()) {
                $data = $response->json();
                $notifications = $data['data']['notifications'] ?? [];
            } else {
                Log::warning('Failed to fetch notifications for all page', [
                    'status' => $response->status()
                ]);
            }
            
            return view('notifications.all', compact('notifications'));
            
        } catch (\Exception $e) {
            Log::error('Error fetching all notifications: ' . $e->getMessage());
            return view('notifications.all', ['notifications' => []]);
        }
    }
    
    /**
     * Show detailed notification view
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            
            // For manual notifications, fetch from payroll API
            if (strpos($id, 'manual_') === 0) {
                $manualId = str_replace('manual_', '', $id);
                
                $response = Http::timeout(10)->get($this->payrollApiUrl . '/notifications/user', [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id ?? null,
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $notifications = $data['data']['notifications'] ?? [];
                    
                    // Find the specific notification
                    $notification = collect($notifications)->firstWhere('id', $id);
                    
                    if ($notification) {
                        return view('notifications.show', ['notification' => $notification]);
                    }
                }
            }
            
            return redirect()->route('notifications.all')->with('error', 'Notification not found');
            
        } catch (\Exception $e) {
            Log::error('Error fetching notification detail: ' . $e->getMessage());
            return redirect()->route('notifications.all')->with('error', 'Failed to load notification');
        }
    }
    
    /**
     * Get payroll system token for API authentication
     * (No longer needed since API is public, but keeping for future use)
     */
    protected function getPayrollToken()
    {
        return env('PAYROLL_API_TOKEN', '');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManualNotification;
use App\Models\NotificationRead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationApiController extends Controller
{
    /**
     * Get notifications for a specific user (for attendance system)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNotifications(Request $request)
    {
        try {
            $userId = $request->input('user_id') ?: Auth::id();
            $employeeId = $request->input('employee_id');
            $userEmail = $request->input('email');
            
            if (!$userId && !$employeeId && !$userEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID, Employee ID, or Email is required'
                ], 400);
            }
            
            // If employee_id is provided, try to find user by it first (more reliable for cross-system)
            if ($employeeId) {
                // First try as employee table ID (integer)
                $user = User::where('employee_id', $employeeId)->first();
                
                // If not found, try to find employee by employee_id string and get user
                if (!$user) {
                    $employee = DB::table('employee_basic_details')
                        ->where('employee_id', $employeeId)
                        ->first();
                    
                    if ($employee) {
                        $user = User::where('employee_id', $employee->id)->first();
                    }
                }
                
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // If still not found and email is provided, find by email
            if (!$userId && $userEmail) {
                $user = User::where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // Final check
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with provided credentials'
                ], 404);
            }
            
            // Get active manual notifications for the user
            $manualNotifications = ManualNotification::active()
                ->forUser($userId)
                ->where('show_in_header', true)
                ->with('reads')
                ->orderBy('priority', 'desc') // High priority first
                ->orderBy('start_date', 'desc')
                ->get();
            
            // Get system-generated notifications (birthdays, etc.)
            $systemNotifications = $this->getSystemNotifications($userId);
            
            // Format manual notifications
            $formattedManualNotifications = $manualNotifications->map(function ($notification) use ($userId) {
                return [
                    'id' => 'manual_' . $notification->id,
                    'type' => 'manual',
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'created_at' => $notification->start_date->toISOString(),
                    'is_read' => $notification->isReadBy($userId),
                    'action_url' => null,
                    'recurrence_type' => $notification->recurrence_type,
                    'end_date' => $notification->end_date?->toISOString(),
                    'send_email' => $notification->send_email,
                    'send_sms' => $notification->send_sms
                ];
            });
            
            // Combine both types of notifications
            $allNotifications = collect($formattedManualNotifications)
                ->concat($systemNotifications)
                ->sortByDesc(function ($notification) {
                    // Sort by priority (high -> medium -> low) then by date
                    $priorityWeight = ['high' => 3, 'medium' => 2, 'low' => 1];
                    return ($priorityWeight[$notification['priority']] ?? 0) * 1000000 + 
                           strtotime($notification['created_at']);
                })
                ->values();
            
            $unreadCount = $allNotifications->where('is_read', false)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $allNotifications,
                    'unread_count' => $unreadCount,
                    'total_count' => $allNotifications->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark a notification as read
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->input('notification_id');
            $userId = $request->input('user_id') ?: Auth::id();
            $employeeId = $request->input('employee_id');
            $userEmail = $request->input('email');
            
            if (!$userId && !$employeeId && !$userEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID, Employee ID, or Email is required'
                ], 400);
            }
            
            // Find user by employee_id or email (prioritize for cross-system compatibility)
            if ($employeeId) {
                // First try as employee table ID (integer)
                $user = User::where('employee_id', $employeeId)->first();
                
                // If not found, try to find employee by employee_id string and get user
                if (!$user) {
                    $employee = DB::table('employee_basic_details')
                        ->where('employee_id', $employeeId)
                        ->first();
                    
                    if ($employee) {
                        $user = User::where('employee_id', $employee->id)->first();
                    }
                }
                
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // If still not found and email is provided, find by email
            if (!$userId && $userEmail) {
                $user = User::where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            if (str_starts_with($notificationId, 'manual_')) {
                // Handle manual notification
                $manualNotificationId = str_replace('manual_', '', $notificationId);
                $notification = ManualNotification::find($manualNotificationId);
                
                if (!$notification) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notification not found'
                    ], 404);
                }
                
                $notification->markAsReadBy($userId);
            } else {
                // Handle system-generated notification (store in database for cross-system sync)
                DB::table('notification_reads')->updateOrInsert(
                    [
                        'notification_id' => $notificationId,
                        'user_id' => $userId
                    ],
                    [
                        'read_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'created_at' => Carbon::now()
                    ]
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $userId = $request->input('user_id') ?: Auth::id();
            $employeeId = $request->input('employee_id');
            $userEmail = $request->input('email');
            
            if (!$userId && !$employeeId && !$userEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID, Employee ID, or Email is required'
                ], 400);
            }
            
            // Find user by employee_id or email (prioritize for cross-system compatibility)
            if ($employeeId) {
                // First try as employee table ID (integer)
                $user = User::where('employee_id', $employeeId)->first();
                
                // If not found, try to find employee by employee_id string and get user
                if (!$user) {
                    $employee = DB::table('employee_basic_details')
                        ->where('employee_id', $employeeId)
                        ->first();
                    
                    if ($employee) {
                        $user = User::where('employee_id', $employee->id)->first();
                    }
                }
                
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // If still not found and email is provided, find by email
            if (!$userId && $userEmail) {
                $user = User::where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $markedCount = 0;
            
            // Mark all manual notifications as read
            $manualNotifications = ManualNotification::active()
                ->forUser($userId)
                ->where('show_in_header', true)
                ->get();
                
            foreach ($manualNotifications as $notification) {
                if (!$notification->isReadBy($userId)) {
                    $notification->markAsReadBy($userId);
                    $markedCount++;
                }
            }
            
            // Mark system notifications as read (store in database for cross-system sync)
            $systemNotifications = $this->getSystemNotifications($userId);
            $systemNotificationIds = $systemNotifications->pluck('id')->toArray();
            
            if (!empty($systemNotificationIds)) {
                $now = Carbon::now();
                $records = [];
                
                foreach ($systemNotificationIds as $notificationId) {
                    $records[] = [
                        'notification_id' => $notificationId,
                        'user_id' => $userId,
                        'read_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
                
                // Use upsert to avoid duplicates
                DB::table('notification_reads')->upsert(
                    $records,
                    ['notification_id', 'user_id'],
                    ['read_at', 'updated_at']
                );
            }
            
            $markedCount += $systemNotifications->where('is_read', false)->count();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'marked_count' => $markedCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get notification statistics for a user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            $userId = $request->input('user_id') ?: Auth::id();
            $employeeId = $request->input('employee_id');
            $userEmail = $request->input('email');
            
            if (!$userId && !$employeeId && !$userEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID, Employee ID, or Email is required'
                ], 400);
            }
            
            // Find user by employee_id or email (prioritize for cross-system compatibility)
            if ($employeeId) {
                // First try as employee table ID (integer)
                $user = User::where('employee_id', $employeeId)->first();
                
                // If not found, try to find employee by employee_id string and get user
                if (!$user) {
                    $employee = DB::table('employee_basic_details')
                        ->where('employee_id', $employeeId)
                        ->first();
                    
                    if ($employee) {
                        $user = User::where('employee_id', $employee->id)->first();
                    }
                }
                
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            // If still not found and email is provided, find by email
            if (!$userId && $userEmail) {
                $user = User::where('email', $userEmail)->first();
                if ($user) {
                    $userId = $user->id;
                }
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Get manual notification stats
            $manualNotifications = ManualNotification::active()
                ->forUser($userId)
                ->where('show_in_header', true)
                ->with('reads')
                ->get();
            
            $manualStats = [
                'total' => $manualNotifications->count(),
                'unread' => $manualNotifications->filter(fn($n) => !$n->isReadBy($userId))->count(),
                'by_priority' => [
                    'high' => $manualNotifications->where('priority', 'high')->count(),
                    'medium' => $manualNotifications->where('priority', 'medium')->count(),
                    'low' => $manualNotifications->where('priority', 'low')->count(),
                ]
            ];
            
            // Get system notification stats
            $systemNotifications = $this->getSystemNotifications($userId);
            $systemStats = [
                'total' => $systemNotifications->count(),
                'unread' => $systemNotifications->where('is_read', false)->count(),
                'by_type' => $systemNotifications->groupBy('type')->map->count()
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'manual_notifications' => $manualStats,
                    'system_notifications' => $systemStats,
                    'total_unread' => $manualStats['unread'] + $systemStats['unread']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get system-generated notifications (birthdays, payroll, etc.)
     * This reuses the logic from the existing NotificationController
     */
    private function getSystemNotifications($userId)
    {
        $notifications = [];
        
        // Get birthday notifications
        $birthdays = $this->getBirthdayNotifications();
        $notifications = array_merge($notifications, $birthdays);
        
        // Get new employee joining notifications
        $newJoinings = $this->getNewJoiningNotifications();
        $notifications = array_merge($notifications, $newJoinings);
        
        // Get employee exit notifications
        $employeeExits = $this->getEmployeeExitNotifications();
        $notifications = array_merge($notifications, $employeeExits);
        
        // Get payroll processing status notifications
        $payrollStatus = $this->getPayrollStatusNotifications();
        $notifications = array_merge($notifications, $payrollStatus);
        
        // Check read status from database (not session for cross-system compatibility)
        $systemNotificationIds = array_column($notifications, 'id');
        
        if (!empty($systemNotificationIds)) {
            $readNotificationIds = DB::table('notification_reads')
                ->where('user_id', $userId)
                ->whereIn('notification_id', $systemNotificationIds)
                ->pluck('notification_id')
                ->toArray();
            
            // Update read status for notifications
            foreach ($notifications as &$notification) {
                $notification['is_read'] = in_array($notification['id'], $readNotificationIds);
            }
        }
        
        return collect($notifications);
    }
    
    /**
     * Copy methods from NotificationController for system notifications
     */
    private function getBirthdayNotifications()
    {
        // Copy the birthday notification logic from NotificationController
        $notifications = [];
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);
        
        $employees = DB::table('employee_basic_details')
            ->select('id', 'name', 'employee_id', 'date_of_birth', 'profile_image')
            ->whereNotNull('date_of_birth')
            ->where('status', '!=', 3)
            ->get();
        
        foreach ($employees as $employee) {
            if (!$employee->date_of_birth) continue;
            
            $dob = Carbon::parse($employee->date_of_birth);
            $birthdayThisYear = Carbon::create($today->year, $dob->month, $dob->day);
            
            if ($birthdayThisYear->isToday()) {
                $notifications[] = [
                    'id' => 'birthday_' . $employee->id . '_' . $today->format('Y-m-d'),
                    'type' => 'birthday_today',
                    'icon' => 'fa-birthday-cake',
                    'color' => 'primary',
                    'priority' => 'medium',
                    'title' => '🎉 Birthday Today!',
                    'message' => "{$employee->name} is celebrating their birthday today!",
                    'employee_name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'profile_image' => $employee->profile_image,
                    'created_at' => $today->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => null
                ];
            } elseif ($birthdayThisYear->between($today->copy()->addDay(), $nextWeek)) {
                $daysUntil = $today->diffInDays($birthdayThisYear);
                $notifications[] = [
                    'id' => 'birthday_upcoming_' . $employee->id . '_' . $birthdayThisYear->format('Y-m-d'),
                    'type' => 'birthday_upcoming',
                    'icon' => 'fa-calendar-day',
                    'color' => 'info',
                    'priority' => 'low',
                    'title' => 'Upcoming Birthday',
                    'message' => "{$employee->name}'s birthday is in {$daysUntil} days ({$birthdayThisYear->format('M d')})",
                    'employee_name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'profile_image' => $employee->profile_image,
                    'created_at' => $birthdayThisYear->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => null
                ];
            }
        }
        
        return $notifications;
    }
    
    private function getNewJoiningNotifications()
    {
        // Copy logic from NotificationController
        $notifications = [];
        $lastWeek = Carbon::now()->subDays(7);
        
        $newEmployees = DB::table('employee_basic_details')
            ->select('id', 'name', 'employee_id', 'date_of_joining', 'profile_image', 'department')
            ->where('date_of_joining', '>=', $lastWeek)
            ->where('status', '!=', 3)
            ->orderBy('date_of_joining', 'desc')
            ->get();
        
        foreach ($newEmployees as $employee) {
            $joiningDate = Carbon::parse($employee->date_of_joining);
            $now = Carbon::now();
            
            if ($joiningDate->isToday()) {
                $timeText = 'today';
            } elseif ($joiningDate->isYesterday()) {
                $timeText = 'yesterday';
            } else {
                $daysAgo = $now->diffInDays($joiningDate);
                if ($joiningDate->isFuture()) {
                    $timeText = "in {$daysAgo} days";
                } else {
                    $timeText = "{$daysAgo} days ago";
                }
            }
            
            $notifications[] = [
                'id' => 'joining_' . $employee->id . '_' . $joiningDate->format('Y-m-d'),
                'type' => 'new_joining',
                'icon' => 'fa-user-plus',
                'color' => 'success',
                'priority' => 'medium',
                'title' => 'New Employee Joined',
                'message' => "{$employee->name} joined the company {$timeText}",
                'employee_name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'profile_image' => $employee->profile_image,
                'department' => $employee->department,
                'created_at' => $joiningDate->toDateTimeString(),
                'is_read' => false,
                'action_url' => null
            ];
        }
        
        return $notifications;
    }
    
    private function getEmployeeExitNotifications()
    {
        return []; // Simplified for now
    }
    
    private function getPayrollStatusNotifications()
    {
        return []; // Simplified for now
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the current user
     */
    public function getNotifications()
    {
        $userId = Auth::id();
        $notifications = [];
        
        // Get active manual notifications for the user
        $manualNotifications = \App\Models\ManualNotification::active()
            ->forUser($userId)
            ->where('show_in_header', true)
            ->with('reads')
            ->orderBy('priority', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Format manual notifications
        foreach ($manualNotifications as $notification) {
            $notifications[] = [
                'id' => 'manual_' . $notification->id,
                'type' => 'manual',
                'title' => $notification->title,
                'message' => $notification->message,
                'priority' => $notification->priority,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'created_at' => $notification->start_date->toDateTimeString(),
                'is_read' => $notification->isReadBy($userId),
                'action_url' => null,
                'profile_image' => null
            ];
        }
        
        // Get system-generated notifications
        $birthdays = $this->getBirthdayNotifications();
        $notifications = array_merge($notifications, $birthdays);
        
        $newJoinings = $this->getNewJoiningNotifications();
        $notifications = array_merge($notifications, $newJoinings);
        
        $employeeExits = $this->getEmployeeExitNotifications();
        $notifications = array_merge($notifications, $employeeExits);
        
        $payrollStatus = $this->getPayrollStatusNotifications();
        $notifications = array_merge($notifications, $payrollStatus);
        
        // Check read status from database for system notifications
        $systemNotificationIds = array_column(
            array_filter($notifications, fn($n) => $n['type'] !== 'manual'),
            'id'
        );
        
        if (!empty($systemNotificationIds)) {
            $readNotificationIds = DB::table('notification_reads')
                ->where('user_id', $userId)
                ->whereIn('notification_id', $systemNotificationIds)
                ->pluck('notification_id')
                ->toArray();
            
            // Update read status for system notifications
            foreach ($notifications as &$notification) {
                if ($notification['type'] !== 'manual') {
                    $notification['is_read'] = in_array($notification['id'], $readNotificationIds);
                }
            }
        }
        
        // Sort by priority and date
        usort($notifications, function($a, $b) {
            $priorityWeight = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aPriority = $priorityWeight[$a['priority']] ?? 0;
            $bPriority = $priorityWeight[$b['priority']] ?? 0;
            
            if ($aPriority !== $bPriority) {
                return $bPriority - $aPriority;
            }
            
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => count(array_filter($notifications, fn($n) => !$n['is_read']))
        ]);
    }
    
    /**
     * Get birthday notifications
     */
    private function getBirthdayNotifications()
    {
        $notifications = [];
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);
        
        $employees = DB::table('employee_basic_details')
            ->select('id', 'name', 'employee_id', 'date_of_birth', 'profile_image')
            ->whereNotNull('date_of_birth')
            ->where('status', '!=', 3) // Exclude employees who have left
            ->get();
        
        foreach ($employees as $employee) {
            if (!$employee->date_of_birth) continue;
            
            $dob = Carbon::parse($employee->date_of_birth);
            $birthdayThisYear = Carbon::create($today->year, $dob->month, $dob->day);
            
            // Check if birthday is today
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
            }
            // Check if birthday is in next 7 days
            elseif ($birthdayThisYear->between($today->copy()->addDay(), $nextWeek)) {
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
    
    /**
     * Get new employee joining notifications
     */
    private function getNewJoiningNotifications()
    {
        $notifications = [];
        $today = Carbon::now()->startOfDay();
        $sevenDaysAgo = Carbon::now()->subDays(7)->startOfDay();
        
        $newEmployees = DB::table('employee_basic_details')
            ->select('id', 'name', 'employee_id', 'date_of_joining', 'profile_image', 'department')
            ->where('date_of_joining', '>=', $sevenDaysAgo)
            ->where('date_of_joining', '<=', $today) // Only past and today joinings
            ->where('status', '!=', 3) // Exclude employees who have left
            ->orderBy('date_of_joining', 'desc')
            ->get();
        
        foreach ($newEmployees as $employee) {
            $joiningDate = Carbon::parse($employee->date_of_joining)->startOfDay();
            $now = Carbon::now()->startOfDay();
            
            // Calculate days difference properly
            if ($joiningDate->isToday()) {
                $timeText = 'today';
            } elseif ($joiningDate->isYesterday()) {
                $timeText = 'yesterday';
            } else {
                $daysAgo = $now->diffInDays($joiningDate);
                $timeText = "{$daysAgo} days ago";
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
                'action_url' => route('employees.index')
            ];
        }
        
        return $notifications;
    }
    
    /**
     * Get employee exit notifications
     */
    private function getEmployeeExitNotifications()
    {
        $notifications = [];
        $lastWeek = Carbon::now()->subDays(7);
        
        $exitedEmployees = DB::table('employee_basic_details')
            ->select('id', 'name', 'employee_id', 'date_of_resignation', 'profile_image', 'department')
            ->whereNotNull('date_of_resignation')
            ->where('date_of_resignation', '>=', $lastWeek)
            ->where('status', '=', 3) // Only employees who have left
            ->orderBy('date_of_resignation', 'desc')
            ->get();
        
        foreach ($exitedEmployees as $employee) {
            $exitDate = Carbon::parse($employee->date_of_resignation);
            $now = Carbon::now();
            
            // Calculate days difference properly
            if ($exitDate->isToday()) {
                $timeText = 'today';
            } elseif ($exitDate->isYesterday()) {
                $timeText = 'yesterday';
            } else {
                $daysAgo = $now->diffInDays($exitDate);
                if ($exitDate->isFuture()) {
                    $timeText = "in {$daysAgo} days";
                } else {
                    $timeText = "{$daysAgo} days ago";
                }
            }
            
            $notifications[] = [
                'id' => 'exit_' . $employee->id . '_' . $exitDate->format('Y-m-d'),
                'type' => 'employee_exit',
                'icon' => 'fa-user-minus',
                'color' => 'warning',
                'priority' => 'medium',
                'title' => 'Employee Left',
                'message' => "{$employee->name} left the company {$timeText}",
                'employee_name' => $employee->name,
                'employee_id' => $employee->employee_id,
                'profile_image' => $employee->profile_image,
                'department' => $employee->department,
                'created_at' => $exitDate->toDateTimeString(),
                'is_read' => false,
                'action_url' => null
            ];
        }
        
        return $notifications;
    }
    
    /**
     * Get payroll processing status notifications
     */
    
    private function getPayrollStatusNotifications()
    {
        $notifications = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Use consolidated status table for payroll month statuses
        $statusRow = DB::table('employee_payroll_attendance_payout_month_statuses')
            ->where('payout_month', (string)$currentMonth)
            ->where('payout_year', (string)$currentYear)
            ->first();

        if ($statusRow) {
            // Payroll overall status
            if ($statusRow->status === 'completed') {
                $notifications[] = [
                    'id' => 'payroll_completed_' . $currentMonth . '_' . $currentYear,
                    'type' => 'payroll_finalized',
                    'icon' => 'fa-check-circle',
                    'color' => 'success',
                    'priority' => 'high',
                    'title' => 'Payroll Completed',
                    'message' => 'Payroll for ' . Carbon::create($currentYear, $currentMonth)->format('F Y') . ' has been completed',
                    'created_at' => $statusRow->finalized_at ?? Carbon::now()->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => route('payroll.index')
                ];
            } elseif ($statusRow->status === 'progress') {
                $notifications[] = [
                    'id' => 'payroll_progress_' . $currentMonth . '_' . $currentYear,
                    'type' => 'payroll_pending',
                    'icon' => 'fa-clock',
                    'color' => 'warning',
                    'priority' => 'medium',
                    'title' => 'Payroll In Progress',
                    'message' => 'Payroll for ' . Carbon::create($currentYear, $currentMonth)->format('F Y') . ' is being processed',
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => route('payroll.index')
                ];
            }

            // OT status
            if ((int)$statusRow->ot_finalized === 1) {
                $notifications[] = [
                    'id' => 'ot_finalized_' . $currentMonth . '_' . $currentYear,
                    'type' => 'ot_finalized',
                    'icon' => 'fa-stopwatch',
                    'color' => 'info',
                    'priority' => 'medium',
                    'title' => 'OT Finalized',
                    'message' => 'OT for ' . Carbon::create($currentYear, $currentMonth)->format('F Y') . ' has been finalized',
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => route('ot-incentive.index')
                ];
            }

            // Incentive status
            if ((int)$statusRow->incentive_finalized === 1) {
                $notifications[] = [
                    'id' => 'incentive_finalized_' . $currentMonth . '_' . $currentYear,
                    'type' => 'incentive_finalized',
                    'icon' => 'fa-gift',
                    'color' => 'info',
                    'priority' => 'medium',
                    'title' => 'Incentive Finalized',
                    'message' => 'Incentive for ' . Carbon::create($currentYear, $currentMonth)->format('F Y') . ' has been finalized',
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => route('ot-incentive.index')
                ];
            }

            // Holiday work payout status
            if ((int)$statusRow->holiday_work_payout_finalized === 1) {
                $notifications[] = [
                    'id' => 'holiday_work_finalized_' . $currentMonth . '_' . $currentYear,
                    'type' => 'holiday_work_finalized',
                    'icon' => 'fa-calendar-check',
                    'color' => 'info',
                    'priority' => 'medium',
                    'title' => 'Holiday Work Payout Finalized',
                    'message' => 'Holiday work payout for ' . Carbon::create($currentYear, $currentMonth)->format('F Y') . ' has been finalized',
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'is_read' => false,
                    'action_url' => route('payroll.index')
                ];
            }
        }

        return $notifications;
    }
    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->input('notification_id');
            $userId = Auth::id();
            
            if (!$notificationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification ID is required'
                ], 400);
            }
            
            // Handle manual notifications
            if (strpos($notificationId, 'manual_') === 0) {
                $manualId = str_replace('manual_', '', $notificationId);
                $manualNotification = \App\Models\ManualNotification::find($manualId);
                
                if ($manualNotification) {
                    $manualNotification->markAsReadBy($userId);
                }
            } else {
                // Handle system notifications with database-based tracking
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
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $userId = Auth::id();
            
            // Mark all active manual notifications as read
            $manualNotifications = \App\Models\ManualNotification::active()
                ->forUser($userId)
                ->where('show_in_header', true)
                ->get();
            
            foreach ($manualNotifications as $notification) {
                $notification->markAsReadBy($userId);
            }
            
            // Get all current system notifications
            $notifications = [];
            
            // Get all notification types
            $birthdays = $this->getBirthdayNotifications();
            $notifications = array_merge($notifications, $birthdays);
            
            $newJoinings = $this->getNewJoiningNotifications();
            $notifications = array_merge($notifications, $newJoinings);
            
            $employeeExits = $this->getEmployeeExitNotifications();
            $notifications = array_merge($notifications, $employeeExits);
            
            $payrollStatus = $this->getPayrollStatusNotifications();
            $notifications = array_merge($notifications, $payrollStatus);
            
            // Extract system notification IDs (excluding manual ones already handled)
            $systemNotificationIds = array_column(
                array_filter($notifications, fn($n) => $n['type'] !== 'manual'),
                'id'
            );
            
            // Store all system notification IDs as read in database
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
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'marked_count' => count($systemNotificationIds) + count($manualNotifications)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * View all notifications page
     */
    public function viewAll()
    {
        $userId = Auth::id();
        $notifications = [];
        
        // Get active manual notifications for the user
        $manualNotifications = \App\Models\ManualNotification::active()
            ->forUser($userId)
            ->with('reads')
            ->orderBy('priority', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Format manual notifications
        foreach ($manualNotifications as $notification) {
            $notifications[] = [
                'id' => 'manual_' . $notification->id,
                'type' => 'manual',
                'title' => $notification->title,
                'message' => $notification->message,
                'priority' => $notification->priority,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'created_at' => $notification->start_date->toDateTimeString(),
                'is_read' => $notification->isReadBy($userId),
                'action_url' => route('notifications.show', 'manual_' . $notification->id),
                'status' => $notification->status,
                'profile_image' => null
            ];
        }
        
        // Get all notification types
        $birthdays = $this->getBirthdayNotifications();
        $notifications = array_merge($notifications, $birthdays);
        
        $newJoinings = $this->getNewJoiningNotifications();
        $notifications = array_merge($notifications, $newJoinings);
        
        $employeeExits = $this->getEmployeeExitNotifications();
        $notifications = array_merge($notifications, $employeeExits);
        
        $payrollStatus = $this->getPayrollStatusNotifications();
        $notifications = array_merge($notifications, $payrollStatus);
        
        // Check read status from database for system notifications
        $systemNotificationIds = array_column(
            array_filter($notifications, fn($n) => $n['type'] !== 'manual'),
            'id'
        );
        
        if (!empty($systemNotificationIds)) {
            $readNotificationIds = DB::table('notification_reads')
                ->where('user_id', $userId)
                ->whereIn('notification_id', $systemNotificationIds)
                ->pluck('notification_id')
                ->toArray();
            
            // Update read status for system notifications
            foreach ($notifications as &$notification) {
                if ($notification['type'] !== 'manual') {
                    $notification['is_read'] = in_array($notification['id'], $readNotificationIds);
                }
            }
        }
        
        // Sort by created_at descending
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return view('notifications.all', compact('notifications'));
    }
    
    /**
     * Show detailed notification view
     */
    public function show($id)
    {
        $userId = Auth::id();
        
        if (strpos($id, 'manual_') === 0) {
            $manualId = str_replace('manual_', '', $id);
            $notification = \App\Models\ManualNotification::findOrFail($manualId);
            
            // Check if user can view this notification
            if (!$notification->canUserView($userId)) {
                abort(403, 'You are not authorized to view this notification.');
            }
            
            // Mark as read if not already
            if (!$notification->isReadBy($userId)) {
                $notification->markAsReadBy($userId);
            }
            
            // Get department names
            $departmentNames = [];
            if ($notification->target_departments) {
                $departments = DB::table('departments')
                    ->whereIn('id', $notification->target_departments)
                    ->pluck('department', 'id');
                $departmentNames = $departments->toArray();
            }
            
            // Get employee names
            $employeeNames = [];
            if ($notification->target_employees) {
                $employees = DB::table('employee_basic_details')
                    ->whereIn('id', $notification->target_employees)
                    ->select('id', 'name', 'employee_id')
                    ->get()
                    ->keyBy('id')
                    ->map(function($emp) {
                        return $emp->name . ' (' . $emp->employee_id . ')';
                    });
                $employeeNames = $employees->toArray();
            }
            
            return view('notifications.show', compact('notification', 'departmentNames', 'employeeNames'));
        }
        
        // For system notifications, redirect back to all notifications
        return redirect()->route('notifications.all')->with('error', 'Detailed view not available for this notification type.');
    }
}

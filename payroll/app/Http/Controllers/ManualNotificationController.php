<?php

namespace App\Http\Controllers;

use App\Models\ManualNotification;
use App\Models\NotificationRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Flasher\Laravel\Facade\Flasher;

class ManualNotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $query = ManualNotification::with(['creator', 'updater'])
            ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by priority if provided
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }
        
        // Search by title or message
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $notifications = $query->paginate(15);
        
        // Get departments for filter
        $departments = DB::table('employee_basic_details')
            ->join('departments', 'employee_basic_details.department', '=', 'departments.id')
            ->select('departments.id', 'departments.department as name')
            ->whereNotNull('employee_basic_details.department')
            ->where('employee_basic_details.department', '!=', '')
            ->where('departments.status', true)
            ->distinct()
            ->orderBy('departments.department')
            ->pluck('name', 'id');
        
        return view('manual-notifications.index', compact('notifications', 'departments'));
    }

    public function create()
    {
        // Get departments for targeting
        $departments = DB::table('employee_basic_details')
            ->join('departments', 'employee_basic_details.department', '=', 'departments.id')
            ->select('departments.id', 'departments.department as name')
            ->whereNotNull('employee_basic_details.department')
            ->where('employee_basic_details.department', '!=', '')
            ->where('departments.status', true)
            ->distinct()
            ->orderBy('departments.department')
            ->pluck('name', 'id');
        
        // Get employees for specific targeting
        $employees = DB::table('employee_basic_details')
            ->leftJoin('departments', 'employee_basic_details.department', '=', 'departments.id')
            ->select('employee_basic_details.id', 'employee_basic_details.name', 'employee_basic_details.employee_id', 'departments.department as department_name')
            ->where('employee_basic_details.status', '!=', 3) // Exclude resigned employees
            ->orderBy('employee_basic_details.name')
            ->get();
        
        return view('manual-notifications.create', compact('departments', 'employees'));
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'target_type' => 'required|in:all,department,specific_employees',
            'target_departments' => 'required_if:target_type,department|array',
            'target_employees' => 'required_if:target_type,specific_employees|array',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'recurrence_type' => 'required|in:once,daily,weekly,monthly',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'show_in_header' => 'boolean',
            'status' => 'required|in:draft,scheduled,active'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $notification = ManualNotification::create([
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority,
                'status' => $request->has('save_as_draft') ? 'draft' : 'scheduled',
                'target_type' => $request->target_type,
                'target_departments' => $request->target_type === 'department' ? $request->target_departments : null,
                'target_employees' => $request->target_type === 'specific_employees' ? $request->target_employees : null,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
                'recurrence_type' => $request->recurrence_type,
                'recurrence_interval' => $request->recurrence_interval ?: 1,
                'recurrence_days' => $request->recurrence_type === 'weekly' ? $request->recurrence_days : null,
                'recurrence_end_date' => $request->recurrence_end_date ? Carbon::parse($request->recurrence_end_date) : null,
                'show_in_header' => $request->has('show_in_header'),
                'send_email' => $request->has('send_email'),
                'send_sms' => $request->has('send_sms'),
                'icon' => $request->icon ?: 'fa-info-circle',
                'color' => $request->color ?: 'primary',
                'created_by' => Auth::id(),
            ]);
            
            // If scheduled for now or past, mark as active
            if (Carbon::parse($request->start_date)->lte(Carbon::now())) {
                $notification->update(['status' => 'active']);
            }
            
            DB::commit();
            
            Flasher::success('Notification created successfully!');
            return redirect()->route('manual-notifications.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Flasher::error('Failed to create notification: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(ManualNotification $manualNotification)
    {
        $manualNotification->load(['creator', 'updater', 'reads.user']);
        
        // Get read statistics
        $totalTargetUsers = $manualNotification->getTargetedUsers()->count();
        $totalReads = $manualNotification->reads()->count();
        $readPercentage = $totalTargetUsers > 0 ? round(($totalReads / $totalTargetUsers) * 100, 2) : 0;
        
        return view('manual-notifications.show', compact('manualNotification', 'totalTargetUsers', 'totalReads', 'readPercentage'));
    }

    public function edit(ManualNotification $manualNotification)
    {
        // Allow editing of draft, scheduled, active, and inactive notifications
        if (!in_array($manualNotification->status, ['draft', 'scheduled', 'active', 'inactive'])) {
            Flasher::error('Only draft, scheduled, active, or inactive notifications can be edited.');
            return redirect()->route('manual-notifications.index');
        }
        
        // Get departments for targeting
        $departments = DB::table('employee_basic_details')
            ->join('departments', 'employee_basic_details.department', '=', 'departments.id')
            ->select('departments.id', 'departments.department as name')
            ->whereNotNull('employee_basic_details.department')
            ->where('employee_basic_details.department', '!=', '')
            ->where('departments.status', true)
            ->distinct()
            ->orderBy('departments.department')
            ->pluck('name', 'id');
        
        // Get employees for specific targeting
        $employees = DB::table('employee_basic_details')
            ->leftJoin('departments', 'employee_basic_details.department', '=', 'departments.id')
            ->select('employee_basic_details.id', 'employee_basic_details.name', 'employee_basic_details.employee_id', 'departments.department as department_name')
            ->where('employee_basic_details.status', '!=', 3)
            ->orderBy('employee_basic_details.name')
            ->get();
        
        return view('manual-notifications.edit', compact('manualNotification', 'departments', 'employees'));
    }

    /**
     * Update the specified notification in storage.
     */
    public function update(Request $request, ManualNotification $manualNotification)
    {
        // Allow updating of draft, scheduled, active, and inactive notifications
        if (!in_array($manualNotification->status, ['draft', 'scheduled', 'active', 'inactive'])) {
            toastr()->error('Only draft, scheduled, active, or inactive notifications can be updated.');
            return redirect()->route('manual-notifications.index');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'target_type' => 'required|in:all,department,specific_employees',
            'target_departments' => 'required_if:target_type,department|array',
            'target_employees' => 'required_if:target_type,specific_employees|array',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'recurrence_type' => 'required|in:once,daily,weekly,monthly',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'show_in_header' => 'boolean',
            'status' => 'required|in:draft,scheduled,active,inactive'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $manualNotification->update([
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority,
                'status' => $request->status,
                'target_type' => $request->target_type,
                'target_departments' => $request->target_type === 'department' ? $request->target_departments : null,
                'target_employees' => $request->target_type === 'specific_employees' ? $request->target_employees : null,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
                'recurrence_type' => $request->recurrence_type,
                'recurrence_interval' => $request->recurrence_interval ?: 1,
                'recurrence_days' => $request->recurrence_type === 'weekly' ? $request->recurrence_days : null,
                'recurrence_end_date' => $request->recurrence_end_date ? Carbon::parse($request->recurrence_end_date) : null,
                'show_in_header' => $request->has('show_in_header'),
                'send_email' => $request->has('send_email'),
                'send_sms' => $request->has('send_sms'),
                'icon' => $request->icon ?: 'fa-info-circle',
                'color' => $request->color ?: 'primary',
                'updated_by' => Auth::id(),
            ]);
            
            // If scheduled for now or past and status is scheduled, mark as active
            if ($request->status === 'scheduled' && Carbon::parse($request->start_date)->lte(Carbon::now())) {
                $manualNotification->update(['status' => 'active']);
            }
            
            DB::commit();
            
            Flasher::success('Notification updated successfully!');
            return redirect()->route('manual-notifications.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Flasher::error('Failed to update notification: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(ManualNotification $manualNotification)
    {
        try {
            // Soft delete the notification
            $manualNotification->update(['status' => 'cancelled']);
            $manualNotification->delete();
            
            Flasher::success('Notification deleted successfully!');
            
        } catch (\Exception $e) {
            Flasher::error('Failed to delete notification: ' . $e->getMessage());
        }
        
        return redirect()->route('manual-notifications.index');
    }
    
    /**
     * Activate a scheduled notification
     */
    public function activate(ManualNotification $manualNotification)
    {
        if (!in_array($manualNotification->status, ['scheduled', 'inactive'])) {
            Flasher::error('Only scheduled or inactive notifications can be activated.');
            return redirect()->back();
        }
        
        $manualNotification->update(['status' => 'active']);
        
        Flasher::success('Notification activated successfully!');
        return redirect()->back();
    }
    
    /**
     * Deactivate an active notification
     */
    public function deactivate(ManualNotification $manualNotification)
    {
        if ($manualNotification->status !== 'active') {
            Flasher::error('Only active notifications can be deactivated.');
            return redirect()->back();
        }
        
        $manualNotification->update(['status' => 'inactive']);
        
        Flasher::success('Notification deactivated successfully!');
        return redirect()->back();
    }
    
    /**
     * Get notification analytics
     */
    public function analytics(ManualNotification $manualNotification)
    {
        $targetedUsers = $manualNotification->getTargetedUsers();
        $reads = $manualNotification->reads()->with('user')->get();
        
        $analytics = [
            'total_targeted' => $targetedUsers->count(),
            'total_reads' => $reads->count(),
            'read_percentage' => $targetedUsers->count() > 0 ? 
                round(($reads->count() / $targetedUsers->count()) * 100, 2) : 0,
            'unread_count' => $targetedUsers->count() - $reads->count(),
            'reads_by_day' => $reads->groupBy(function($read) {
                return $read->read_at->format('Y-m-d');
            })->map->count(),
            'reads_by_department' => $reads->map(function($read) {
                return $read->user->employee->department ?? 'Unknown';
            })->countBy()
        ];
        
        return response()->json([
            'success' => true,
            'analytics' => $analytics,
            'targeted_users' => $targetedUsers,
            'reads' => $reads
        ]);
    }

    /**
     * Get notifications data for DataTables
     */
    public function getData(Request $request)
    {
        $query = ManualNotification::with(['creator', 'updater'])
            ->select([
                'id',
                'title',
                'message',
                'priority',
                'status',
                'target_type',
                'target_departments',
                'target_employees',
                'start_date',
                'end_date',
                'recurrence_type',
                'icon',
                'color',
                'created_by',
                'created_at'
            ]);

        // Apply filters - Handle search parameter properly
        $searchValue = '';
        if ($request->has('search.value')) {
            $searchValue = $request->input('search.value');
        } elseif ($request->has('search') && is_string($request->search)) {
            $searchValue = $request->search;
        }
        
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('title', 'like', "%{$searchValue}%")
                  ->orWhere('message', 'like', "%{$searchValue}%");
            });
        }

        if ($request->has('status') && !empty($request->input('status')) && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('priority') && !empty($request->input('priority')) && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply ordering
        $columns = ['title', 'priority', 'status', 'target_type', 'start_date', 'created_at', 'id'];
        $orderColumnIndex = $request->input('order.0.column', $request->input('order[0][column]', 6));
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $orderDirection = $request->input('order.0.dir', $request->input('order[0][dir]', 'desc'));
        $query->orderBy($orderColumn, $orderDirection);

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $notifications = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($notifications as $index => $notification) {
            $actions = '';

            // View Details
            $actions .= '<a href="' . route('manual-notifications.show', $notification) . '" class="btn-action btn-action-view" title="View Details" data-toggle="tooltip"><i class="fas fa-eye"></i></a>';

            // Edit (for draft, scheduled, active, inactive)
            if (in_array($notification->status, ['draft', 'scheduled', 'active', 'inactive'])) {
                $actions .= '<a href="' . route('manual-notifications.edit', $notification) . '" class="btn-action btn-action-edit" title="Edit" data-toggle="tooltip"><i class="fas fa-edit"></i></a>';
            }

            // Activate (for scheduled)
            if ($notification->status == 'scheduled') {
                $actions .= '<form method="POST" action="' . route('manual-notifications.activate', $notification) . '" style="display: inline;">
                    ' . csrf_field() . '
                    <button type="submit" class="btn-action btn-action-activate" title="Activate Now" data-toggle="tooltip"><i class="fas fa-play"></i></button>
                </form>';
            }

            // Deactivate (for active)
            if ($notification->status == 'active') {
                $actions .= '<form method="POST" action="' . route('manual-notifications.deactivate', $notification) . '" style="display: inline;">
                    ' . csrf_field() . '
                    <button type="submit" class="btn-action btn-action-deactivate" title="Deactivate" data-toggle="tooltip"><i class="fas fa-pause"></i></button>
                </form>';
            }

            // Analytics
            $actions .= '<button type="button" class="btn-action btn-action-analytics" title="Analytics" data-toggle="tooltip" onclick="showAnalytics(' . $notification->id . ')"><i class="fas fa-chart-bar"></i></button>';

            // Delete (for draft, scheduled, inactive)
            if (in_array($notification->status, ['draft', 'scheduled', 'inactive'])) {
                $actions .= '<form method="POST" action="' . route('manual-notifications.destroy', $notification) . '" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this notification?\')">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn-action btn-action-delete" title="Delete" data-toggle="tooltip"><i class="fas fa-trash"></i></button>
                </form>';
            }

            $data[] = [
                'no' => $start + $index + 1,
                'title' => '<div class="d-flex align-items-center">
                    <div class="notification-icon bg-' . $notification->color . ' me-3">
                        <i class="fas ' . $notification->icon . '"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">' . htmlspecialchars($notification->title) . '</h6>
                        <small class="text-muted">' . htmlspecialchars(substr($notification->message, 0, 50)) . (strlen($notification->message) > 50 ? '...' : '') . '</small>
                    </div>
                </div>',
                'priority' => '<span class="badge badge-' . ($notification->priority == 'high' ? 'danger' : ($notification->priority == 'medium' ? 'warning' : 'info')) . '">' . ucfirst($notification->priority) . '</span>',
                'status' => '<span class="badge badge-' . (
                    $notification->status == 'active' ? 'success' :
                    ($notification->status == 'scheduled' ? 'primary' :
                    ($notification->status == 'inactive' ? 'secondary' :
                    ($notification->status == 'cancelled' ? 'danger' : 'warning')))
                ) . '">' . ucfirst($notification->status) . '</span>',
                'target' => $notification->target_type == 'all' ? '<span class="badge badge-info">All Employees</span>' :
                           ($notification->target_type == 'department' ? '<span class="badge badge-primary">' . (is_array($notification->target_departments) ? count($notification->target_departments) : 0) . ' Departments</span>' :
                           '<span class="badge badge-success">' . (is_array($notification->target_employees) ? count($notification->target_employees) : 0) . ' Employees</span>'),
                'schedule' => '<div class="d-flex flex-column">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> ' . $notification->start_date->format('M d, Y H:i') . '
                    </small>' .
                    ($notification->end_date ? '<small class="text-muted">
                        <i class="fas fa-calendar-times"></i> ' . $notification->end_date->format('M d, Y H:i') . '
                    </small>' : '') .
                    ($notification->recurrence_type != 'once' ? '<small class="text-info">
                        <i class="fas fa-redo"></i> ' . ucfirst($notification->recurrence_type) . '
                    </small>' : '') .
                '</div>',
                'creator' => '<div class="d-flex flex-column">
                    <small class="fw-bold">' . ($notification->creator->name ?? 'Unknown') . '</small>
                    <small class="text-muted">' . $notification->created_at->format('M d, Y') . '</small>
                </div>',
                'action' => $actions
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }
}

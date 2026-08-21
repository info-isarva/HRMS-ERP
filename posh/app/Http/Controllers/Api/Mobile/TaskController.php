<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        
        // Your logic to fetch and return tasks goes here
        //Admin will view all tasks , manager will view tasks assigned to their team, employee will view their own tasks        
        //Implement task fetching logic based on user role

        $filter = $request->query('filter', 'all');
        $today  = Carbon::today();
        $assignedTo = $request->query('assigned_to');
        $userId = $user->id;
        $employeeIds = collect();

        // If the user is a manager, fetch their employees
        if ($user->crm_role_type === 2) {
            $employeeIds = User::where('assign_manager', $user->id)->pluck('id');
        }
        
        if($user->crm_role_type !== 0 && $user->crm_role_type !== 1){
            // Employees can only see their own tasks
            $baseQuery = Task::where(function ($query) use ($userId, $employeeIds) {
                $query->where('user_assigned_id', $userId)
                    ->orWhere('created_by', $userId);

                // Include tasks for employees if the user is a manager
                if ($employeeIds->isNotEmpty()) {
                    $query->orWhereIn('user_assigned_id', $employeeIds)
                          ->orWhereIn('created_by', $employeeIds);
                }
            })->where('status', '!=', 'Completed');
        
        }

        if($user->crm_role_type === 0 || $user->crm_role_type === 1){
            // Admins and Managers can see all tasks
            $baseQuery = Task::where('status', '!=', 'Completed');
        }
       
        if ($assignedTo) {
            $baseQuery->where('user_assigned_id', $assignedTo);
        }

        /**
         * Apply filter separately
         */
        if ($filter === 'deals') {
            $baseQuery->where('related_type', 'deal');
        }

        if ($filter === 'leads') {
            $baseQuery->where('related_type', 'lead');
        }

        $title = $request->query('title');
        if ($title) {
            $baseQuery->where('name', 'like', "%$title%");
        }

        $tasks = [
            'overdue' => [
                'count' => (clone $baseQuery)->where('due_at', '<', $today)->count(),
                'data' => (clone $baseQuery)
                ->where('due_at', '<', $today)
                ->orderBy('due_at', 'desc')->get(),
                //->paginate(10, ['*'], 'overdue_page'),
            
            ],

            'today' => [
                'count' => (clone $baseQuery)->whereDate('due_at', $today)->count(),
                'data' => (clone $baseQuery)
                ->whereDate('due_at', $today)
                ->orderBy('due_at', 'desc')->get(),
                //->paginate(10, ['*'], 'today_page'),
            ],
            'upcoming' => [
                'count' => (clone $baseQuery)->whereDate('due_at', '>', $today)->count(),
                'data' => (clone $baseQuery)
                ->whereDate('due_at', '>', $today)
                ->orderBy('due_at', 'asc')->get(),
                //->paginate(10, ['*'], 'upcoming_page'),
            ],
        ];

        // Add completed tasks for the Completed tab (with filters)
        $completedQuery = Task::where(function ($query) use ($userId, $employeeIds) {
            $query->where('user_assigned_id', $userId)
                ->orWhere('created_by', $userId);

            // Include completed tasks for employees if the user is a manager
            if ($employeeIds->isNotEmpty()) {
                $query->orWhereIn('user_assigned_id', $employeeIds)
                      ->orWhereIn('created_by', $employeeIds);
            }
        })
        ->where('status', 'Completed');
        if ($assignedTo) {
            $completedQuery->where('user_assigned_id', $assignedTo);
        }
        if ($filter === 'deals') {
            $completedQuery->where('related_type', 'deal');
        }
        if ($filter === 'leads') {
            $completedQuery->where('related_type', 'lead');
        }
        if ($title) {
            $completedQuery->where('name', 'like', "%$title%");
        }

        $tasks['completed'] = [
            'count' => $completedQuery->count(),
            'data' => $completedQuery
            ->orderBy('completed_at', 'desc')->get(),
            //->paginate(10, ['*'], 'completed_page');
        ];

        // Add organization_name to each task in all tabs
        foreach (['overdue', 'today', 'upcoming', 'completed'] as $tab) {
            foreach ($tasks[$tab]['data'] as $task) {
                $this->addOrganizationName($task);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $tasks, // Replace with actual tasks data
        ]);
    }

    function addOrganizationName($task)
    {
        try {
            if ($task->related_type === 'deal') {
                $task->organization_name =
                    Deal::with('organization')->find($task->related_id)->organization->name ?? 'No Organization';
            } elseif ($task->related_type === 'lead') {
                $task->organization_name =
                    Lead::with('organization')->find($task->related_id)->organization->name ?? 'No Organization';
            } else {
                $task->organization_name = 'Invalid Related Type';
            }
        } catch (\Exception $e) {
            $task->organization_name = 'Error Fetching Organization';
        }

        return $task;
    }

    //Create task API method
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Validate request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_at' => 'required|date',
            'related_type' => 'required|string|max:50',
            'related_id' => 'required|integer',
            'priority' => 'required|in:high,higest,low,lowest,normal',
            'status' => 'required|in:Not Started,Deferred,In Progress,Completed,Waiting for input',
            'reminder_offset' => 'nullable|integer|min:0',
            'reminder_notifications_enabled' => 'nullable|boolean',
            'user_owner_id' => 'nullable|integer',
            'user_assigned_id' => 'nullable|integer',
        ]);
        // Create task
        $task = new Task();
        $task->name = $validatedData['name'];
        $task->description = $validatedData['description'] ?? null;
        $task->due_at = $validatedData['due_at'] ?? null;
        $task->related_type = $validatedData['related_type'];
        $task->related_id = $validatedData['related_id'];
        $task->priority = $validatedData['priority'];
        $task->status = $validatedData['status'];
        $task->reminder_notifications_enabled = $validatedData['reminder_notifications_enabled'] ?? true;
        $task->user_owner_id = $validatedData['user_owner_id'] ?? $user->id;
        $task->user_assigned_id = $validatedData['user_assigned_id'];
        $task->completed_at = $validatedData['status'] === 'Completed' ? now() : null;
        $task->created_by = $user->id;
        $task->save();

        // Check if reminder notifications are enabled before creating reminders
        if($validatedData['status'] !== 'Completed' && $task->reminder_notifications_enabled){
            $offset = $validatedData['reminder_offset'] ?? 30;
            try{
                $task->reminders()->delete();
            }catch (\Exception $e) {
                // Log the exception or handle it as needed
            }
            $task->createReminder($offset);
        }
        return response()->json([
            'success' => true,
            'data' => $task,
        ], 201);
    }
    
    //Update task API method
    public function update(Request $request, $id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string|in:normal,high,highest,low,lowest',
            'status' => 'required|string|in:Not Started,Deferred,In Progress,Completed,Waiting for input',
            'due_at' => 'required|date',
            'related_type' => 'required|string|max:50',
            'related_id' => 'required|integer',
            'reminder_notifications_enabled' => 'nullable|boolean',
            'reminder_offset' => 'nullable|integer|min:0',
            'user_assigned_id' => 'nullable|integer',
        ]);
        if ($validated['status'] === 'Completed') {
            $completed_at = now();
        } else {
            $completed_at = null;
        }

        // Find task
        $task = Task::findOrFail($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        
        }
        
        // Update task
        $task->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'due_at' => $validated['due_at'],
            'priority' => $validated['priority'],
            'completed_at' => $completed_at,
            'status' => $validated['status'],
            'related_type' => $validated['related_type'],
            'related_id' => $validated['related_id'],
            'user_assigned_id' => $validated['user_assigned_id'],
            'reminder_notifications_enabled' => $validated['reminder_notifications_enabled'] ?? ($task->reminder_notifications_enabled ?? true),
            'updated_by' => Auth::id(),
        ]);

        // Manage reminders based on the flag
        if (($validated['reminder_notifications_enabled'] ?? ($task->reminder_notifications_enabled ?? true)) && $validated['status'] !== 'Completed') {
            // recreate reminder with provided offset (or default 30)
            try {
                $task->reminders()->delete();
            } catch (\Exception $e) {
                // ignore
            }
            $offset = $request->input('reminder_offset', 30);
            $task->createReminder($offset);
        }

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    //Delete task API method
    public function destroy($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Find task
        $task = Task::findOrFail($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }
        // Delete task
        $task->delete();
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    //mark task as completed
    public function markAsCompleted($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Find task
        $task = Task::findOrFail($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }
        // Mark task as completed
        $task->status = 'completed';
        $task->completed_at = now();
        $task->save();
        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    //Show task details API method
    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
            ], 401);
        }
        // Find task
        $task = Task::findOrFail($id);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }
}
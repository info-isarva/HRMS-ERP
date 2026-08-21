<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        // Only super admin can access activity logs
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->is_super_admin) {
                abort(403, 'Unauthorized access to activity logs');
            }
            return $next($request);
        });
    }

    /**
     * Display activity logs
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest()
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('log_name', 'like', "%{$search}%")
                      ->orWhere('subject_type', 'like', "%{$search}%")
                      ->orWhereHas('causer', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->log_name, function ($query, $logName) {
                return $query->where('log_name', $logName);
            })
            ->when($request->event, function ($query, $event) {
                return $query->where('event', $event);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->causer_id, function ($query, $causerId) {
                return $query->where('causer_id', $causerId);
            });

        $activities = $query->paginate(20);

        // Get filter options
        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();
        $events = Activity::distinct()->pluck('event')->filter()->values();
        $users = DB::table('users')
            ->select('id', 'name', 'email')
            ->whereIn('id', Activity::distinct()->pluck('causer_id')->filter())
            ->get();

        // Get statistics
        $stats = $this->getActivityStats();

        return view('activity-logs.index', compact('activities', 'logNames', 'events', 'users', 'stats'));
    }

    /**
     * Show activity log details
     */
    public function show($id)
    {
        $activity = Activity::with('causer')->findOrFail($id);
        
        return view('activity-logs.show', compact('activity'));
    }

    /**
     * Get activity statistics
     */
    private function getActivityStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_activities' => Activity::count(),
            'today_activities' => Activity::whereDate('created_at', $today)->count(),
            'week_activities' => Activity::where('created_at', '>=', $thisWeek)->count(),
            'month_activities' => Activity::where('created_at', '>=', $thisMonth)->count(),
            'unique_users' => Activity::distinct('causer_id')->count('causer_id'),
            'most_active_user' => Activity::select('causer_id')
                ->with('causer:id,name')
                ->groupBy('causer_id')
                ->orderByRaw('COUNT(*) DESC')
                ->first(),
            'recent_activity' => Activity::with('causer')->latest()->first(),
            'activity_by_event' => Activity::select('event', DB::raw('COUNT(*) as count'))
                ->groupBy('event')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $query = Activity::with('causer')
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc');

        $activities = $query->get();

        $filename = 'activity-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Log Name',
                'Description',
                'Event',
                'Subject Type',
                'Subject ID',
                'Causer Type',
                'Causer ID',
                'User Name',
                'User Email',
                'Properties',
                'IP Address',
                'User Agent',
                'Created At',
                'Updated At'
            ]);

            // CSV Data
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->log_name,
                    $activity->description,
                    $activity->event,
                    $activity->subject_type,
                    $activity->subject_id,
                    $activity->causer_type,
                    $activity->causer_id,
                    $activity->causer->name ?? 'N/A',
                    $activity->causer->email ?? 'N/A',
                    json_encode($activity->properties),
                    $activity->properties['ip'] ?? 'N/A',
                    $activity->properties['user_agent'] ?? 'N/A',
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete old activity logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $days = $request->input('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $deletedCount = Activity::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} old activity logs (older than {$days} days)",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Stream activity logs for real-time updates
     */
    public function stream(Request $request)
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {
            $lastId = 0;
            
            while (true) {
                // Get new activities since last check
                $newActivities = Activity::where('id', '>', $lastId)
                    ->orderBy('id')
                    ->limit(10)
                    ->get();

                if ($newActivities->count() > 0) {
                    foreach ($newActivities as $activity) {
                        $data = [
                            'type' => 'new_activity',
                            'activity' => [
                                'id' => $activity->id,
                                'description' => $activity->description,
                                'causer' => $activity->causer ? [
                                    'name' => $activity->causer->name,
                                    'email' => $activity->causer->email
                                ] : null,
                                'created_at' => $activity->created_at->toISOString(),
                            ]
                        ];
                        
                        echo "data: " . json_encode($data) . "\n\n";
                        $lastId = $activity->id;
                    }
                    
                    ob_flush();
                    flush();
                }
                
                // Sleep for 5 seconds before checking again
                sleep(5);
                
                // Check if client disconnected
                if (connection_aborted()) {
                    break;
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // For nginx
        
        return $response;
    }
}

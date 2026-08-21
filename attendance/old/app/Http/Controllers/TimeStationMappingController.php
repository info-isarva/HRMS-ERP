<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TimeStationService;
use App\Models\TimeStationMapping;
use App\Models\TimeStationLog;
use App\Models\Employee;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class TimeStationMappingController extends Controller
{
    protected $service;

    public function __construct(TimeStationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        // Get existing mappings
        $mappings = TimeStationMapping::with('employee')
            ->orderBy('ts_name')
            ->get();

        return view('timestation.mapping', compact('mappings'));
    }

    public function getUnmapped()
    {
        $users = $this->service->getUnmappedUsers();
        return response()->json($users);
    }

    public function searchEmployees(Request $request)
    {
        $term = $request->term;
        $employees = Employee::where('name', 'LIKE', "%$term%")
            ->orWhere('employee_id', 'LIKE', "%$term%")
            ->limit(10)
            ->get(['payroll_id', 'employee_id', 'name']);
            
        return response()->json($employees);
    }

    public function mapUser(Request $request)
    {
        $request->validate([
            'ts_user_id' => 'required',
            'employee_payroll_id' => 'required|exists:employees,payroll_id',
            'ts_name' => 'nullable'
        ]);

        TimeStationMapping::updateOrCreate(
            ['ts_user_id' => $request->ts_user_id],
            [
                'employee_payroll_id' => $request->employee_payroll_id,
                'ts_name' => $request->ts_name,
                'is_ignored' => false
            ]
        );

        // Retroactively update logs
        TimeStationLog::where('ts_user_id', $request->ts_user_id)
            ->update(['employee_payroll_id' => $request->employee_payroll_id, 'sync_status' => 'mapped']);

        return response()->json(['success' => true]);
    }

    public function ignoreUser(Request $request)
    {
        $request->validate(['ts_user_id' => 'required']);

        TimeStationMapping::updateOrCreate(
            ['ts_user_id' => $request->ts_user_id],
            [
                'is_ignored' => true,
                'ts_name' => $request->ts_name
            ]
        );

        TimeStationLog::where('ts_user_id', $request->ts_user_id)
            ->update(['sync_status' => 'ignored']);

        return response()->json(['success' => true]);
    }

    public function syncNow(Request $request)
    {
        // Run artisan command
        // Note: In shared hosting this might be restricted, but user has SSH so likely okay.
        // Or we can just call service method directly.
        try {
            $startDate = Carbon::now()->subDays(1)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            $activities = $this->service->fetchActivities($startDate, $endDate);
            $count = $this->service->syncLogs($activities);
            
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

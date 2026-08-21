<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\GpsTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GpsTrackingController extends Controller
{
    public function __construct(private GpsTrackingService $gpsTracking)
    {
    }

    public function index(Request $request): View
    {
        $employees = Employee::query()
            ->orderBy('name')
            ->get(['id', 'employee_id', 'name', 'designation']);

        $mangaloreEmployee = $employees->firstWhere('employee_id', 'MNG-001');
        $selectedEmployeeId = $request->integer('employee_id')
            ?: ($mangaloreEmployee?->id ?? $employees->first()?->id);
        $selectedDate = $request->get('date', now()->toDateString());

        return view('admin.gps-tracking.index', [
            'employees' => $employees,
            'selectedEmployeeId' => $selectedEmployeeId,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function trackingData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'nullable|date',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = Carbon::parse(
            $validated['date'] ?? now(GpsTrackingService::DISPLAY_TIMEZONE)->toDateString(),
            GpsTrackingService::DISPLAY_TIMEZONE
        );

        return response()->json([
            'success' => true,
            'data' => $this->gpsTracking->getTrackingDay($employee, $date),
        ]);
    }
}

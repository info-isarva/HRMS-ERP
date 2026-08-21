<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\GpsSessionException;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFieldEvent;
use App\Services\GpsTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsTrackingController extends Controller
{
    public function __construct(private GpsTrackingService $gpsTracking)
    {
    }

    public function ping(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found for this user',
            ], 404);
        }

        $validated = $request->validate([
            'pings' => 'sometimes|array|min:1',
            'pings.*.latitude' => 'required_with:pings|numeric|between:-90,90',
            'pings.*.longitude' => 'required_with:pings|numeric|between:-180,180',
            'pings.*.recorded_at' => 'required_with:pings|date',
            'pings.*.altitude' => 'nullable|numeric',
            'pings.*.accuracy' => 'nullable|numeric',
            'pings.*.speed' => 'nullable|numeric',
            'pings.*.bearing' => 'nullable|numeric',
            'latitude' => 'required_without:pings|numeric|between:-90,90',
            'longitude' => 'required_without:pings|numeric|between:-180,180',
            'recorded_at' => 'required_without:pings|date',
            'altitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'bearing' => 'nullable|numeric',
        ]);

        $pings = $validated['pings'] ?? [[
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => $validated['recorded_at'],
            'altitude' => $validated['altitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
            'speed' => $validated['speed'] ?? null,
            'bearing' => $validated['bearing'] ?? null,
        ]];

        $stored = $this->gpsTracking->storePings($employee, $request->user()->id, $pings);

        return response()->json([
            'success' => true,
            'message' => 'GPS ping(s) recorded',
            'data' => ['stored' => $stored],
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found for this user',
            ], 404);
        }

        $validated = $request->validate([
            'event_type' => 'required|in:office,visit',
            'place_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'check_in_at' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        try {
            $event = $this->gpsTracking->checkIn($employee, $request->user()->id, $validated);
        } catch (GpsSessionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->status);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in recorded',
            'data' => [
                'event' => $this->gpsTracking->serializeFieldEvent($event),
            ],
        ], 201);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found for this user',
            ], 404);
        }

        $validated = $request->validate([
            'event_id' => 'required|integer|exists:employee_field_events,id',
            'check_out_at' => 'nullable|date',
            'address' => 'nullable|string|max:500',
        ]);

        $event = EmployeeFieldEvent::query()
            ->where('employee_id', $employee->id)
            ->findOrFail($validated['event_id']);

        try {
            $event = $this->gpsTracking->checkOut($employee, $event, $validated);
        } catch (GpsSessionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->status);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-out recorded',
            'data' => [
                'event' => $this->gpsTracking->serializeFieldEvent($event),
            ],
        ]);
    }

    public function timeline(Request $request): JsonResponse
    {
        $employee = $this->resolveEmployee($request);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found for this user',
            ], 404);
        }

        $date = Carbon::parse(
            $request->get('date', now(GpsTrackingService::DISPLAY_TIMEZONE)->toDateString()),
            GpsTrackingService::DISPLAY_TIMEZONE
        );

        return response()->json([
            'success' => true,
            'data' => $this->gpsTracking->getTrackingDay($employee, $date),
        ]);
    }

    private function resolveEmployee(Request $request): ?Employee
    {
        $user = $request->user();

        if ($user->employee_id) {
            $byCode = Employee::query()->where('employee_id', $user->employee_id)->first();
            if ($byCode) {
                return $byCode;
            }
        }

        if ($user->payroll_id) {
            $byPayroll = Employee::query()->where('payroll_id', $user->payroll_id)->first();
            if ($byPayroll) {
                return $byPayroll;
            }
        }

        return Employee::query()->where('email', $user->email)->first();
    }
}

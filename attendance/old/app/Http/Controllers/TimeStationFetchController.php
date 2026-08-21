<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ProposedAttendance;
use App\Models\AttendanceRule;
use App\Models\Attendance;
use App\Models\TimeStationMapping;
use App\Models\TimeStationLog;
use App\Models\DutyRoster;
use App\Models\Shift;
use App\Services\TimeStationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeStationFetchController extends Controller
{
    protected $service;

    public function __construct(TimeStationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $monthYear = $request->get('month_year', now()->format('Y-m'));
        
        $proposed = ProposedAttendance::with('employee')
            ->where('month_year', $monthYear)
            ->orderBy('date')
            ->orderBy('employee_payroll_id')
            ->get();

        // Check if this month is already locked in the main attendance table
        // A month is considered locked if any attendance record for this month has been processed/finalized
        $monthStart = Carbon::parse($monthYear . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        $isLocked = \App\Models\Attendance::whereBetween('date', [$monthStart, $monthEnd])
            ->where('source', 'timestation_fetch')
            ->exists();

        return view('timestation.fetch', compact('proposed', 'monthYear', 'isLocked'));
    }

    public function fetch(Request $request)
    {
        $request->validate([
            'month_year' => 'required|date_format:Y-m',
        ]);

        $monthYear = $request->month_year;
        $startOfMonth = Carbon::parse($monthYear . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($monthYear . '-01')->endOfMonth();

        try {
            // 1. Fetch activities from TimeStation
            $activities = $this->service->fetchActivities($startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d'));
            
            if (empty($activities)) {
                return redirect()->back()->with('error', 'No activities found for the selected month.');
            }

            // 2. Sync to Logs (Optional, but good for tracking)
            $this->service->syncLogs($activities);

            // 3. Process into Proposed Attendance
            $this->processProposed($monthYear, $activities);

            return redirect()->route('timestation.fetch.index', ['month_year' => $monthYear])
                ->with('success', 'Attendance fetched and processed successfully.');

        } catch (\Exception $e) {
            Log::error('TimeStation Fetch Error', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error fetching attendance: ' . $e->getMessage());
        }
    }

    private function processProposed($monthYear, $activities)
    {
        $mappings = TimeStationMapping::where('is_ignored', false)->pluck('employee_payroll_id', 'ts_user_id')->toArray();

        // Clear existing non-overridden proposed records for this month
        ProposedAttendance::where('month_year', $monthYear)
            ->where('is_overridden', false)
            ->delete();

        // Group activities by employee and sort by timestamp
        $employeeActivities = [];
        foreach ($activities as $activity) {
            $tsUserId = $activity['employee_id'] ?? null;
            if (!$tsUserId || !isset($mappings[$tsUserId])) continue;

            $payrollId = $mappings[$tsUserId];
            // Normalize time string - handle both "09:01:00" and "09:01 AM"
            $timeStr = $activity['time'] ?? '00:00:00';
            $dateStr = $activity['date'] ?? '';
            
            try {
                $timestamp = Carbon::parse($dateStr . ' ' . $timeStr);
                
                $employeeActivities[$payrollId][] = [
                    'timestamp' => $timestamp,
                    'type' => $activity['activity'] ?? '',
                    'date' => $dateStr
                ];
            } catch (\Exception $e) {
                Log::warning("Skipping invalid timestamp: $dateStr $timeStr");
            }
        }

        $activeRules = AttendanceRule::where('is_active', true)
            ->orderBy('shift_threshold_hours', 'desc')
            ->get();

        foreach ($employeeActivities as $payrollId => $punches) {
            // Sort punches by timestamp ascending
            usort($punches, function($a, $b) {
                return $a['timestamp']->timestamp <=> $b['timestamp']->timestamp;
            });

            $i = 0;
            $count = count($punches);
            while ($i < $count) {
                $punch = $punches[$i];
                
                // Look for Check-In
                if (stripos($punch['type'], 'In') !== false) {
                    $checkIn = $punch;
                    $checkOut = null;
                    
                    // Look for next Check-Out (following punch)
                    for ($j = $i + 1; $j < $count; $j++) {
                        if (stripos($punches[$j]['type'], 'Out') !== false) {
                            $checkOut = $punches[$j];
                            $i = $j; // Jump to this punch
                            break;
                        }
                    }
                    
                    if ($checkIn) {
                        $inTime = $checkIn['timestamp'];
                        $outTime = $checkOut ? $checkOut['timestamp'] : null;
                        
                        // Calculate total hours - ensure positive
                        $totalHours = 0;
                        if ($outTime) {
                            // diffInMinutes with true for absolute
                            $minutes = $inTime->diffInMinutes($outTime, true);
                            $totalHours = round($minutes / 60, 2);
                        }
                        
                        $dateKey = $inTime->format('Y-m-d');
                        $status = $totalHours > 0 ? 'present' : 'absent';
                        
                        Log::debug("Processing punch pair", [
                            'emp' => $payrollId,
                            'in' => $inTime->toDateTimeString(),
                            'out' => $outTime ? $outTime->toDateTimeString() : 'MISSING',
                            'hours' => $totalHours
                        ]);

                        ProposedAttendance::updateOrCreate(
                            ['employee_payroll_id' => $payrollId, 'date' => $dateKey],
                            [
                                'check_in' => $inTime->format('H:i:s'),
                                'check_out' => $outTime ? $outTime->format('H:i:s') : null,
                                'total_hours' => $totalHours,
                                'status' => $status,
                                'source_status' => $status,
                                'month_year' => $monthYear,
                                'notes' => null
                            ]
                        );

                        // Check for Long Shift Rules
                        foreach ($activeRules as $rule) {
                            if ($totalHours >= $rule->shift_threshold_hours) {
                                // Update note for this shift
                                ProposedAttendance::where('employee_payroll_id', $payrollId)
                                    ->where('date', $dateKey)
                                    ->update(['notes' => "Triggered Rule: {$rule->name} ({$totalHours} hrs)"]);

                                // Grant recovery day
                                $recoveryDate = $inTime->copy()->addDays($rule->recovery_days_offset);
                                if ($recoveryDate->format('Y-m') == $monthYear) {
                                    ProposedAttendance::updateOrCreate(
                                        ['employee_payroll_id' => $payrollId, 'date' => $recoveryDate->format('Y-m-d')],
                                        [
                                            'status' => $rule->recovery_status,
                                            'source_status' => $rule->recovery_status,
                                            'month_year' => $monthYear,
                                            'notes' => "Recovery day for shift on {$dateKey}"
                                        ]
                                    );
                                }
                                break; // Only apply one rule (first one matched)
                            }
                        }
                    }
                }
                $i++;
            }
        }
    }

    public function override(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:proposed_attendance,id',
            'status' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $proposed = ProposedAttendance::findOrFail($request->id);
        $proposed->update([
            'status' => $request->status,
            'is_overridden' => true,
            'overridden_by' => auth()->user()->name,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true]);
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'month_year' => 'required|date_format:Y-m',
        ]);

        $monthYear = $request->month_year;
        
        // Check if month is already locked
        $monthStart = Carbon::parse($monthYear . '-01')->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        
        $isLocked = Attendance::whereBetween('date', [$monthStart, $monthEnd])
            ->where('source', 'timestation_fetch')
            ->exists();
            
        if ($isLocked) {
            return redirect()->back()->with('error', 'This month has already been finalized and cannot be processed again.');
        }
        
        Log::info('Starting finalization process', ['month_year' => $monthYear]);
        
        $proposed = ProposedAttendance::where('month_year', $monthYear)->get();
        
        if ($proposed->isEmpty()) {
            Log::warning('No proposed records found for finalization', ['month_year' => $monthYear]);
            return redirect()->back()->with('error', 'No proposed records found for this month.');
        }

        Log::info('Found proposed records', ['count' => $proposed->count()]);

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($proposed as $p) {
                try {
                    Attendance::updateOrCreate(
                        ['employee_payroll_id' => $p->employee_payroll_id, 'date' => $p->date],
                        [
                            'check_in_time' => $p->check_in,
                            'check_out_time' => $p->check_out,
                            'total_hours' => $p->total_hours,
                            'status' => $p->status,
                            'source' => 'timestation_fetch',
                            'notes' => $p->notes,
                            'processed_at' => now()
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Failed to finalize individual record', [
                        'employee_payroll_id' => $p->employee_payroll_id,
                        'date' => $p->date,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Finalization complete', [
                'success' => $successCount,
                'errors' => $errorCount
            ]);
            
            DB::commit();
            return redirect()->back()->with('success', "Successfully finalized {$successCount} records to main attendance table.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalization transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error finalizing: ' . $e->getMessage());
        }
    }
}

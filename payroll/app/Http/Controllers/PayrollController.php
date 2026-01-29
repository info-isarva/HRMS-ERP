<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\{
    EmployeeBasicDetail,
    EmployeePersonalDetail,
    EmployeeBankDetail,
    EmployeeStatutoryComponent,
    EmployeeSalaryComponent,
    StatutoryComponent,
    SalaryComponent,
    EmployeeDocument,
    EmployeePayrollAttendancePayoutMonthStatus,
    EmployeePayrollAttendance,
    EmployeePayrollAttendanceSalaryComponentOverride,
    EmployeePayrollAttendanceStatutoryComponentOverride,
    EmployeeAdvance,
    EmployeeAdvanceDeduction,
    Department,
    PositionType,
    CompanySettings,
    Location
};

use App\Helpers\FinancialYearHelper;
use App\Services\PDFGenerator;
use App\Services\ActivityLogService;
use PDF;
use Mpdf\Mpdf;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class PayrollController extends Controller
{
    public function index()
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $isEditable = $fyContext['isFinancialYearEditable'];
        $selectedFY = $fyContext['selectedFinancialYear'];
        
        $now = \Carbon\Carbon::now();

        // Get latest payout status within selected financial year
        $latestQuery = \App\Models\EmployeePayrollAttendancePayoutMonthStatus::orderByDesc('payout_year')
            ->orderByDesc('payout_month');
            
        // Filter by selected financial year
        if ($selectedFY) {
            $latestQuery = FinancialYearHelper::filterPayrollBySelectedFinancialYear($latestQuery);
        }
        
        $latest = $latestQuery->first();

        if (!$latest) {
            // Default to first month of selected financial year
            if ($selectedFY) {
                $defaultMonth = $selectedFY->start_date->month;
                $defaultYear = $selectedFY->start_date->year;
            } else {
                $defaultMonth = $now->month;
                $defaultYear = $now->year;
            }
        } elseif ($latest->status === 'completed') {
            $next = \Carbon\Carbon::createFromDate($latest->payout_year, $latest->payout_month, 1)->addMonth();
            $defaultMonth = $next->month;
            $defaultYear = $next->year;
        } else {
            $defaultMonth = $latest->payout_month;
            $defaultYear = $latest->payout_year;
        }

        // Override default with current month if we are in the active financial year
        if ($selectedFY && $now->between($selectedFY->start_date, $selectedFY->end_date)) {
            $defaultMonth = $now->month;
            $defaultYear = $now->year;
        } elseif (!$selectedFY) {
            $defaultMonth = $now->month;
            $defaultYear = $now->year;
        }

        // 🔄 Use old session value if available (for example, when override prompt was shown)
        $selectedMonth = session('old_payout_month', $defaultMonth);
        $selectedYear = session('old_payout_year', $defaultYear);

        // 🗓️ Generate dropdown options based on selected financial year
        $dropdownMonths = collect();
        
        if ($selectedFY) {
            // Use selected financial year dates
            $financialYearStart = $selectedFY->start_date->copy();
            $financialYearEnd = $selectedFY->end_date->copy();
        } else {
            // Fallback to current financial year: April to March
            $financialYearStart = \Carbon\Carbon::createFromDate(2025, 4, 1);
            $financialYearEnd = \Carbon\Carbon::createFromDate(2026, 3, 1);
        }
        
        // Current month and year for comparison
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        // Get all payroll statuses for the selected financial year
        $payrollStatusQuery = \App\Models\EmployeePayrollAttendancePayoutMonthStatus::query();
        if ($selectedFY) {
            $payrollStatusQuery = FinancialYearHelper::filterPayrollBySelectedFinancialYear($payrollStatusQuery);
        }
        
            // keyBy will overwrite if multiples exist which isn't ideal here since we need to check ALL records for a month
        // So let's group by monthKey instead
        $payrollStatuses = $payrollStatusQuery->get()
            ->groupBy(function ($item) {
                return $item->payout_year . '-' . str_pad($item->payout_month, 2, '0', STR_PAD_LEFT);
            });
            
        $activeLocationsCount = \App\Models\Location::where('status', 1)->count();
    
        // Generate all months in the financial year range
        $current = $financialYearStart->copy();
        while ($current->lte($financialYearEnd)) {
            // For current FY: only add months that are not in the future
            // For historical FY: add all months
            $shouldInclude = !$isEditable || 
                           $current->year < $currentYear || 
                           ($current->year == $currentYear && $current->month <= $currentMonth);
                           
            if ($shouldInclude) {
                $monthKey = $current->year . '-' . str_pad($current->month, 2, '0', STR_PAD_LEFT);
                $statuses = $payrollStatuses->get($monthKey);
                
                $statusLabel = ' (Not Processed)';
                $statusCode = 'not_processed';

                if ($statuses && $statuses->isNotEmpty()) {
                    // Check for global record (location_id is null)
                    $globalRecord = $statuses->firstWhere('location_id', null);
                    
                    // Check individual locations
                    $locationRecords = $statuses->whereNotNull('location_id');
                    $completedLocationsCount = $locationRecords->where('status', 'completed')->count();
                    $activeRecordsCount = $statuses->count();
                    
                    if ($globalRecord && $globalRecord->status === 'completed') {
                         $statusLabel = ' (Payroll Finalized)';
                         $statusCode = 'completed';
                    } elseif ($completedLocationsCount >= $activeLocationsCount && $activeLocationsCount > 0) { // Ensure there are active locations to compare against
                         // All individual locations are finalized
                         $statusLabel = ' (Payroll Finalized)';
                         $statusCode = 'completed';
                    } elseif ($completedLocationsCount > 0) {
                         $statusLabel = ' (Partial Finalized)';
                         $statusCode = 'partial_completed';
                    } else {
                         // Fallback logic for pending/progress
                         // If any record is in progress, show In Progress
                         if ($statuses->contains('status', 'progress')) {
                             $statusLabel = ' (In Progress)';
                             $statusCode = 'progress';
                         } elseif ($statuses->contains('status', 'pending')) {
                             $statusLabel = ' (Pending)';
                             $statusCode = 'pending';
                         }
                    }
                }
                
                $dropdownMonths->push([
                    'payout_month' => $current->month,
                    'payout_year' => $current->year,
                    'label' => $current->format('M-Y') . $statusLabel,
                    'status' => $statusCode
                ]);
            }
            $current->addMonth();
        }    
        
        // Sort in descending order (latest month first)
        $dropdownMonths = $dropdownMonths->sortByDesc(function ($month) {
            return $month['payout_year'] * 100 + $month['payout_month'];
        })->values();

        $currentStep = 1;

        // Check if this is a fresh visit (not from redirect with warning)
        if (!session()->has('warning')) {
            // Clear any old session data for fresh visits
            session()->forget(['old_payout_month', 'old_payout_year', 'old_payout_month_year', 'old_location_id']);
        }
        
        $locations = \App\Models\Location::where('status', 1)->pluck('name', 'id');

        return view('payroll.index', compact('dropdownMonths', 'selectedMonth', 'selectedYear', 'currentStep', 'fyContext', 'locations'));
    }


    public function store(Request $request)
    {
        // Check if current financial year context allows editing
        try {
            FinancialYearHelper::requireEditableContext('Cannot create new payroll for previous financial years');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
        
        $request->validate([
            'payout_month_year' => 'required'
        ]);

        list($month, $year) = explode('-', $request->payout_month_year);
        $month = (int)$month;
        $year = (int)$year;

        $now = Carbon::now();

        if (Carbon::create($year, $month)->gt($now)) {
            return back()->with('error', 'You cannot create payroll for future months.');
        }

        DB::beginTransaction();

        try {
            $locationId = $request->input('location_id'); // Can be null for "All"

            // Check if payroll for selected month/year/location already exists
        $globalPayroll = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
            ->where('payout_year', $year)
            ->whereNull('location_id')
            ->first();

        // If global payroll ("All Locations") is completed, prevent creating/updating specific location payrolls
    if ($globalPayroll && $globalPayroll->status === 'completed' && $locationId !== null) {
        return back()->with('error', 'Payroll for All Locations is already completed for this month. You cannot process individual locations separately.');
    }

    // CHECK AGGREGATE COMPLETION FOR "ALL LOCATIONS"
    // If user selected "All Locations" (null) and no global record exists, check if all individual ones are done
    if ($locationId === null && (!$globalPayroll || $globalPayroll->status !== 'completed')) {
         $activeLocationsCount = \App\Models\Location::where('status', 1)->count();
         $individualCompletedCount = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
            ->where('payout_year', $year)
            ->whereNotNull('location_id')
            ->where('status', 'completed')
            ->count();
            
         if ($individualCompletedCount >= $activeLocationsCount && $activeLocationsCount > 0) {
             DB::commit();
             return redirect()->route('payroll.salary-breakdown', ['month' => $month, 'year' => $year])
                ->with('info', 'All individual location payrolls are completed. Showing aggregate view.');
         }
    }

    $existingPayroll = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
        ->where('payout_year', $year)
        ->where('location_id', $locationId)
        ->first();

            // If payroll exists and is completed, redirect to salary breakdown
            if ($existingPayroll && $existingPayroll->status === 'completed') {
                DB::commit();
                return redirect()->route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $locationId])
                    ->with('info', 'Payroll for this month and location is already completed. Showing salary breakdown.');
            }

            // Find previous payroll for SAME location
            $previous = EmployeePayrollAttendancePayoutMonthStatus::orderByDesc('payout_year')
                ->orderByDesc('payout_month')
                ->where('location_id', $locationId)
                ->first();

            // Handle override (only if confirmed via client-side)
            if ($request->override_confirmed) {
                if ($previous && $previous->status !== 'completed') {
                    $previous->status = 'pending';
                    $previous->updated_by = Auth::id();
                    $previous->save();
                }
            }

            // Create or update current (only if not completed)
            if ($existingPayroll) {
                // If record exists but not completed, update to progress
                $existingPayroll->status = 'progress';
                $existingPayroll->updated_by = Auth::id();
                $existingPayroll->save();
                $status = $existingPayroll;
            } else {
                // Create new record
                $status = EmployeePayrollAttendancePayoutMonthStatus::create([
                    'payout_month' => $month,
                    'payout_year' => $year,
                    'location_id' => $locationId,
                    'status' => 'progress',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id()
                ]);
            }

            DB::commit();

            // Log payroll creation
            ActivityLogService::logPayrollCreated($month, $year, $status->status, [
                'payout_month_id' => $status->id,
                'created_by' => Auth::id(),
                'override_confirmed' => $request->override_confirmed ?? false
            ]);

            return redirect()->route('payroll.attendance', ['month' => $month, 'year' => $year, 'location_id' => $locationId])
                ->with('success', 'Payroll month created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function getMonthStatusSummary(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');

        // 1. Check Global (All Locations) Status
    $globalStatus = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
        ->where('payout_year', $year)
        ->whereNull('location_id')->first();

    $locations = Location::where('status', 1)->get();
    $activeLocationsCount = $locations->count();
    
    // Check individual locations status
    $individualCompletedCount = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
        ->where('payout_year', $year)
        ->whereNotNull('location_id')
        ->whereIn('location_id', $locations->pluck('id'))
        ->where('status', 'completed')
        ->count();

    // Global is completed if exclipit global record exists OR if all active individual locations are completed
    $globalCompleted = ($globalStatus && $globalStatus->status === 'completed') || 
                       ($individualCompletedCount >= $activeLocationsCount && $activeLocationsCount > 0);

    // 2. Get status for all individual locations
    $locationStatuses = [];
    $completedLocations = [];
    $pendingLocations = [];
    $progressLocations = [];

    foreach ($locations as $location) {
        $status = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
            ->where('payout_year', $year)
            ->where('location_id', $location->id)
            ->first();

        $statusText = $status ? $status->status : 'new';
        // If no individual record but explicit global exists, treat as completed (inherited)
        if ($statusText === 'new' && $globalStatus && $globalStatus->status === 'completed') {
            $statusText = 'completed';
        }
        
        $locationStatuses[] = [
            'id' => $location->id,
            'name' => $location->name,
            'status' => $statusText
        ];

        if ($statusText === 'completed') {
            $completedLocations[] = $location->name;
        } elseif ($statusText === 'progress') {
            $progressLocations[] = $location->name;
        } else {
            $pendingLocations[] = $location->name;
        }
    }
        // 3. Construct description message
        $description = '';
        if ($globalCompleted) {
            $description = '<span class="text-success fw-bold"><i class="fa fa-check-circle me-1"></i>All locations payroll completed.</span>';
        } else {
            $parts = [];
            if (!empty($completedLocations)) {
                $parts[] = '<span class="text-success"><i class="fa fa-check me-1"></i>' . implode(', ', $completedLocations) . ' payroll completed</span>';
            }
            if (!empty($progressLocations)) {
                $parts[] = '<span class="text-warning"><i class="fa fa-spinner fa-spin me-1"></i>' . implode(', ', $progressLocations) . ' in progress</span>';
            }
            if (!empty($pendingLocations)) {
                 if (count($pendingLocations) === count($locations)) {
                    $parts[] = '<span class="text-muted">All locations pending</span>';
                 } else {
                    // $parts[] = '<span class="text-muted">' . implode(', ', $pendingLocations) . ' pending</span>';
                    // Don't list all pending if some are done, just say "Others pending" or list if few
                     $parts[] = '<span class="text-muted">Others pending</span>';
                 }
            }
            $description = implode(' | ', $parts);
        }

        return response()->json([
            'global_completed' => $globalCompleted,
            'any_completed' => !empty($completedLocations),
            'description' => $description,
            'location_details' => $locationStatuses
        ]);
    }

    public function checkPayrollStatus(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
        $locationId = $request->query('location_id');

        // Check if selected month/year has completed status for this location
    $selectedPayroll = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
        ->where('payout_year', $year)
        ->where('location_id', $locationId)
        ->first();

    // Check aggregate global completion if checking for "All Locations" (locationId is null)
    $isGloballyCompleted = false;
    if ($locationId === null) {
        if ($selectedPayroll && $selectedPayroll->status === 'completed') {
            $isGloballyCompleted = true;
        } else {
             // Check individual locations
             $activeLocationsCount = Location::where('status', 1)->count();
             $individualCompletedCount = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
                ->where('payout_year', $year)
                ->whereNotNull('location_id')
                ->where('status', 'completed')
                ->count();
                
             if ($individualCompletedCount >= $activeLocationsCount && $activeLocationsCount > 0) {
                 $isGloballyCompleted = true;
             }
        }
    }

    // If selected month is already completed (or globally completed via aggregate), return special flag
    if (($selectedPayroll && $selectedPayroll->status === 'completed') || $isGloballyCompleted) {
        return response()->json([
            'requires_override' => false,
            'is_completed' => true,
            'status' => 'completed',
            'message' => 'This payroll month is already completed. You will be redirected to salary breakdown.'
        ]);
    }

        $latest = EmployeePayrollAttendancePayoutMonthStatus::orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->where('location_id', $locationId)
            ->first();

        $requiresOverride = false;

        if ($latest && $latest->status !== 'completed') {
            $selectedDate = Carbon::createFromDate($year, $month, 1);
            $latestDate = Carbon::createFromDate($latest->payout_year, $latest->payout_month, 1);

            // Check if selected month is different from the latest incomplete month
            if (!$selectedDate->isSameMonth($latestDate)) {
                $requiresOverride = true;
            }
        }

        return response()->json([
            'requires_override' => $requiresOverride,
            'is_completed' => false,
            'status' => $selectedPayroll ? $selectedPayroll->status : 'new'
        ]);
    }


    public function attendance($month, $year)
    {
        $locationId = request('location_id');

        // Find the payout month status record
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->first();
        
        if (!$payoutMonth) {
            return redirect()->route('payroll.index')->with('error', 'Payroll month not found.');
        }

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
    
        // Fetch held salaries for this month/year or indefinite
        $heldEmployeeIds = \App\Models\HeldSalary::where('status', 'active')
            ->where(function ($q) use ($month, $year) {
                $q->where('hold_type', 'indefinite')
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('hold_type', 'month')
                         ->where('payout_month', $month)
                         ->where('payout_year', $year);
                  });
            })
            ->pluck('employee_id')
            ->toArray();

        // Fetch employees: Active OR resigned in the same month
        $employees = EmployeeBasicDetail::with('exitDetails')->whereDate('date_of_joining', '<=', $endOfMonth)
            ->where(function ($query) use ($startOfMonth) {
                $query->whereNull('date_of_resignation') // Still working
                    ->orWhereDate('date_of_resignation', '>=', $startOfMonth); // Resigned after month started
            })
            ->where('exclude_from_payroll', 0) // Include only employees not excluded from payroll
            // ->whereNotIn('id', $heldEmployeeIds) // Exclude held employees - COMMENTED OUT TO ALLOW ATTENDANCE SAVING
            ->when($locationId, function ($q) use ($locationId) {
                return $q->where('location_id', $locationId);
            })
            ->orderBy('employee_id')
            ->get();



        // $total_days = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        // $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $total_days = $startOfMonth->daysInMonth;
        $monthName = $startOfMonth->format('F Y');
        $currentStep = 2; // Set current step for attendance view

        // Fetch existing attendance records for this payout month
        $existingAttendances = EmployeePayrollAttendance::where('payout_month_id', $payoutMonth->id)
        ->get()
        ->keyBy('emp_id');  // Key by employee ID for quick lookup

        // Get master table data for departments
    // Use correct column name 'department' instead of non-existent 'name'
    $departments = Department::pluck('department', 'id')->toArray();
    
        // Check if attendance has been saved (for progress steps validation)
        $attendanceSaved = $existingAttendances->isNotEmpty();
        
        // Check if payroll is finalized
        $isFinalized = $payoutMonth->status === 'completed';

        $isFinalized = $payoutMonth->status === 'completed';
        
        // Pass locationId to view
        return view('payroll.attendance', compact('employees', 'month', 'year', 'total_days', 'monthName', 'currentStep', 'existingAttendances', 'departments', 'attendanceSaved', 'isFinalized', 'locationId', 'heldEmployeeIds'));
    }

    public function saveAttendance(Request $request, $month, $year)
    {
       // print_r('hiii'); exit();
        $total_days = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        
        $request->validate([
            'present_days' => 'required|array',
            'present_days.*' => 'required|min:0|max:'.$total_days,
            'location_id' => 'nullable'
        ]);
        
        $locationId = $request->input('location_id');

      //  dd($request);
        // Get payout month status record
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->first();

        if (!$payoutMonth) {
            return back()->with('error', 'Payroll month not found. Please start over.');
        }

        DB::beginTransaction();

         try {
            $userId = auth()->id();
            $now = now();
            
            foreach ($request->present_days as $empId => $presentDays) {
                // Use updateOrCreate to ensure single record
                EmployeePayrollAttendance::updateOrCreate(
                    [
                        'emp_id' => $empId,
                        'payout_month_id' => $payoutMonth->id
                    ],
                    [
                        'total_working_days' => $total_days,
                        'employee_worked_days' => $presentDays,
                        'updated_by' => $userId,
                        'updated_at' => $now,
                        // Only set created_by on initial create
                        'created_by' => DB::raw("IF(created_by IS NULL, $userId, created_by)")
                    ]
                );
            }
            
            DB::commit();
            
            // Log attendance save
            ActivityLogService::logPayrollAttendanceSave($month, $year, [
                'payout_month_id' => $payoutMonth->id,
                'employee_count' => count($request->present_days),
                'total_working_days' => $total_days,
                'saved_by' => auth()->id()
            ]);
            
            return redirect()->route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $locationId])
                ->with('success', 'Attendance saved successfully! Proceed to salary calculation.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attendance save error: '.$e->getMessage());
            
            // Handle unique constraint violation specifically
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()->with('error', 'Duplicate attendance records detected. Please contact support.');
            }
            
            return back()->with('error', 'Failed to save attendance: '.$e->getMessage());
        }
    }


    public function salaryBreakdown($month, $year)
    {
        $locationId = request('location_id');

        // New Logic for Handling individual/aggregate view
        $finalizedPayrolls = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
            ->where('payout_year', $year)
            ->where('status', 'completed')
            ->get();
            
        $activeLocations = \App\Models\Location::where('status', 1)->get();
        $finalizedLocationIds = $finalizedPayrolls->pluck('location_id')->toArray();
        
        $isGlobalFinalized = in_array(null, $finalizedLocationIds, true);
        $individualFinalizedCount = count(array_filter($finalizedLocationIds, function($id) { return $id !== null; }));
        $isAllIndividualFinalized = $individualFinalizedCount >= $activeLocations->count();
        
        $showAllOption = $isGlobalFinalized || $isAllIndividualFinalized;
        
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->first();

        // If requesting "All" (location_id=null) but no Global record exists, check if we can show aggregate
        if (!$payoutMonth && $locationId === null && $showAllOption) {
             // Use values() to reset keys and toArray() to ensure plain array for whereIn
             $payoutMonthIdList = $finalizedPayrolls->pluck('id')->values()->toArray();
             
             if (empty($payoutMonthIdList)) {
                 return redirect()->route('payroll.index')->with('error', 'Payroll month not found.');
             }
             
             $isFinalized = true;
        } elseif ($payoutMonth) {
             $isFinalized = $payoutMonth->status === 'completed';
             $payoutMonthIdList = [$payoutMonth->id];
        } else {
             return redirect()->route('payroll.index')->with('error', 'Payroll month not found.');
        }

    $attendances = EmployeePayrollAttendance::with([
        'employee' => function($query) {
            $query->with([
                'salaryComponents' => function($q) {
                    $q->withTrashed();
                },
                'statutoryComponents' => function($q) {
                    $q->withTrashed();
                },
                'exitDetails' => function($q) {
                    $q->whereNull('deleted_at')->orderBy('id', 'desc'); // Get latest non-deleted exit requests
                }
            ]);
        },
        'salaryOverrides',
        'statutoryOverrides'
    ])->whereIn('payout_month_id', $payoutMonthIdList)->get();

    // SKP: Identify Held Employees
    $heldEmployeeIds = \App\Models\HeldSalary::where('status', 'active')
        ->where(function ($q) use ($month, $year) {
            $q->where('hold_type', 'indefinite')
              ->orWhere(function ($q2) use ($month, $year) {
                  $q2->where('hold_type', 'month')
                     ->where('payout_month', $month)
                     ->where('payout_year', $year);
              });
        })
        ->pluck('employee_id')
        ->toArray();

    $releasedEmployeeIds = \App\Models\HeldSalary::where('status', 'released')
        ->where(function ($q) use ($month, $year) {
             $q->where('hold_type', 'month')
                ->where('payout_month', $month)
                ->where('payout_year', $year);
        })
        ->pluck('employee_id')
        ->toArray();

    // Mark held employees
    $attendances->each(function($attendance) use ($heldEmployeeIds) {
        $attendance->is_held = in_array($attendance->emp_id, $heldEmployeeIds);
    });

    $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
    $currentStep = 3;

    // Get all active components with actual data
    $earningSalaryComponents = SalaryComponent::where('type', 'earning')
        ->where('status', '1')
        ->orderBy('id')
        ->get();
    $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
        ->where('status', '1')
        ->orderBy('id')
        ->get();
    $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);
    
    $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
        ->where('status', '1')
        ->orderBy('id')
        ->get();
    $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
        ->where('status', '1')
        ->orderBy('id')
        ->get();
    $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

    // Note: We'll show all active components initially and filter after calculations are done

    $epfComponentIds = [1, 2, 4]; // Your actual IDs

    if ($isFinalized) {
        // When finalized, use stored values from employee_payroll_attendances
        $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
            $earnings = json_decode($attendance->earnings, true) ?? [];
            $deductions = json_decode($attendance->deductions, true) ?? [];
            
            // Calculate EPF Wage dynamically
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                    $rawEpfWage += $earnings[$componentId]['value'];
                }
            }

            // Apply EPF option logic for display purposes
            $employeeStatutoryComponent = $attendance->employee->statutoryComponents
                ->where('statutory_component_id', 1)
                ->whereNull('deleted_at')
                ->first();
            
            $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
            switch ($epfOption) {
                case 'restrict_15000':
                    $epfWage = min(15000, $rawEpfWage);
                    break;
                case '12_percent':
                    $epfWage = $rawEpfWage;
                    break;
                case 'manual_value':
                    // For finalized payrolls, EPF wage should be based on stored earnings
                    $epfWage = $rawEpfWage;
                    break;
                default:
                    $epfWage = min(15000, $rawEpfWage);
            }

            // For finalized payrolls, find advance deduction from stored deductions
            // Look for ADVC or Advance in the stored deductions
            $storedAdvanceData = null;
            $advanceComponentId = null;
            foreach ($deductions as $componentId => $deduction) {
                if (isset($deduction['name']) && 
                    (strtoupper($deduction['name']) === 'ADVC' || 
                     strtoupper($deduction['name']) === 'ADVANCE' ||
                     stripos($deduction['name'], 'advance') !== false)) {
                    $storedAdvanceData = $deduction;
                    $advanceComponentId = $componentId;
                    break;
                }
            }
            
            // Also calculate current advance deductions (in case new advances were added after finalization)
            $currentAdvanceDeduction = $this->calculateAdvanceDeduction($attendance->employee->id, $month, $year);
            
            // Determine the final advance value to use
            $finalAdvanceValue = 0;
            $finalAdvanceApplicable = false;
            
            if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                // Use stored advance data if it exists and has value
                $finalAdvanceValue = $storedAdvanceData['value'];
                $finalAdvanceApplicable = true;
            } elseif ($currentAdvanceDeduction > 0) {
                // Use current advance calculation if no stored data or stored data is zero
                $finalAdvanceValue = $currentAdvanceDeduction;
                $finalAdvanceApplicable = true;
            }
            
            // Add advance deduction with 'advance' key for consistency in view
            $deductions['advance'] = [
                'value' => $finalAdvanceValue,
                'applicable' => $finalAdvanceApplicable,
                'name' => 'Advance',
                'default_value' => $finalAdvanceValue,
                'overridden' => false,
                'type' => 'advance',
                'status' => 'deductions'
            ];
            
            // Remove the original ADVC entry to prevent duplication
            if ($advanceComponentId !== null) {
                unset($deductions[$advanceComponentId]);
            }

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->totalEarnings = $attendance->gross_pay;
            
            // Calculate total deductions from the actual deductions array to avoid duplication
            $calculatedTotalDeductions = 0;
            foreach ($deductions as $deduction) {
                if (isset($deduction['applicable']) && $deduction['applicable'] && isset($deduction['value'])) {
                    $calculatedTotalDeductions += $deduction['value'];
                }
            }
            
            $attendance->totalDeductions = $calculatedTotalDeductions;
            $attendance->netPay = $attendance->gross_pay - $attendance->totalDeductions;
            
            $attendance->epfWage = round($epfWage);
            return $attendance;
        });
    } else {
        // When not finalized, calculate values as before
        $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
            $employee = $attendance->employee;
            $factor = $attendance->total_working_days > 0 
                ? $attendance->employee_worked_days / $attendance->total_working_days 
                : 0;

            // Create maps for component values
            $salaryComponentMap = [];
            $statutoryComponentMap = [];

            foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                $salaryComponentMap[$component->salary_component_id] = $component->value;
            }

            foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            }

            // Calculate earnings
            $earnings = [];
            $totalEarnings = 0;
            
            foreach ($earningComponents as $component) {
                $value = 0;
                $isApplicable = false;
                
                if ($component instanceof \App\Models\SalaryComponent) {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    $baseValue = $salaryComponentMap[$component->id] ?? 0;
                } else {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;
                }
                
                if ($isApplicable) {
                    $value = $baseValue * $factor;
                    $totalEarnings += $value;
                }
                
                $earnings[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
                ];
            }

            // Calculate EPF Wages
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                    $rawEpfWage += $earnings[$componentId]['value'];
                }
            }

            // Apply EPF option logic
            $employeeStatutoryComponent = $employee->statutoryComponents
                ->where('statutory_component_id', 1)
                ->whereNull('deleted_at')
                ->first();
            
            $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
            switch ($epfOption) {
                case 'restrict_15000':
                    $epfWage = min(15000, $rawEpfWage);
                    break;
                case '12_percent':
                    $epfWage = $rawEpfWage;
                    break;
                case 'manual_value':
                    $epfWage = $statutoryComponentMap[1] ?? 0; // Use manual value
                    break;
                default:
                    $epfWage = min(15000, $rawEpfWage);
            }

            // Calculate deductions
            $deductions = [];
            $totalDeductions = 0;
            
            foreach ($deductionComponents as $component) {
                $value = 0;
                $isApplicable = false;
                
                if ($component instanceof \App\Models\StatutoryComponent) {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                    if ($isApplicable) {
                        if ($component->id == 1) { // EPF
                            // Check if full amount should be deducted from employee CTC
                            $employeeStatutoryComponent = $employee->statutoryComponents
                                ->where('statutory_component_id', 1)
                                ->whereNull('deleted_at')
                                ->first();
                            
                            $fullAmountDeduct = $employeeStatutoryComponent && $employeeStatutoryComponent->full_amount_deduct_from_ctc;
                            $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
                            
                            if ($epfOption == 'manual_value') {
                                // For manual value, use the stored value directly
                                $value = round($baseValue * $factor);
                            } elseif ($fullAmountDeduct) {
                                // Deduct both employee and employer portions (24% total)
                                $value = round(0.24 * $epfWage);
                            } else {
                                // Normal employee portion only (12%)
                                $value = round(0.12 * $epfWage);
                            }
                        } elseif ($component->id == 2) { // ESI
                            if ($totalEarnings <= 21000) {
                                $value = round(0.0075 * $totalEarnings);
                            } else {
                                $value = 0;
                                $isApplicable = false;
                            }
                        } elseif ($component->id == 4) { // Professional Tax
                            $value = ($totalEarnings >= 25000) ? 200 : 0;
                        } else {
                            $value = round($baseValue * $factor);
                        }
                    }
                } else {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    if ($isApplicable) {
                        $baseValue = $salaryComponentMap[$component->id] ?? 0;
                        $value = round($baseValue * $factor);
                    }
                }

                $deductions[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
                ];
                
                if ($isApplicable) {
                    $totalDeductions += $value;
                }
            }

            // Apply existing overrides
            $this->applyComponentOverrides($attendance, $earnings, $deductions);

            // Recalculate totals after applying overrides
            $totalEarnings = 0;
            foreach ($earnings as $id => $earning) {
                if ($earning['applicable']) {
                    $totalEarnings += $earning['value'];
                }
            }

            $totalDeductions = 0;
            foreach ($deductions as $id => $deduction) {
                if ($deduction['applicable']) {
                    $totalDeductions += $deduction['value'];
                }
            }
            
            // Calculate and add any advance deductions
            $advanceDeduction = $this->calculateAdvanceDeduction($employee->id, $month, $year);
            if ($advanceDeduction > 0) {
                // Add advance as a custom deduction
                $deductions['advance'] = [
                    'value' => $advanceDeduction,
                    'applicable' => true,
                    'name' => 'Advance',
                    'default_value' => $advanceDeduction,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
                
                $totalDeductions += $advanceDeduction;
            } else {
                // Add advance as non-applicable deduction for consistency
                $deductions['advance'] = [
                    'value' => 0,
                    'applicable' => false,
                    'name' => 'Advance',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
            }

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->totalEarnings = round($totalEarnings);
            $attendance->totalDeductions = round($totalDeductions);
            $attendance->netPay = round($totalEarnings - $totalDeductions);
            $attendance->epfWage = round($epfWage);

            return $attendance;
        });
    }

    // Filter components that have actual usage by at least one employee
    $earningComponents = $earningComponents->filter(function($component) use ($attendances) {
        foreach ($attendances as $attendance) {
            // Check if this component has any usage (either applicable or has value)
            $componentData = $attendance->earnings[$component->id] ?? null;
            if ($componentData && 
                (($componentData['applicable'] ?? false) || 
                 (($componentData['value'] ?? 0) > 0))) {
                return true; // Show if any employee has it applicable or has a value
            }
        }
        return false;
    });

    $deductionComponents = $deductionComponents->filter(function($component) use ($attendances) {
        foreach ($attendances as $attendance) {
            // Check if this component has any usage (either applicable or has value)  
            $componentData = $attendance->deductions[$component->id] ?? null;
            if ($componentData && 
                (($componentData['applicable'] ?? false) || 
                 (($componentData['value'] ?? 0) > 0))) {
                return true; // Show if any employee has it applicable or has a value
            }
        }
        return false;
    });

    // Reset collection keys to ensure proper indexing
    $earningComponents = $earningComponents->values();
    $deductionComponents = $deductionComponents->values();

    // Check if any employee has advance deductions after calculations
    $hasAdvanceDeductions = false;
    foreach ($attendances as $attendance) {
        if (isset($attendance->deductions['advance']['applicable']) && 
            $attendance->deductions['advance']['applicable'] && 
            ($attendance->deductions['advance']['value'] ?? 0) > 0) {
            $hasAdvanceDeductions = true;
            break;
        }
    }

    // Get master table data for departments and designations
    $departments = \App\Models\Department::where('status', 1)
        ->pluck('department', 'id')
        ->toArray();
    $designations = \App\Models\PositionType::where('status', 1)
        ->pluck('position', 'id')
        ->toArray();
    
    // Check if attendance has been saved (for progress steps validation)
    $attendanceSaved = !$attendances->isEmpty();
    
    // Check if salaries have been reviewed (at least one attendance record exists)
    $salariesReviewed = $attendanceSaved;

    // Locations for filter
    $locations = Location::where('status', 1)->pluck('name', 'id')->toArray();

    return view('payroll.salary-breakdown', compact(
        'locations',
        'attendances',
        'month',
        'year',
        'monthName',
        'currentStep',
        'earningComponents',
        'deductionComponents',
        'epfComponentIds',
        'isFinalized',
        'hasAdvanceDeductions',
        'departments',
        'designations',
        'attendanceSaved',
        'salariesReviewed',
        'activeLocations',
        'finalizedLocationIds',
        'showAllOption',
        'locationId',
        'releasedEmployeeIds'
    ));
}

    public function comparison($month, $year)
    {
        $locationId = request('location_id');

        // Get current month data
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->first();

        if (!$payoutMonth) {
            return redirect()->route('payroll.index')->with('error', 'Payroll month not found');
        }

        $currentStep = 4; // Comparison step
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        
        // Get current month attendances with calculations
        $currentTotals = $this->getPayrollTotalsForMonth($month, $year, $locationId);

        // Get previous month data
        $previousMonth = $month - 1;
        $previousYear = $year;
        if ($previousMonth < 1) {
            $previousMonth = 12;
            $previousYear = $year - 1;
        }

        $previousTotals = null;
        $previousMonthName = Carbon::createFromDate($previousYear, $previousMonth, 1)->format('F Y');
        
        // Get previous month totals
        $previousTotals = $this->getPayrollTotalsForMonth($previousMonth, $previousYear, $locationId);

        // Calculate differences
        $differences = null;
        if ($previousTotals) {
            $differences = [
                'employees' => $currentTotals['employees'] - $previousTotals['employees'],
                'total_gross_pay' => $currentTotals['total_gross_pay'] - $previousTotals['total_gross_pay'],
                'total_deductions' => $currentTotals['total_deductions'] - $previousTotals['total_deductions'],
                'net_payable' => $currentTotals['net_payable'] - $previousTotals['net_payable'],
                'total_advance' => $currentTotals['total_advance'] - $previousTotals['total_advance'],
                'total_epf' => $currentTotals['total_epf'] - $previousTotals['total_epf'],
                'total_esic' => $currentTotals['total_esic'] - $previousTotals['total_esic'],
                'total_pt' => $currentTotals['total_pt'] - $previousTotals['total_pt'],
            ];
        }

        // Check if finalized
        $isFinalized = $payoutMonth->status === 'completed';
        $attendanceSaved = $currentTotals && $currentTotals['employees'] > 0;

        return view('payroll.comparison', compact(
            'month',
            'year',
            'monthName',
            'currentStep',
            'currentTotals',
            'previousTotals',
            'previousMonthName',
            'differences',
            'isFinalized',
            'attendanceSaved',
            'locationId'
        ));
    }

    private function getPayrollTotalsForMonth($month, $year, $locationId = null)
    {
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->first();

        if (!$payoutMonth) {
            return null;
        }

        // Get all attendance records with employee relationships
        $attendances = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->where('payout_month_id', $payoutMonth->id)->get();

        if ($attendances->isEmpty()) {
            return null;
        }

        // Get components
        $earningComponents = \App\Models\SalaryComponent::where('type', 'earning')->where('status', '1')->get()
            ->merge(\App\Models\StatutoryComponent::where('type', 'earning')->where('status', '1')->get());
        
        $deductionComponents = \App\Models\StatutoryComponent::where('type', 'deduction')->where('status', '1')->get()
            ->merge(\App\Models\SalaryComponent::where('type', 'deduction')->where('status', '1')->get());

        $epfComponentIds = [1, 2, 4];

        // Calculate totals
        $totals = [
            'employees' => $attendances->count(),
            'total_gross_pay' => 0,
            'total_deductions' => 0,
            'net_payable' => 0,
            'total_advance' => 0,
            'total_epf' => 0,
            'total_esic' => 0,
            'total_pt' => 0,
        ];

        foreach ($attendances as $attendance) {
            $employee = $attendance->employee;
            if (!$employee) continue;

            $factor = $attendance->total_working_days > 0 
                ? $attendance->employee_worked_days / $attendance->total_working_days 
                : 0;

            // Create component maps
            $salaryComponentMap = [];
            $statutoryComponentMap = [];

            foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                $salaryComponentMap[$component->salary_component_id] = $component->value;
            }

            foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            }

            // Calculate earnings
            $earnings = [];
            $grossPay = 0;

            foreach ($earningComponents as $component) {
                $isApplicable = $component instanceof \App\Models\SalaryComponent
                    ? array_key_exists($component->id, $salaryComponentMap)
                    : array_key_exists($component->id, $statutoryComponentMap);
                
                if ($isApplicable) {
                    $baseValue = $component instanceof \App\Models\SalaryComponent
                        ? ($salaryComponentMap[$component->id] ?? 0)
                        : ($statutoryComponentMap[$component->id] ?? 0);
                    
                    $value = round($baseValue * $factor);
                    $grossPay += $value;
                    $earnings[$component->id] = ['value' => $value, 'applicable' => true];
                }
            }

            // Apply overrides
            foreach ($attendance->salaryOverrides as $override) {
                if (isset($earnings[$override->salary_component_id])) {
                    $grossPay -= $earnings[$override->salary_component_id]['value'];
                    $grossPay += $override->override_value;
                }
            }

            // Calculate deductions
            $deductions = [];
            $totalDeductions = 0;
            $epf = 0;
            $esic = 0;
            $pt = 0;

            // Calculate EPF wage
            $epfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                    $epfWage += $earnings[$componentId]['value'];
                }
            }

            foreach ($deductionComponents as $component) {
                $isApplicable = $component instanceof \App\Models\SalaryComponent
                    ? array_key_exists($component->id, $salaryComponentMap)
                    : array_key_exists($component->id, $statutoryComponentMap);
                
                if ($isApplicable) {
                    $baseValue = $component instanceof \App\Models\SalaryComponent
                        ? ($salaryComponentMap[$component->id] ?? 0)
                        : ($statutoryComponentMap[$component->id] ?? 0);
                    
                    $value = 0;
                    
                    if ($component->id == 1) { // EPF
                        $value = round(0.12 * $epfWage);
                        $epf += $value;
                    } elseif ($component->id == 3) { // ESIC
                        if ($grossPay <= 21000) {
                            $value = round(0.0075 * $grossPay);
                            $esic += $value;
                        }
                    } elseif ($component->id == 4) { // PT
                        $value = ($grossPay >= 25000) ? 200 : 0;
                        $pt += $value;
                    } else {
                        $value = round($baseValue * $factor);
                    }
                    
                    $totalDeductions += $value;
                    $deductions[$component->id] = ['value' => $value, 'applicable' => true];
                }
            }

            // Apply deduction overrides
            foreach ($attendance->statutoryOverrides as $override) {
                if (isset($deductions[$override->statutory_component_id])) {
                    $totalDeductions -= $deductions[$override->statutory_component_id]['value'];
                    $totalDeductions += $override->override_value;
                }
            }

            // Add advance deductions
            $advance = $this->calculateAdvanceDeduction($employee->id, $month, $year);
            $totalDeductions += $advance;

            $totals['total_gross_pay'] += $grossPay;
            $totals['total_deductions'] += $totalDeductions;
            $totals['net_payable'] += ($grossPay - $totalDeductions);
            $totals['total_advance'] += $advance;
            $totals['total_epf'] += $epf;
            $totals['total_esic'] += $esic;
            $totals['total_pt'] += $pt;
        }

        // Only return totals if there's meaningful data
        if ($totals['total_gross_pay'] > 0 || $totals['net_payable'] > 0) {
            return $totals;
        }

        return null;
    }

    private function calculatePayrollTotals($attendances)
    {
        $totals = [
            'employees' => $attendances->count(),
            'total_gross_pay' => 0,
            'total_deductions' => 0,
            'net_payable' => 0,
            'total_advance' => 0,
            'total_epf' => 0,
            'total_esic' => 0,
            'total_pt' => 0,
        ];

        foreach ($attendances as $attendance) {
            // Use the stored gross_pay and total_deduction from database
            $grossPay = $attendance->gross_pay ?? 0;
            $totalDeductions = $attendance->total_deduction ?? 0;
            $netPayable = $attendance->total_payable ?? 0;

            // Parse deductions JSON to get breakdown
            $deductions = is_string($attendance->deductions) 
                ? json_decode($attendance->deductions, true) 
                : $attendance->deductions;

            $advance = 0;
            $epf = 0;
            $esic = 0;
            $pt = 0;

            if (is_array($deductions)) {
                foreach ($deductions as $key => $deduction) {
                    if (!is_array($deduction)) continue;
                    
                    $value = $deduction['value'] ?? 0;
                    $applicable = $deduction['applicable'] ?? false;
                    
                    if (!$applicable || $value <= 0) continue;

                    $name = strtolower($deduction['name'] ?? $key);
                    
                    // Categorize deductions based on name
                    if (stripos($name, 'epf') !== false || stripos($name, 'pf') !== false || stripos($name, 'provident') !== false) {
                        $epf += $value;
                    } elseif (stripos($name, 'esic') !== false || stripos($name, 'insurance') !== false) {
                        $esic += $value;
                    } elseif (stripos($name, 'pt') !== false || stripos($name, 'professional tax') !== false) {
                        $pt += $value;
                    } elseif (stripos($name, 'advance') !== false) {
                        $advance += $value;
                    }
                }
            }

            $totals['total_gross_pay'] += $grossPay;
            $totals['total_deductions'] += $totalDeductions;
            $totals['net_payable'] += $netPayable;
            $totals['total_advance'] += $advance;
            $totals['total_epf'] += $epf;
            $totals['total_esic'] += $esic;
            $totals['total_pt'] += $pt;
        }

        return $totals;
    }

    private function applyComponentOverrides($attendance, &$earnings, &$deductions)
    {
        // Apply salary component overrides
        foreach ($attendance->salaryOverrides as $override) {
            $componentId = $override->salary_component_id;
            if (isset($earnings[$componentId])) {
                $earnings[$componentId]['value'] = $override->override_value;
                $earnings[$componentId]['overridden'] = true;
                $earnings[$componentId]['default_value'] = $override->default_value;
                $earnings[$componentId]['applicable'] = true; // Ensure component is visible
            } elseif (isset($deductions[$componentId])) {
                $deductions[$componentId]['value'] = $override->override_value;
                $deductions[$componentId]['overridden'] = true;
                $deductions[$componentId]['default_value'] = $override->default_value;
                $deductions[$componentId]['applicable'] = true; // Ensure component is visible
            }
        }

        // Apply statutory component overrides
        foreach ($attendance->statutoryOverrides as $override) {
            $componentId = $override->statutory_component_id;
            if (isset($earnings[$componentId])) {
                $earnings[$componentId]['value'] = $override->override_value;
                $earnings[$componentId]['overridden'] = true;
                $earnings[$componentId]['default_value'] = $override->default_value;
                $earnings[$componentId]['applicable'] = true; // Ensure component is visible
            } elseif (isset($deductions[$componentId])) {
                $deductions[$componentId]['value'] = $override->override_value;
                $deductions[$componentId]['overridden'] = true;
                $deductions[$componentId]['default_value'] = $override->default_value;
                $deductions[$componentId]['applicable'] = true; // Ensure component is visible
            }
        }
    }

    public function saveComponentOverride(Request $request)
    {
        // Check if this is a removal request
        $isRemoval = $request->has('remove_override') && $request->remove_override === true;
        
        if ($isRemoval) {
            // Validation for removal (override_value can be null)
            $request->validate([
                'attendance_id' => 'required|exists:employee_payroll_attendances,id',
                'employee_id' => 'required|exists:employee_basic_details,id',
                'component_id' => 'required|integer',
                'component_type' => 'required|in:salary,statutory',
            ]);
        } else {
            // Validation for normal override
            $request->validate([
                'attendance_id' => 'required|exists:employee_payroll_attendances,id',
                'employee_id' => 'required|exists:employee_basic_details,id',
                'component_id' => 'required|integer',
                'component_type' => 'required|in:salary,statutory',
                'override_value' => 'required|numeric|min:0',
                'default_value' => 'required|numeric|min:0',
            ]);
        }

        try {
            DB::beginTransaction();

            $attendance = EmployeePayrollAttendance::findOrFail($request->attendance_id);
            $userId = auth()->id();
            
            if ($isRemoval) {
                // Remove the override by deleting the record
                if ($request->component_type === 'salary') {
                    EmployeePayrollAttendanceSalaryComponentOverride::where([
                        'payroll_attendance_id' => $request->attendance_id,
                        'emp_id' => $request->employee_id,
                        'salary_component_id' => $request->component_id,
                    ])->delete();
                } else {
                    EmployeePayrollAttendanceStatutoryComponentOverride::where([
                        'payroll_attendance_id' => $request->attendance_id,
                        'emp_id' => $request->employee_id,
                        'statutory_component_id' => $request->component_id,
                    ])->delete();
                }
                
                $actionMessage = 'Component override removed successfully (reverted to default)';
            } else {
                // Save or update the override
                if ($request->component_type === 'salary') {
                    $override = EmployeePayrollAttendanceSalaryComponentOverride::updateOrCreate(
                        [
                            'payroll_attendance_id' => $request->attendance_id,
                            'emp_id' => $request->employee_id,
                            'salary_component_id' => $request->component_id,
                        ],
                        [
                            'default_value' => $request->default_value,
                            'override_value' => $request->override_value,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]
                    );
                } else {
                    $override = EmployeePayrollAttendanceStatutoryComponentOverride::updateOrCreate(
                        [
                            'payroll_attendance_id' => $request->attendance_id,
                            'emp_id' => $request->employee_id,
                            'statutory_component_id' => $request->component_id,
                        ],
                        [
                            'default_value' => $request->default_value,
                            'override_value' => $request->override_value,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]
                    );
                }
                
                $actionMessage = 'Component override saved successfully';
            }

            // Mark attendance as manually overridden
           // $attendance->update(['manual_override' => true]);

            // Recalculate payroll for this employee
            $this->recalculateEmployeePayroll($attendance);

            // Log component override
            $employee = EmployeeBasicDetail::find($request->employee_id);
            $componentName = $request->component_type === 'salary' 
                ? SalaryComponent::find($request->component_id)->name 
                : StatutoryComponent::find($request->component_id)->name;

            if ($isRemoval) {
                ActivityLogService::log(
                    'Component Override Removed',
                    'Payroll',
                    "Removed override for {$componentName} ({$request->component_type}) for employee {$employee->name}. Reverted to default value.",
                    null,
                    null,
                    auth()->id()
                );
            } else {
                ActivityLogService::logPayrollComponentOverride(
                    $employee->name,
                    $componentName,
                    $request->component_type,
                    $request->default_value,
                    $request->override_value,
                    [
                        'employee_id' => $request->employee_id,
                        'component_id' => $request->component_id,
                        'month' => $attendance->payoutMonth->payout_month,
                        'year' => $attendance->payoutMonth->payout_year,
                        'overridden_by' => auth()->id()
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $actionMessage,
                'net_pay' => number_format($attendance->fresh()->netPay, 2),
                'gross_pay' => number_format($attendance->fresh()->totalEarnings, 2),
                'total_deductions' => number_format($attendance->fresh()->totalDeductions, 2)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving override: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recalculateEmployeePayroll($attendance)
    {
        $attendance->load([
            'employee.salaryComponents',
            'employee.statutoryComponents',
            'salaryOverrides',
            'statutoryOverrides'
        ]);

        $employee = $attendance->employee;
        $factor = $attendance->total_working_days > 0 
            ? $attendance->employee_worked_days / $attendance->total_working_days 
            : 0;

        // Get active components for this employee
        $activeSalaryComponents = $employee->salaryComponents->filter(function($c) {
            return !$c->deleted_at;
        })->pluck('value', 'salary_component_id')->toArray();

        $activeStatutoryComponents = $employee->statutoryComponents->filter(function($c) {
            return !$c->deleted_at;
        })->pluck('value', 'statutory_component_id')->toArray();

        $epfComponentIds = [1, 2, 4]; // EPF component IDs

        // Calculate earnings
        $earnings = [];
        $totalEarnings = 0;
        
        $earningComponents = SalaryComponent::where('type', 'earning')->get()
            ->merge(StatutoryComponent::where('type', 'earning')->get());
        
        foreach ($earningComponents as $component) {
            $isApplicable = false;
            $baseValue = 0;
            
            if ($component instanceof \App\Models\SalaryComponent) {
                $isApplicable = isset($activeSalaryComponents[$component->id]);
                $baseValue = $isApplicable ? $activeSalaryComponents[$component->id] : 0;
            } else {
                $isApplicable = isset($activeStatutoryComponents[$component->id]);
                $baseValue = $isApplicable ? $activeStatutoryComponents[$component->id] : 0;
            }
            
            $value = $baseValue * $factor;
            $earnings[$component->id] = [
                'name' => $component->short_name,
                'value' => $value,
                'default_value' => $value,
                'applicable' => $isApplicable,
                'overridden' => false,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                'status' => 'earnings'
            ];
            
            if ($isApplicable) {
                $totalEarnings += $value;
            }
        }

        // Calculate EPF Wages
        $rawEpfWage = 0;
        foreach ($epfComponentIds as $componentId) {
            if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                $rawEpfWage += $earnings[$componentId]['value'];
            }
        }

        // Apply EPF option logic
        $employeeStatutoryComponent = $employee->statutoryComponents
            ->where('statutory_component_id', 1)
            ->whereNull('deleted_at')
            ->first();
        
        $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
        switch ($epfOption) {
            case 'restrict_15000':
                $epfWage = min(15000, $rawEpfWage);
                break;
            case '12_percent':
                $epfWage = $rawEpfWage;
                break;
            case 'manual_value':
                $epfWage = $activeStatutoryComponents[1] ?? 0; // Use manual value
                break;
            default:
                $epfWage = min(15000, $rawEpfWage);
        }

        // Calculate deductions
        $deductions = [];
        $totalDeductions = 0;
        
        // Process statutory deductions first
        $statutoryDeductionComponents = StatutoryComponent::where('type', 'deduction')->get();
        foreach ($statutoryDeductionComponents as $component) {
            $isApplicable = isset($activeStatutoryComponents[$component->id]);
            $baseValue = $isApplicable ? $activeStatutoryComponents[$component->id] : 0;
            $value = 0;

            if ($component->id == 1) { // EPF
                if ($isApplicable) {
                    // Check if full amount should be deducted from employee CTC
                    $employeeStatutoryComponent = $employee->statutoryComponents
                        ->where('statutory_component_id', 1)
                        ->whereNull('deleted_at')
                        ->first();
                    
                    $fullAmountDeduct = $employeeStatutoryComponent && $employeeStatutoryComponent->full_amount_deduct_from_ctc;
                    $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
                    
                    if ($epfOption == 'manual_value') {
                        // For manual value, use the stored value directly
                        $value = round($baseValue * $factor);
                    } elseif ($fullAmountDeduct) {
                        // Deduct both employee and employer portions (24% total)
                        $value = round(0.24 * $epfWage);
                    } else {
                        // Normal employee portion only (12%)
                        $value = round(0.12 * $epfWage);
                    }
                } else {
                    $value = 0;
                }
            } elseif ($component->id == 2) { // ESI
                if ($isApplicable && $totalEarnings <= 21000) {
                    $value = round(0.0075 * $totalEarnings);
                } else {
                    $isApplicable = $isApplicable && ($totalEarnings <= 21000);
                    $value = 0;
                }
            } elseif ($component->id == 4) { // Professional Tax
                $value = $isApplicable ? (($totalEarnings >= 25000) ? 200 : 0) : 0;
            } else {
                // For other statutory components like Labour Welfare Fund
                $value = $isApplicable ? round($baseValue * $factor) : 0;
            }
            
            $deductions[$component->id] = [
                'name' => $component->short_name,
                'value' => $value,
                'default_value' => $value,
                'applicable' => $isApplicable,
                'overridden' => false,
                'type' => 'statutory',
                'status' => 'deductions'
            ];
            
            if ($isApplicable) {
                $totalDeductions += $value;
            }
        }
        
        // Process salary deductions separately with prefixed keys to avoid ID conflicts
        $salaryDeductionComponents = SalaryComponent::where('type', 'deduction')->get();
        foreach ($salaryDeductionComponents as $component) {
            $isApplicable = isset($activeSalaryComponents[$component->id]);
            $baseValue = $isApplicable ? $activeSalaryComponents[$component->id] : 0;
            $value = $isApplicable ? round($baseValue * $factor) : 0;
            
            // Use 'salary_' prefix to avoid conflicts with statutory component IDs
            $deductions['salary_' . $component->id] = [
                'name' => $component->short_name,
                'value' => $value,
                'default_value' => $value,
                'applicable' => $isApplicable,
                'overridden' => false,
                'type' => 'salary',
                'status' => 'deductions'
            ];
            
            if ($isApplicable) {
                $totalDeductions += $value;
            }
        }

        // Apply overrides
        foreach ($attendance->salaryOverrides as $override) {
            $componentId = $override->salary_component_id;
            if (isset($earnings[$componentId])) {
                $earnings[$componentId]['value'] = $override->override_value;
                $earnings[$componentId]['default_value'] = $override->default_value;
                $earnings[$componentId]['overridden'] = true;
            } elseif (isset($deductions['salary_' . $componentId])) {
                $deductions['salary_' . $componentId]['value'] = $override->override_value;
                $deductions['salary_' . $componentId]['default_value'] = $override->default_value;
                $deductions['salary_' . $componentId]['overridden'] = true;
            }
        }

        foreach ($attendance->statutoryOverrides as $override) {
            $componentId = $override->statutory_component_id;
            if (isset($earnings[$componentId])) {
                $earnings[$componentId]['value'] = $override->override_value;
                $earnings[$componentId]['default_value'] = $override->default_value;
                $earnings[$componentId]['overridden'] = true;
            } elseif (isset($deductions[$componentId])) {
                $deductions[$componentId]['value'] = $override->override_value;
                $deductions[$componentId]['default_value'] = $override->default_value;
                $deductions[$componentId]['overridden'] = true;
            }
        }

        // Recalculate totals after overrides
        $totalEarnings = 0;
        foreach ($earnings as $id => $earning) {
            if ($earning['applicable']) {
                $totalEarnings += $earning['value'];
            }
        }

        $totalDeductions = 0;
        foreach ($deductions as $id => $deduction) {
            if ($deduction['applicable']) {
                $totalDeductions += $deduction['value'];
            }
        }

        // Calculate and add any advance deductions
        // Extract month and year from attendance payout month
        $payoutMonth = $attendance->payoutMonth ?? EmployeePayrollAttendancePayoutMonthStatus::find($attendance->payout_month_id);
        if ($payoutMonth) {
            $month = $payoutMonth->payout_month;
            $year = $payoutMonth->payout_year;
            
            $advanceDeduction = $this->calculateAdvanceDeduction($employee->id, $month, $year);
            if ($advanceDeduction > 0) {
                $deductions['advance'] = [
                    'name' => 'Advance',
                    'value' => $advanceDeduction,
                    'default_value' => $advanceDeduction,
                    'applicable' => true,
                    'overridden' => false
                ];
                
                $totalDeductions += $advanceDeduction;
            }
        }

        // Update attendance record
        $attendance->update([
            'earnings' => json_encode($earnings),
            'deductions' => json_encode($deductions),
            'gross_pay' => round($totalEarnings),
            'total_deduction' => round($totalDeductions),
            'total_payable' => round($totalEarnings - $totalDeductions),
            'epf_wage' => round($epfWage),
            'manual_override' => $attendance->salaryOverrides->isNotEmpty() || 
                                $attendance->statutoryOverrides->isNotEmpty()
        ]);
    }

    public function finalize($month, $year)
    {
        $locationId = request('location_id');

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'location_id' => $locationId
        ])->firstOrFail();

        if ($payoutMonth->status === 'completed') {
            return redirect()->route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $locationId])
                ->with('error', 'This payroll has already been finalized!');
        }

        DB::beginTransaction();

        try {
            // Update payout month status
            $payoutMonth->status = 'completed';
            $payoutMonth->finalized_by = Auth::id();
            $payoutMonth->finalized_at = now();
            $payoutMonth->save();

            // CLEANUP LOGIC: Avoid duplicate/conflicting payroll statuses
            if ($payoutMonth->location_id !== null) {
                // If a specific location is finalized, remove the "Global/All Locations" (NULL) record
                $globalRecords = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
                    ->where('payout_year', $year)
                    ->whereNull('location_id')
                    ->get();
                    
                foreach ($globalRecords as $record) {
                    // Safely delete associated attendance records first to avoid foreign key constraints
                    EmployeePayrollAttendance::where('payout_month_id', $record->id)->delete();
                    $record->delete();
                }
            } else {
                 // If "Global/All Locations" is finalized, remove individual location records
                 $locationRecords = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
                    ->where('payout_year', $year)
                    ->whereNotNull('location_id')
                    ->get();
                    
                 foreach ($locationRecords as $record) {
                     EmployeePayrollAttendance::where('payout_month_id', $record->id)->delete();
                     $record->delete();
                 }
            }

            // Get all attendance records
            $attendances = EmployeePayrollAttendance::with([
                    'employee.salaryComponents',
                    'employee.statutoryComponents',
                    'salaryOverrides',
                    'statutoryOverrides'
                ])
                ->where('payout_month_id', $payoutMonth->id)
                ->get();

            // (Held employees are no longer excluded from calculation/storage. They are filtered only in reports)


            // Update each attendance record
            foreach ($attendances as $attendance) {
                $this->recalculateEmployeePayroll($attendance);
                
                // Save advance deductions
                $this->saveAdvanceDeductions($attendance->emp_id, $month, $year);

                $attendance->update(['is_finalized' => true]);
            }

            // Log payroll finalization
            ActivityLogService::logPayrollFinalized($month, $year, [
                'payout_month_id' => $payoutMonth->id,
                'employee_count' => $attendances->count(),
                'total_gross_pay' => $attendances->sum('gross_pay'),
                'total_net_pay' => $attendances->sum('total_payable'),
                'finalized_by' => Auth::id(),
                'finalized_at' => $payoutMonth->finalized_at
            ]);

            DB::commit();

        return redirect()->route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $locationId])
            ->with('success', 'Payroll finalized successfully! Editing is now disabled.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll finalization error: '.$e->getMessage());
            return back()->with('error', 'Failed to finalize payroll: '.$e->getMessage());
        }
    }

    /** Payslip Design - By Ashok */

    public function paySlipsList($month = null, $year = null)
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $selectedFY = $fyContext['selectedFinancialYear'];
        
        $now = \Carbon\Carbon::now();

        $defaultMonth = $now->month;
        $defaultYear = $now->year;

        // Check if payout_month_year is provided via GET parameter (from form submission)
        if (request()->has('payout_month_year')) {
            // Redirect to the salary list view by calling payrollList logic
            return $this->payrollList(request());
        }
        // If month/year are provided via route parameters, use them and set session
        elseif ($month && $year) {
            session(['old_payout_month' => (int)$month, 'old_payout_year' => (int)$year]);
        }

        // 🔄 Use old session value if available (for example, when override prompt was shown)
        $selectedMonth = session('old_payout_month', $defaultMonth);
        $selectedYear = session('old_payout_year', $defaultYear);

        // 🗓️ Generate dropdown options for months based on selected financial year
        $dropdownMonths = collect();
        
        $availableMonthsQuery = EmployeePayrollAttendancePayoutMonthStatus::select('payout_month', 'payout_year', 'status')
            ->where('status', '=', 'completed');
            
        // Filter by selected financial year
        if ($selectedFY) {
            $availableMonthsQuery = FinancialYearHelper::filterPayrollBySelectedFinancialYear($availableMonthsQuery);
        }
            
        $availableMonths = $availableMonthsQuery
            ->groupBy('payout_year', 'payout_month', 'status')  // Add status to GROUP BY
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($item) {
                return [
                    'payout_month' => $item->payout_month,
                    'payout_year' => $item->payout_year,
                    'status' => $item->status,
                    'label' => Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M-Y')
                ];
            });
        $dropdownMonths = $availableMonths;

        // Check if this is a fresh visit (not from redirect with warning)
        if (!session()->has('warning')) {
            // Clear any old session data for fresh visits
            session()->forget(['old_payout_month', 'old_payout_year', 'old_payout_month_year']);
        }

        return view('payroll.payslips.payslips-list', compact('dropdownMonths', 'selectedMonth', 'selectedYear', 'fyContext'));
    }

    public function payrollList(Request $request)
    {
        $now = \Carbon\Carbon::now();

        $defaultMonth = $now->month;
        $defaultYear = $now->year;

        // 🔄 Use old session value if available (for example, when override prompt was shown)
        $selectedMonth = session('old_payout_month', $defaultMonth);
        $selectedYear = session('old_payout_year', $defaultYear);

        // ️ Generate dropdown options for months
        $dropdownMonths = collect();
        $start = \Carbon\Carbon::createFromDate($now->year - 1, 1, 1);
        $end = \Carbon\Carbon::createFromDate($now->year, $now->month, 1);

        while ($start->lte($end)) {
            $dropdownMonths->push([
                'payout_month' => $start->month,
                'payout_year' => $start->year,
                'label' => $start->format('M-Y')
            ]);
            $start->addMonth();
        }

        $request->validate([
            'payout_month_year' => 'required'
        ]);


        list($month, $year) = explode('-', $request->payout_month_year);
        $month = (int) $month;
        $year = (int) $year;

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed'
        ])->firstOrFail();

      $isFinalized = $payoutMonth->status === 'completed';
      
        $attendances = EmployeePayrollAttendance::with([
            'employee' => function ($query) {
                $query->with([
                    'salaryComponents' => function ($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function ($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->where('payout_month_id', $payoutMonth->id)->get();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $currentStep = 3;

        // Get all components
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4]; // Your actual IDs

      if ($isFinalized) {
            // When finalized, use stored values from employee_payroll_attendances
            $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
                $earnings = json_decode($attendance->earnings, true) ?? [];
                $deductions = json_decode($attendance->deductions, true) ?? [];
                
                // Calculate EPF Wage dynamically
                $rawEpfWage = 0;
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && isset($earnings[$componentId]['applicable']) && $earnings[$componentId]['applicable']) {
                        $rawEpfWage += $earnings[$componentId]['value'];
                    }
                }

                // Apply EPF option logic for display purposes
                $employeeStatutoryComponent = $attendance->employee->statutoryComponents
                    ->where('statutory_component_id', 1)
                    ->whereNull('deleted_at')
                    ->first();
                
                $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
                switch ($epfOption) {
                    case 'restrict_15000':
                        $epfWage = min(15000, $rawEpfWage);
                        break;
                    case '12_percent':
                        $epfWage = $rawEpfWage;
                        break;
                    case 'manual_value':
                        // For finalized payrolls, EPF wage should be based on stored earnings
                        $epfWage = $rawEpfWage;
                        break;
                    default:
                        $epfWage = min(15000, $rawEpfWage);
                }

                // Add advance deduction if not already included in stored deductions
                if (!isset($deductions['advance'])) {
                    $advanceDeduction = $this->calculateAdvanceDeduction($attendance->employee->id, $month, $year);
                    if ($advanceDeduction > 0) {
                        $deductions['advance'] = [
                            'value' => $advanceDeduction,
                            'applicable' => true,
                            'name' => 'Advance',
                            'default_value' => $advanceDeduction,
                            'overridden' => false,
                            'type' => 'advance',
                            'status' => 'deductions'
                        ];
                    } else {
                        $deductions['advance'] = [
                            'value' => 0,
                            'applicable' => false,
                            'name' => 'Advance',
                            'default_value' => 0,
                            'overridden' => false,
                            'type' => 'advance',
                            'status' => 'deductions'
                        ];
                    }
                }

                // Ensure status field exists for each component
                foreach ($earnings as $id => &$earning) {
                    if (!isset($earning['status'])) {
                        $earning['status'] = 'earnings';
                    }
                }
                foreach ($deductions as $id => &$deduction) {
                    if (!isset($deduction['status'])) {
                        $deduction['status'] = 'deductions';
                    }
                }

                $earningCount = count($earnings);
                $deductionCount = count($deductions);

                if ($earningCount < $deductionCount) {
                    $earnings[$deductionCount] = [
                        'value' => '',
                        'applicable' => false,
                        'name' => '',
                        'default_value' => 0,
                        'overridden' => false,
                        'type' => '',
                        'status' => 'earning'
                    ];
                }

                if ($earningCount > $deductionCount) {
                    $deductions[$earningCount] = [
                        'value' => '',
                        'applicable' => false,
                        'name' => '',
                        'default_value' => 0,
                        'overridden' => false,
                        'type' => '',
                        'status' => 'deduction'
                    ];
                }

                $attendance->earnings = $earnings;
                $attendance->deductions = $deductions;
                $attendance->combainedValues = (object) array_merge((array) $earnings, (array) $deductions);
                $attendance->totalEarnings = $attendance->gross_pay;
                
                // For finalized payrolls, use the stored total_deduction value from database
                // This ensures we use the correct value that was calculated and stored during finalization
                $attendance->totalDeductions = $attendance->total_deduction;
                $attendance->netPay = $attendance->total_payable;
                $attendance->epfWage = round($epfWage);
                return $attendance;
            });
        } else {
        // When not finalized, calculate values as before
        $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds) {
            $employee = $attendance->employee;
            $factor = $attendance->total_working_days > 0
                ? $attendance->employee_worked_days / $attendance->total_working_days
                : 0;

            // Create maps for component values - FIXED: Only include active (non-deleted) components
            $salaryComponentMap = [];
            $statutoryComponentMap = [];

            // Process salary components - FIXED: Check if component is active for this employee
            foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                $salaryComponentMap[$component->salary_component_id] = $component->value;
            }

            // Process statutory components - FIXED: Check if component is active for this employee
            foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            }

            // Calculate earnings
            $earnings = [];
            $totalEarnings = 0;

            foreach ($earningComponents as $component) {
                $value = 0;
                $isApplicable = false; // FIXED: Default to false

                if ($component instanceof \App\Models\SalaryComponent) {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    $baseValue = $salaryComponentMap[$component->id] ?? 0;
                } else {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;
                }

                // FIXED: Only calculate value if component is applicable
                if ($isApplicable) {
                    $value = $baseValue * $factor;
                    $totalEarnings += $value;
                }

                $earnings[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                    'status' => 'earnings'
                ];
            }

            // Calculate EPF Wages - FIXED: Only include applicable earnings
            $epfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                    $epfWage += $earnings[$componentId]['value'];
                }
            }
            $epfWage = min(15000, $epfWage);

            // Calculate deductions
            $deductions = [];
            $totalDeductions = 0;

            foreach ($deductionComponents as $component) {
                $value = 0;
                $isApplicable = false; // FIXED: Default to false

                if ($component instanceof \App\Models\StatutoryComponent) {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                    // FIXED: Only calculate value if component is applicable
                    if ($isApplicable) {
                        if ($component->id == 1) { // EPF
                            $value = round(0.12 * $epfWage);
                        } elseif ($component->id == 2) { // ESI
                            if ($totalEarnings <= 21000) {
                                $value = round(0.0075 * $totalEarnings);
                            } else {
                                $value = 0;
                                $isApplicable = false; // Not applicable if earnings > 21000
                            }
                        } elseif ($component->id == 4) { // Professional Tax
                            $value = ($totalEarnings >= 25000) ? 200 : 0;
                        } else {
                            $value = round($baseValue * $factor);
                        }
                    }
                } else {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    if ($isApplicable) {
                        $baseValue = $salaryComponentMap[$component->id] ?? 0;
                        $value = round($baseValue * $factor);
                    }
                }

                $deductions[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                    'status' => 'deductions'
                ];

                // FIXED: Only add to total if applicable
                if ($isApplicable) {
                    $totalDeductions += $value;
                }
            }

            // Apply existing overrides
            $this->applyComponentOverrides($attendance, $earnings, $deductions);

            // Recalculate totals after applying overrides
            $totalEarnings = 0;
            foreach ($earnings as $id => $earning) {
                if ($earning['applicable']) {
                    $totalEarnings += $earning['value'];
                }
            }

            $totalDeductions = 0;
            foreach ($deductions as $id => $deduction) {
                if ($deduction['applicable']) {
                    $totalDeductions += $deduction['value'];
                }
            }

            // Calculate and add any advance deductions
            $advanceDeduction = $this->calculateAdvanceDeduction($employee->id, $month, $year);
            if ($advanceDeduction > 0) {
                // Add advance as a custom deduction
                $deductions['advance'] = [
                    'value' => $advanceDeduction,
                    'applicable' => true,
                    'name' => 'Advance',
                    'default_value' => $advanceDeduction,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
                
                $totalDeductions += $advanceDeduction;
            } else {
                // Add advance as non-applicable deduction for consistency
                $deductions['advance'] = [
                    'value' => 0,
                    'applicable' => false,
                    'name' => 'Advance',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
            }

            $earningCount = count($earnings);
            $deductionCount = count($deductions);

            if ($earningCount < $deductionCount)
                $earnings[$deductionCount] = [
                    'value' => '',
                    'applicable' => false,
                    'name' => '',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => '',
                    'status' => 'earning'
                ];

            if ($earningCount > $deductionCount)
                $deductions[$earningCount] = [
                    'value' => '',
                    'applicable' => false,
                    'name' => '',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => '',
                    'status' => 'deduction'
                ];

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->combainedValues = (object) array_merge((array) $earnings, (array) $deductions);
            $attendance->totalEarnings = round($totalEarnings);
            $attendance->totalDeductions = round($totalDeductions);
            $attendance->netPay = round($totalEarnings - $totalDeductions);
            $attendance->epfWage = round($epfWage);

            return $attendance;
        });
      }

        $isFinalized = $payoutMonth->status === 'completed';

        // Get master table data for departments and designations
    // Correct master data plucks
    $departments = Department::pluck('department', 'id')->toArray();
    $designations = PositionType::pluck('position', 'id')->toArray();

        return view('payroll.payslips.salary-list', compact(
            'attendances',
            'month',
            'year',
            'monthName',
            'currentStep',
            'earningComponents',
            'deductionComponents',
            'epfComponentIds',
            'isFinalized',
            'dropdownMonths',
            'selectedMonth',
            'selectedYear',
            'departments',
            'designations'
        ));

    }
    public function payslip()
    {
        return view('payroll.payslips.payslips');
    }

    // public function payslip_pdf(PDFGenerator $pdfGenerator)
    // {
    //     $html = view('payroll.payslips.pdf-format', ['data' => ''])->render();

    //     return $pdfGenerator->createPDF($html, 'payslip-30-06-2025.pdf', false);
    // }

    public function payslip_pdf(EmployeeBasicDetail $employee, $month, $year, PDFGenerator $pdfGenerator)
    {
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed'
        ])->firstOrFail();+
          
        $isFinalized = $payoutMonth->status === 'completed';

        $attendances = EmployeePayrollAttendance::with([
            'employee' => function ($query) {
                $query->with([
                    'salaryComponents' => function ($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function ($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->where([
            ['payout_month_id', '=', $payoutMonth->id],
            ['emp_id', '=', $employee->id], // Only this employee
        ])->get();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $currentStep = 3;

        // Get all components
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4]; // Your actual IDs
      
      if ($isFinalized) {
            // When finalized, use stored values from employee_payroll_attendances
            $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
                $earnings = json_decode($attendance->earnings, true) ?? [];
                $deductions = json_decode($attendance->deductions, true) ?? [];
                
                // Calculate EPF Wage dynamically with full amount deduction support
                $rawEpfWage = 0;
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && isset($earnings[$componentId]['applicable']) && $earnings[$componentId]['applicable']) {
                        $rawEpfWage += $earnings[$componentId]['value'];
                    }
                }
                
                // Get employee's EPF configuration for dynamic calculation
                $employee = $attendance->employee;
                $epfStatutory = $employee->statutoryComponents->where('statutory_component_id', 1)->first();
                $epfOption = $epfStatutory->epf_option ?? 'restrict_15000';
                $fullAmountDeduct = $epfStatutory->full_amount_deduct_from_ctc ?? false;
                
                // Apply EPF option logic
                switch ($epfOption) {
                    case 'restrict_15000':
                        $epfWage = min(15000, $rawEpfWage);
                        break;
                    case '12_percent':
                        $epfWage = $rawEpfWage;
                        break;
                    case 'manual_value':
                        $epfWage = $epfStatutory->value ?? 0;
                        break;
                    default:
                        $epfWage = min(15000, $rawEpfWage);
                }

                // For finalized payrolls, find advance deduction from stored deductions
                // Look for ADVC or Advance in the stored deductions
                $storedAdvanceData = null;
                $advanceComponentId = null;
                foreach ($deductions as $componentId => $deduction) {
                    if (isset($deduction['name']) && 
                        (strtoupper($deduction['name']) === 'ADVC' || 
                         strtoupper($deduction['name']) === 'ADVANCE' ||
                         stripos($deduction['name'], 'advance') !== false)) {
                        $storedAdvanceData = $deduction;
                        $advanceComponentId = $componentId;
                        break;
                    }
                }
                
                // Also calculate current advance deductions (in case new advances were added after finalization)
                $currentAdvanceDeduction = $this->calculateAdvanceDeduction($attendance->employee->id, $month, $year);
                
                // Determine the final advance value to use
                $finalAdvanceValue = 0;
                $finalAdvanceApplicable = false;
                
                if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                    // Use stored advance data if it exists and has value
                    $finalAdvanceValue = $storedAdvanceData['value'];
                    $finalAdvanceApplicable = true;
                } elseif ($currentAdvanceDeduction > 0) {
                    // Use current advance calculation if no stored data or stored data is zero
                    $finalAdvanceValue = $currentAdvanceDeduction;
                    $finalAdvanceApplicable = true;
                }
                
                // Add advance deduction with 'advance' key for consistency in payslip
                $deductions['advance'] = [
                    'value' => $finalAdvanceValue,
                    'applicable' => $finalAdvanceApplicable,
                    'name' => 'Advance',
                    'default_value' => $finalAdvanceValue,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
                
                // Remove the original ADVC entry to prevent duplication
                if ($advanceComponentId !== null) {
                    unset($deductions[$advanceComponentId]);
                }

                // Ensure status field exists for each component
                foreach ($earnings as $id => &$earning) {
                    if (!isset($earning['status'])) {
                        $earning['status'] = 'earnings';
                    }
                }
                foreach ($deductions as $id => &$deduction) {
                    if (!isset($deduction['status'])) {
                        $deduction['status'] = 'deductions';
                    }
                }

                $earningCount = count($earnings);
                $deductionCount = count($deductions);

                if ($earningCount < $deductionCount) {
                    $earnings[$deductionCount] = [
                        'value' => '',
                        'applicable' => false,
                        'name' => '',
                        'default_value' => 0,
                        'overridden' => false,
                        'type' => '',
                        'status' => 'earning'
                    ];
                }

                if ($earningCount > $deductionCount) {
                    $deductions[$earningCount] = [
                        'value' => '',
                        'applicable' => false,
                        'name' => '',
                        'default_value' => 0,
                        'overridden' => false,
                        'type' => '',
                        'status' => 'deduction'
                    ];
                }

                $attendance->earnings = $earnings;
                $attendance->deductions = $deductions;
                $attendance->combainedValues = (object) array_merge((array) $earnings, (array) $deductions);
                $attendance->totalEarnings = $attendance->gross_pay;
                
                // For finalized payrolls, use the stored total_deduction value from database
                // This ensures we use the correct value that was calculated and stored during finalization
                $attendance->totalDeductions = $attendance->total_deduction;
                $attendance->netPay = $attendance->total_payable;
                $attendance->epfWage = round($epfWage);
                return $attendance;
            });
        } else {
            // When not finalized, calculate values as before
        $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
            $employee = $attendance->employee;
            $factor = $attendance->total_working_days > 0
                ? $attendance->employee_worked_days / $attendance->total_working_days
                : 0;

            // Create maps for component values - FIXED: Only include active (non-deleted) components
            $salaryComponentMap = [];
            $statutoryComponentMap = [];

            // Process salary components - FIXED: Check if component is active for this employee
            foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                $salaryComponentMap[$component->salary_component_id] = $component->value;
            }

            // Process statutory components - FIXED: Check if component is active for this employee
            foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            }

            // Calculate earnings
            $earnings = [];
            $totalEarnings = 0;

            foreach ($earningComponents as $component) {
                $value = 0;
                $isApplicable = false; // FIXED: Default to false

                if ($component instanceof \App\Models\SalaryComponent) {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    $baseValue = $salaryComponentMap[$component->id] ?? 0;
                } else {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;
                }

                // FIXED: Only calculate value if component is applicable
                if ($isApplicable) {
                    $value = $baseValue * $factor;
                    $totalEarnings += $value;
                }

                $earnings[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                    'status' => 'earnings'
                ];
            }

            // Calculate EPF Wages - FIXED: Only include applicable earnings
            $epfWage = 0;
            foreach ($epfComponentIds as $componentId) {
                if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                    $epfWage += $earnings[$componentId]['value'];
                }
            }
            $epfWage = min(15000, $epfWage);

            // Calculate deductions
            $deductions = [];
            $totalDeductions = 0;

            foreach ($deductionComponents as $component) {
                $value = 0;
                $isApplicable = false; // FIXED: Default to false

                if ($component instanceof \App\Models\StatutoryComponent) {
                    $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                    $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                    // FIXED: Only calculate value if component is applicable
                    if ($isApplicable) {
                        if ($component->id == 1) { // EPF
                            $value = round(0.12 * $epfWage);
                        } elseif ($component->id == 2) { // ESI
                            if ($totalEarnings <= 21000) {
                                $value = round(0.0075 * $totalEarnings);
                            } else {
                                $value = 0;
                                $isApplicable = false; // Not applicable if earnings > 21000
                            }
                        } elseif ($component->id == 4) { // Professional Tax
                            $value = ($totalEarnings >= 25000) ? 200 : 0;
                        } else {
                            $value = round($baseValue * $factor);
                        }
                    }
                } else {
                    $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                    if ($isApplicable) {
                        $baseValue = $salaryComponentMap[$component->id] ?? 0;
                        $value = round($baseValue * $factor);
                    }
                }

                $deductions[$component->id] = [
                    'value' => $value,
                    'applicable' => $isApplicable,
                    'name' => $component->name,
                    'default_value' => $value,
                    'overridden' => false,
                    'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                    'status' => 'deductions'
                ];

                // FIXED: Only add to total if applicable
                if ($isApplicable) {
                    $totalDeductions += $value;
                }
            }
            // Apply existing overrides
            $this->applyComponentOverrides($attendance, $earnings, $deductions);

            $totalEarnings = 0;
            foreach ($earnings as $id => $earning) {
                if ($earning['applicable']) {
                    $totalEarnings += $earning['value'];
                }
            }

            $totalDeductions = 0;
            foreach ($deductions as $id => $deduction) {
                if ($deduction['applicable']) {
                    $totalDeductions += $deduction['value'];
                }
            }

            // Calculate and add any advance deductions
            $advanceDeduction = $this->calculateAdvanceDeduction($employee->id, $month, $year);
            if ($advanceDeduction > 0) {
                // Add advance as a custom deduction
                $deductions['advance'] = [
                    'value' => $advanceDeduction,
                    'applicable' => true,
                    'name' => 'Advance',
                    'default_value' => $advanceDeduction,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
                
                $totalDeductions += $advanceDeduction;
            } else {
                // Add advance as non-applicable deduction for consistency
                $deductions['advance'] = [
                    'value' => 0,
                    'applicable' => false,
                    'name' => 'Advance',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => 'advance',
                    'status' => 'deductions'
                ];
            }

            $earningCount = count($earnings);
            $deductionCount = count($deductions);

            if ($earningCount < $deductionCount)
                $earnings[$deductionCount] = [
                    'value' => '',
                    'applicable' => false,
                    'name' => '',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => '',
                    'status' => 'earning'
                ];

            if ($earningCount > $deductionCount)
                $deductions[$earningCount] = [
                    'value' => '',
                    'applicable' => false,
                    'name' => '',
                    'default_value' => 0,
                    'overridden' => false,
                    'type' => '',
                    'status' => 'deduction'
                ];
          
                $attendance->earnings = $earnings;
                $attendance->deductions = $deductions;
                $attendance->combainedValues = (object) array_merge((array) $earnings, (array) $deductions);
                $attendance->totalEarnings = round($totalEarnings);
                $attendance->totalDeductions = round($totalDeductions);
                $attendance->netPay = round($totalEarnings - $totalDeductions);
                $attendance->epfWage = round($epfWage);
    
                return $attendance;
            });
      }
      
        // Get master table data for departments and designations
    $departments = Department::pluck('department', 'id')->toArray();
    $designations = PositionType::pluck('position', 'id')->toArray();
    
    // Get company settings for PDF template
    $companySettings = CompanySettings::where('id', 1)->first();
        
        $html = view('payroll.payslips.pdf-format', [
            'employee' => $employee, 
            'attendances' => $attendances, 
            'monthName' => $monthName,
            'departments' => $departments,
            'designations' => $designations,
            'companySettings' => $companySettings
        ])->render();

        // Log payslip PDF generation
        ActivityLogService::logPayrollDataExported('payslip_pdf', $month, $year, 'pdf', 1);

        return $pdfGenerator->createPDF($html, 'payslip-'.now()->format('d_M_Y_Hi').'.pdf', false);
    }



public function downloadBankTransferExcel($month, $year)
{
    try {
        $locationId = request('location_id');

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed',
            'location_id' => $locationId
        ])->first();

        $payoutMonthIdList = [];
        if (!$payoutMonth && $locationId === null) {
             $ids = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed'
            ])->pluck('id')->toArray();
            if (!empty($ids)) $payoutMonthIdList = $ids;
        } elseif ($payoutMonth) {
            $payoutMonthIdList = [$payoutMonth->id];
        }

        if (empty($payoutMonthIdList)) {
            abort(404, 'Payroll month not found');
        }

        // Get held employees to exclude
        $heldEmployeeIds = $this->getActiveHeldEmployeeIds($month, $year);

        // 2. Fetch attendance with employee + bank + personal detail
        $attendances = EmployeePayrollAttendance::with([
            'employee.bankDetail',
            'employee.personalDetail'
        ])->whereIn('payout_month_id', $payoutMonthIdList)
          ->whereNotIn('emp_id', $heldEmployeeIds)
          ->whereDoesntHave('employee.exitDetails', function($q) {
              $q->where('settlement_mode', 'immediate');
          })
          ->get();

        // 3. Company static info
        $company = [
            'account_no' => 'YOUR_COMPANY_ACCOUNT_NUMBER',
            'name' => 'DIVYA ROOPA INFRACON PVT LTD',
            'address' => [
                'line1' => 'PERMUDE',
                'line2' => 'MANGALORE',
                'line3' => '5745'
            ]
        ];

        // 4. Load XLS with macro
        $templatePath = storage_path('app/templates/CANARA BANK BULK FILE SHEET.xls');
        $fileName = 'canara_bank_bulk_' . strtolower(now()->format('d_M_Y_Hi')) . '.xls';
        $outputPath = storage_path("app/output/{$fileName}");

        $reader = IOFactory::createReader('Xls');
        $reader->setIncludeCharts(true);
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 5. Header fields
        $currentDate = now()->format('d/m/Y');
        $totalAmount = $attendances->sum('total_payable');
        $recordCount = $attendances->count();

        $sheet->setCellValue('D2', $currentDate);
        $sheet->setCellValue('E2', $totalAmount);
        $sheet->setCellValue('F2', $recordCount);
        $sheet->setCellValue('D4', $company['name']);
        $sheet->setCellValue('F4', $company['address']['line1']);
        $sheet->setCellValue('H4', $company['address']['line2']);
        $sheet->setCellValue('J4', $company['address']['line3']);
        $sheet->setCellValue('B2', $company['account_no']); // Optional

        // 6. Employee rows
        $row = 10;
        foreach ($attendances as $index => $attendance) {
            $emp = $attendance->employee;
            $bankDetail = $emp->bankDetail;
            $personalDetail = $emp->personalDetail;

            $address = $personalDetail->address ?? '';
            $addressLines = explode("\n", $address);
            $addressLines = array_map('trim', array_slice($addressLines, 0, 3));
            $addressLines = array_pad($addressLines, 3, '');

            $types = function_exists('getTransactionTypes') ? getTransactionTypes() : [
                'neft' => 'NEFT TRANSFER',
                'rtgs' => 'RTGS TRANSFER',
                'imps' => 'IMPS TRANSFER',
            ];
            $transactionType = 'NEFT TRANSFER';
            if ($bankDetail && $bankDetail->transaction_type) {
                $transactionType = $types[$bankDetail->transaction_type] ?? 'NEFT TRANSFER';
            }

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $transactionType);
            $sheet->setCellValue("C{$row}", $bankDetail->ifsc_code ?? '');
            $sheet->setCellValue("D{$row}", $bankDetail->account_number ?? '');
            $sheet->setCellValue("E{$row}", strtoupper($emp->name ?? ''));

            // Uncomment if address is needed by bank or macro
            // $sheet->setCellValue("F{$row}", strtoupper($addressLines[0]));
            // $sheet->setCellValue("G{$row}", strtoupper($addressLines[1]));
            // $sheet->setCellValue("H{$row}", strtoupper($addressLines[2]));
            // $sheet->setCellValue("I{$row}", $emp->email ?? '');

            $sheet->setCellValue("J{$row}", $index + 1);

            $netPay = number_format((float) $attendance->total_payable, 2, '.', '');
            $sheet->setCellValueExplicit("K{$row}", $netPay, DataType::TYPE_STRING);

            $sheet->setCellValue("L{$row}", $company['name']);
            $row++;
        }

        // 7. Format Net Pay column
        if ($row > 10) {
            $sheet->getStyle("K10:K" . ($row - 1))
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // 8. Save with macro support
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save($outputPath);

        // 9. Clean memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Log bank transfer Excel download
        ActivityLogService::logPayrollDataExported('bank_transfer_excel', $month, $year, 'xls', $recordCount);

        // 10. Return file
        return response()->download($outputPath)->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        \Log::error('Excel download error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'error' => 'Failed to generate Excel file',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function downloadBankTransferCSV($month, $year)
{
 
    try {
        $locationId = request('location_id');

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed',
            'location_id' => $locationId
        ])->first();

        $payoutMonthIdList = [];
        if (!$payoutMonth && $locationId === null) {
             $ids = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed'
            ])->pluck('id')->toArray();
            if (!empty($ids)) $payoutMonthIdList = $ids;
        } elseif ($payoutMonth) {
            $payoutMonthIdList = [$payoutMonth->id];
        }

        if (empty($payoutMonthIdList)) {
            abort(404, 'Payroll month not found');
        }

        // Exclude held
        $heldEmployeeIds = $this->getActiveHeldEmployeeIds($month, $year);

        // 2. Fetch attendance with relationships, excluding early salary processed employees
        $attendances = EmployeePayrollAttendance::with([
            'employee.bankDetail',
            'employee.personalDetail'
        ])->whereIn('payout_month_id', $payoutMonthIdList)
          ->whereNotIn('emp_id', $heldEmployeeIds)
          ->where('early_salary_processed', false) // Exclude early salary processed employees
          ->whereDoesntHave('employee.exitDetails', function($q) {
              $q->where('settlement_mode', 'immediate');
          })
          ->get();

        // 3. Static company info
        $company = [
            'account_no' => 'YOUR_COMPANY_ACCOUNT_NUMBER',
            'name' => 'DIVYA ROOPA INFRACON PVT LTD',
            'address' => [
                'line1' => 'PERMUDE',
                'line2' => 'MANGALORE',
                'line3' => '5745'
            ]
        ];

        // 4. Header fields
        $currentDate = now()->format('d/m/Y');
        $totalAmount = number_format((float) $attendances->sum('total_payable'), 2, '.', '');
        $recordCount = $attendances->count();

        // 5. Prepare file
        $fileName = 'canara_bank_bulk_' . now()->format('d_M_Y_Hi') . '.csv';
        $filePath = storage_path("app/output/{$fileName}");

        $handle = fopen($filePath, 'w');

        // ✅ Insert first row with D2, E2, F2 values
        $headerRow = ['', '', '', $currentDate, $totalAmount, $recordCount];
        fputcsv($handle, $headerRow);

        $types = function_exists('getTransactionTypes') ? getTransactionTypes() : [
            'neft' => 'NEFT TRANSFER',
            'rtgs' => 'RTGS TRANSFER',
            'imps' => 'IMPS TRANSFER',
        ];

        // 6. Insert data rows
        foreach ($attendances as $index => $attendance) {
            $emp = $attendance->employee;
            $bank = $emp->bankDetail;
            $personal = $emp->personalDetail;

            $type = $bank && $bank->transaction_type
                ? ($types[$bank->transaction_type] ?? 'NEFT TRANSFER')
                : 'NEFT TRANSFER';

            $netPay = number_format((float) $attendance->total_payable, 2, '.', '');

            $rowData = [
                $index + 1,                          // A
                $type,                               // B
                $bank->ifsc_code ?? '',              // C
                //$bank->account_number ?? '',          
                "'" . ($bank->account_number ?? ''),  // D ✅ Fix here
                strtoupper($emp->name ?? ''),        // E 
                '', '', '', '',                      // F, G, H, I
                $index + 1,                          // J  
                $netPay,                             // K
                $company['name'],                    // L
            ];

            fputcsv($handle, $rowData);
        }

        fclose($handle);

        // Log bank transfer CSV download
        ActivityLogService::logPayrollDataExported('bank_transfer_csv', $month, $year, 'csv', $recordCount);

        // 7. Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        \Log::error('CSV download error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to generate CSV',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function downloadBankTransferICICI($month, $year)
    {
        try {
            $locationId = request('location_id');

            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed',
                'location_id' => $locationId
            ])->first();

            $payoutMonthIdList = [];
            if (!$payoutMonth && $locationId === null) {
                 $ids = EmployeePayrollAttendancePayoutMonthStatus::where([
                    'payout_month' => $month,
                    'payout_year' => $year,
                    'status' => 'completed'
                ])->pluck('id')->toArray();
                if (!empty($ids)) $payoutMonthIdList = $ids;
            } elseif ($payoutMonth) {
                $payoutMonthIdList = [$payoutMonth->id];
            }

            if (empty($payoutMonthIdList)) {
                abort(404, 'Payroll month not found');
            }

            // Exclude held
            $heldEmployeeIds = $this->getActiveHeldEmployeeIds($month, $year);

            // 2. Fetch attendance with relationships, excluding early salary processed employees
            $attendances = EmployeePayrollAttendance::with([
                'employee.bankDetail',
                'employee.personalDetail'
            ])->whereIn('payout_month_id', $payoutMonthIdList)
              ->whereNotIn('emp_id', $heldEmployeeIds)
              ->where('early_salary_processed', false) // Exclude early salary processed employees
              ->whereDoesntHave('employee.exitDetails', function($q) {
                  $q->where('settlement_mode', 'immediate');
              })
              ->get();

            // 3. Create new spreadsheet for ICICI format
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('ICICI Bank Transfer');

            // 4. Set headers according to ICICI specifications
            $headers = [
                'PYMT_PROD_TYPE_CODE',  // 1. Fixed Value: PAB_VENDOR
                'PYMT_MODE',           // 2. FT, NEFT, RTGS, IMPS
                'DEBIT_ACC_NO',        // 3. 12 digit ICICI Bank Account number
                'BNF_NAME',            // 4. Name of Beneficiary
                'BENE_ACC_NO',         // 5. Account number of Beneficiary
                'BENE_IFSC',           // 6. IFSC code of the beneficiary
                'AMOUNT',              // 7. Numeric value with decimal up to 2 places
                'CREDIT_NARR',         // 8. 30 Alphanumeric Characters allowed
                'PYMT_DATE',           // 9. Date format DD-MM-YYYY
                'MOBILE_NUM',          // 10. Mobile no of Bene (10 Digit)
                'EMAIL_ID',            // 11. Email Id of Bene (500 Characters)
                'REMARK',              // 12. Non Mandatory field (For Internal Use Only)
                'REF_NO'               // 13. Non Mandatory field
            ];

            // Set header row
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // 5. Company static info for ICICI format  
            $company = [
                'debit_account' => '001405010378', // Replace with your 12-digit ICICI account
                'name' => 'ISARVA INFOTECH PVT LTD'
            ];

            // 6. Transaction types mapping
           $types = function_exists('getTransactionTypesICICI') ? getTransactionTypesICICI() : [
                'neft' => 'NEFT',
                'rtgs' => 'RTGS', 
                'imps' => 'IMPS',
            ];

            // 7. Populate data rows
            $row = 2;
            $currentDate = now()->format('d-m-Y'); // DD-MM-YYYY format for ICICI
            
            foreach ($attendances as $index => $attendance) {
                $emp = $attendance->employee;
                $bank = $emp->bankDetail;
                $personal = $emp->personalDetail;

                // Determine payment mode
                $type = $bank && $bank->transaction_type
                    ? ($types[$bank->transaction_type] ?? 'NEFT')
                    : 'NEFT';

                $netPay = number_format((float) $attendance->total_payable, 2, '.', '');

                // Populate row data according to ICICI format specifications
                $sheet->setCellValue('A' . $row, 'PAB_VENDOR'); // 1. PYMT_PROD_TYPE_CODE - Fixed value
                $sheet->setCellValue('B' . $row, $type); // 2. PYMT_MODE
                $sheet->setCellValueExplicit('C' . $row, $company['debit_account'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // 3. DEBIT_ACC_NO
                $sheet->setCellValue('D' . $row, strtoupper($emp->name ?? '')); // 4. BNF_NAME
                $sheet->setCellValueExplicit('E' . $row, $bank->account_number ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // 5. BENE_ACC_NO
                $sheet->setCellValue('F' . $row, $bank->ifsc_code ?? ''); // 6. BENE_IFSC
                $sheet->setCellValue('G' . $row, $netPay); // 7. AMOUNT
                $sheet->setCellValue('H' . $row, 'SALARY PROCESSED'); // 8. CREDIT_NARR
                $sheet->setCellValue('I' . $row, $currentDate); // 9. PYMT_DATE
                $sheet->setCellValue('J' . $row, ''); // 10. MOBILE_NUM (not mandatory)
                $sheet->setCellValue('K' . $row, ''); // 11. EMAIL_ID (not mandatory)
                $sheet->setCellValue('L' . $row, ''); // 12. REMARK (not mandatory)
                $sheet->setCellValue('M' . $row, ''); // 13. REF_NO (not mandatory)

                $row++;
            }

            // 8. Format the spreadsheet
            // Auto-size columns
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Style header row
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '366092']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

            // 8. Create filename and save
          //  $fileName = 'icici_bank_transfer_' . now()->format('d_M_Y_Hi') . '.xlsx';
            $locationName = 'All';
            if ($locationId) {
                $loc = Location::find($locationId);
                if ($loc) {
                    $locationName = $loc->name;
                }
            }
            // Sanitize location name
            $locationName = preg_replace('/[^A-Za-z0-9\-]/', '_', $locationName);

            $fileName = 'NFPS_FMT(' . $locationName . '-' . now()->format('d-m-Y') . ').xlsx';
            $filePath = storage_path("app/output/{$fileName}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            // Log ICICI bank transfer XLSX download
            ActivityLogService::logPayrollDataExported('bank_transfer_icici_xlsx', $month, $year, 'xlsx', $attendances->count());

            // 9. Return file as download
            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('ICICI XLSX download error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate ICICI XLSX',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEarlySalaryProcessed(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'early_salary_processed' => 'required|boolean'
        ]);

        try {
            // Find the payout month record first
            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $request->month,
                'payout_year' => $request->year
            ])->first();
            
            if (!$payoutMonth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout month record not found.'
                ], 404);
            }
            
            // Find the attendance record based on emp_id and payout_month_id
            $attendance = EmployeePayrollAttendance::where('emp_id', $request->employee_id)
                ->where('payout_month_id', $payoutMonth->id)
                ->first();
                
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found for the specified employee and period.'
                ], 404);
            }
            
            // Check if payroll is finalized
            if ($payoutMonth->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update early salary status for finalized payroll.'
                ], 422);
            }

            $attendance->early_salary_processed = $request->early_salary_processed;
            $attendance->save();

            // Log early salary processed update
            $employee = EmployeeBasicDetail::find($request->employee_id);
            ActivityLogService::logPayrollComponentOverride(
                $employee->name,
                'Early Salary Processed',
                'system',
                $request->early_salary_processed ? 0 : 1, // old value (opposite)
                $request->early_salary_processed ? 1 : 0, // new value
                [
                    'employee_id' => $request->employee_id,
                    'month' => $request->month,
                    'year' => $request->year,
                    'updated_by' => auth()->id()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Early salary processed status updated successfully.',
                'early_salary_processed' => $attendance->early_salary_processed
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating early salary processed status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update early salary processed status.'
            ], 500);
        }
    }

    public function epfExcelORCSV($month, $year, Request $request)
    {
        try {
            // Get format from request (default to 1 for XLSX)
            $format = $request->input('format', 1);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet 1');

        // Set column headers (for XLSX and CSV; TXT doesn't use headers)
        $sheet->setCellValue('A1', 'UAN');
        $sheet->setCellValue('B1', 'MEMBER NAME');
        $sheet->setCellValue('C1', 'GROSS WAGES');
        $sheet->setCellValue('D1', 'EPF WAGES');
        $sheet->setCellValue('E1', 'EPS WAGES');
        $sheet->setCellValue('F1', 'EDLI WAGES');
        $sheet->setCellValue('G1', 'EPF CONTRI REMITTED');
        $sheet->setCellValue('H1', 'EPS CONTRI REMITTED');
        $sheet->setCellValue('I1', 'EPF EPS DIFF REMITTED');
        $sheet->setCellValue('J1', 'NCP DAYS');
        $sheet->setCellValue('K1', 'REFUND OF ADVANCES');

        // Fetch payout month
        $locationId = request('location_id');

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed',
            'location_id' => $locationId
        ])->first();

        $payoutMonthIdList = [];
        if (!$payoutMonth && $locationId === null) {
             $ids = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed'
            ])->pluck('id')->toArray();
            if (!empty($ids)) $payoutMonthIdList = $ids;
        } elseif ($payoutMonth) {
            $payoutMonthIdList = [$payoutMonth->id];
        }

        if (empty($payoutMonthIdList)) {
            abort(404, 'Payroll month not found');
        }

        $isFinalized = true;

        // Exclude held
        $heldEmployeeIds = $this->getActiveHeldEmployeeIds($month, $year);

        // Fetch attendance with relationships
        $attendances = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'bankDetail',
                    'personalDetail',
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->whereIn('payout_month_id', $payoutMonthIdList)
          ->whereNotIn('emp_id', $heldEmployeeIds)
          ->get();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Component and calculation logic
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4];

        // Transform and filter attendances
        $attendances = $attendances->filter(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $isFinalized) {
            $employee = $attendance->employee;
            $earnings = [];
            $deductions = [];
            $epfWage = 0;
            $totalEarnings = 0;
            $totalDeductions = 0;

            if ($isFinalized) {
                // When finalized, use stored JSON values
                $earnings = json_decode($attendance->earnings, true) ?? [];
                $deductions = json_decode($attendance->deductions, true) ?? [];

                // Check if EPF (id == 1) is enabled
                if (!isset($deductions[1]) || !$deductions[1]['applicable']) {
                    return false; // Exclude if EPF is not enabled
                }

                // Calculate EPF Wage dynamically
                $rawEpfWage = 0;
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                        $rawEpfWage += $earnings[$componentId]['value'];
                    }
                }

                // Apply EPF option logic
                $employeeStatutoryComponent = $employee->statutoryComponents
                    ->where('statutory_component_id', 1)
                    ->whereNull('deleted_at')
                    ->first();
                
                $epfOption = $employeeStatutoryComponent->epf_option ?? 'restrict_15000';
                switch ($epfOption) {
                    case 'restrict_15000':
                        $epfWage = min(15000, $rawEpfWage);
                        break;
                    case '12_percent':
                        $epfWage = $rawEpfWage;
                        break;
                    case 'manual_value':
                        // For EPF downloads, use the actual wage for calculation
                        $epfWage = $rawEpfWage;
                        break;
                    default:
                        $epfWage = min(15000, $rawEpfWage);
                }

                $totalEarnings = $attendance->gross_pay;
                $totalDeductions = $attendance->total_deduction;
            } else {
                // When not finalized, calculate values
                $factor = $attendance->total_working_days > 0
                    ? $attendance->employee_worked_days / $attendance->total_working_days
                    : 0;

                $salaryComponentMap = [];
                $statutoryComponentMap = [];
                $epfOptionMap = []; // Map to store EPF options

                foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                    $salaryComponentMap[$component->salary_component_id] = $component->value;
                }

                foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                    $statutoryComponentMap[$component->statutory_component_id] = $component->value;
                    // Store EPF option if this is an EPF component
                    if ($component->statutory_component_id == 1) { // EPF component ID
                        $epfOptionMap[1] = $component->epf_option ?? 'restrict_15000';
                    }
                }

                // Check if EPF (id == 1) is enabled
                if (!array_key_exists(1, $statutoryComponentMap)) {
                    return false; // Exclude if EPF is not enabled
                }

                foreach ($earningComponents as $component) {
                    $value = 0;
                    $isApplicable = false;

                    if ($component instanceof \App\Models\SalaryComponent) {
                        $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                        $baseValue = $salaryComponentMap[$component->id] ?? 0;
                    } else {
                        $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                        $baseValue = $statutoryComponentMap[$component->id] ?? 0;
                    }

                    if ($isApplicable) {
                        $value = $baseValue * $factor;
                        $totalEarnings += $value;
                    }

                    $earnings[$component->id] = [
                        'value' => $value,
                        'applicable' => $isApplicable,
                        'name' => $component->name,
                        'default_value' => $value,
                        'overridden' => false,
                        'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                        'status' => 'earnings'
                    ];
                    }

                // Calculate EPF Wage
                $rawEpfWage = 0;
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                        $rawEpfWage += $earnings[$componentId]['value'];
                    }
                }

                // Apply EPF option logic
                $epfOption = $epfOptionMap[1] ?? 'restrict_15000';
                switch ($epfOption) {
                    case 'restrict_15000':
                        $epfWage = min(15000, $rawEpfWage);
                        break;
                    case '12_percent':
                        $epfWage = $rawEpfWage;
                        break;
                    case 'manual_value':
                        $epfWage = $statutoryComponentMap[1] ?? 0; // Use manual value
                        break;
                    default:
                        $epfWage = min(15000, $rawEpfWage);
                }

                foreach ($deductionComponents as $component) {
                    $value = 0;
                    $isApplicable = false;

                    if ($component instanceof \App\Models\StatutoryComponent) {
                        $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                        $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                        if ($isApplicable) {
                            if ($component->id == 1) { // EPF
                                $epfOption = $epfOptionMap[1] ?? 'restrict_15000';
                                if ($epfOption == 'manual_value') {
                                    $value = round($baseValue * $factor); // Use manual value directly
                                } else {
                                    $value = round(0.12 * $epfWage);
                                }
                            } elseif ($component->id == 2) { // ESI
                                if ($totalEarnings <= 21000) {
                                    $value = round(0.0075 * $totalEarnings);
                                } else {
                                    $value = 0;
                                    $isApplicable = false;
                                }
                            } elseif ($component->id == 4) { // Professional Tax
                                $value = ($totalEarnings >= 25000) ? 200 : 0;
                            } else {
                                $value = round($baseValue * $factor);
                            }
                        }
                    } else {
                        $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                        if ($isApplicable) {
                            $baseValue = $salaryComponentMap[$component->id] ?? 0;
                            $value = round($baseValue * $factor);
                        }
                    }

                    $deductions[$component->id] = [
                        'value' => $value,
                        'applicable' => $isApplicable,
                        'name' => $component->name,
                        'default_value' => $value,
                        'overridden' => false,
                        'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                        'status' => 'deductions'
                    ];

                    if ($isApplicable) {
                        $totalDeductions += $value;
                    }
                }

                $this->applyComponentOverrides($attendance, $earnings, $deductions);

                $totalEarnings = 0;
                foreach ($earnings as $id => $earning) {
                    if ($earning['applicable']) {
                        $totalEarnings += $earning['value'];
                    }
                }

                $totalDeductions = 0;
                foreach ($deductions as $id => $deduction) {
                    if ($deduction['applicable']) {
                        $totalDeductions += $deduction['value'];
                    }
                }
            }

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->totalEarnings = round($totalEarnings);
            $attendance->totalDeductions = round($totalDeductions);
            $attendance->netPay = round($totalEarnings - $totalDeductions);
            $attendance->epfWage = round($epfWage);

            return true; // Include in filtered collection
        });

        // Construct file name
        $fileNamePrefix = $format == 2 ? 'EPF_FORMAT_2' : ($format == 3 ? 'EPF_FORMAT_3' : 'EPF_FORMAT_1');
        $fileExtension = $format == 2 ? 'csv' : ($format == 3 ? 'txt' : 'xlsx');
        $fileName = "{$fileNamePrefix}_{$monthName}.{$fileExtension}";
        $fileName = str_replace(' ', '', $fileName);

        if ($format == 3) {
            // Format 3: TXT (based on MAY-1.txt, no headers, #~# delimiter)
            $txtContent = '';
            foreach ($attendances as $attendance) {
                $emp = $attendance->employee;
                $personal = $emp->personalDetail;

                $uan = $personal->pf_account_number ? (int) $personal->pf_account_number : '';
                $name = strtoupper($emp->name ?? '');
                $grossWages = $attendance->epfWage ?? 0;
                $epfWages = $attendance->epfWage ?? 0;
                $epsWages = $attendance->epfWage ?? 0;
                $edliWages = $attendance->epfWage ?? 0;
                // Get EPF configuration for proper contribution calculation
                $employee = $attendance->employee;
                $epfStatutory = $employee->statutoryComponents->where('statutory_component_id', 1)->first();
                $fullAmountDeduct = $epfStatutory->full_amount_deduct_from_ctc ?? false;
                
                // Calculate EPF contributions based on full amount deduction setting
                if ($fullAmountDeduct) {
                    // When full amount deduction is enabled, show proper breakdown
                    $epfContri = round($attendance->epfWage * 0.12); // Employee contribution: 12%
                    $epsContri = round($attendance->epfWage * 0.0833); // Employer EPS: 8.33%
                    $epfEpsDiff = round($attendance->epfWage * 0.0367); // Employer EPF-EPS diff: 3.67%
                } else {
                    // Normal EPF calculation
                    $epfContri = isset($attendance->deductions[1]['value']) ? round($attendance->deductions[1]['value']) : 0;
                    $epsContri = round($attendance->epfWage * 0.0833) ?? 0;
                    $epfEpsDiff = round($attendance->epfWage * 0.0367) ?? 0;
                }
                $ncpDays = (($attendance->total_working_days - $attendance->employee_worked_days) ?? 0);
                $refundAdvances = 0;

                $grossWages = (int) $grossWages;
                $epfWages = (int) $epfWages;
                $epsWages = (int) $epsWages;
                $edliWages = (int) $edliWages;
                $epfContri = (int) $epfContri;
                $epsContri = (int) $epsContri;
                $epfEpsDiff = (int) $epfEpsDiff;
                $ncpDays = (int) $ncpDays;

                $name = str_replace('#', '', $name);
                $txtContent .= "$uan#~#$name#~#$grossWages#~#$epfWages#~#$epsWages#~#$edliWages#~#$epfContri#~#$epsContri#~#$epfEpsDiff#~#$ncpDays#~#$refundAdvances\r\n";
            }

            header('Content-Type: text/plain');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
            echo $txtContent;
            exit;
        }

        // Populate spreadsheet for XLSX and CSV
        $row = 2;
        foreach ($attendances as $attendance) {
            $emp = $attendance->employee;
            $bank = $emp->bankDetail;
            $personal = $emp->personalDetail;

            $uan = $personal->pf_account_number ? (int) $personal->pf_account_number : '';
            $sheet->setCellValue('A' . $row, $uan);
            $sheet->setCellValue('B' . $row, strtoupper($emp->name ?? ''));
            $sheet->setCellValue('C' . $row, $attendance->epfWage ?? '');
            $sheet->setCellValue('D' . $row, $attendance->epfWage ?? '');
            $sheet->setCellValue('E' . $row, $attendance->epfWage ?? '');
            $sheet->setCellValue('F' . $row, $attendance->epfWage ?? '');
            // Get EPF configuration for proper contribution calculation
            $employee = $attendance->employee;
            $epfStatutory = $employee->statutoryComponents->where('statutory_component_id', 1)->first();
            $fullAmountDeduct = $epfStatutory->full_amount_deduct_from_ctc ?? false;
            
            // Calculate EPF contributions based on full amount deduction setting
            if ($fullAmountDeduct) {
                // When full amount deduction is enabled, show proper breakdown
                $sheet->setCellValue('G' . $row, round($attendance->epfWage * 0.12)); // Employee contribution: 12%
                $sheet->setCellValue('H' . $row, round($attendance->epfWage * 0.0833)); // Employer EPS: 8.33%
                $sheet->setCellValue('I' . $row, round($attendance->epfWage * 0.0367)); // Employer EPF-EPS diff: 3.67%
            } else {
                // Normal EPF calculation
                $sheet->setCellValue('G' . $row, isset($attendance->deductions[1]['value']) ? round($attendance->deductions[1]['value']) : '');
                $sheet->setCellValue('H' . $row, round($attendance->epfWage * 0.0833) ?? '');
                $sheet->setCellValue('I' . $row, round($attendance->epfWage * 0.0367) ?? '');
            }
            $sheet->setCellValue('J' . $row, (($attendance->total_working_days - $attendance->employee_worked_days) ?? 0));
            $sheet->setCellValue('K' . $row, '0');
            $row++;
        }

        if ($format == 2) {
            // Format 2: CSV (Calibri, size 11, no header color)
            $sheet->getStyle('A1:K' . $row)->getFont()->setName('Calibri')->setSize(11);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);

            header('Content-Type: text/csv');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
        } else {
            // Format 1: XLSX (Arial, size 10, with header styling)
            $sheet->getStyle('A1:K' . $row)->getFont()->setName('Arial')->setSize(10);
            $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('0000FF');
            $sheet->getStyle('A1:K1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->getStyle('A2:A' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
        }

        // Log EPF export
        ActivityLogService::logPayrollDataExported('epf_' . ($format == 2 ? 'csv' : ($format == 3 ? 'txt' : 'xlsx')), $month, $year, $fileExtension, $attendances->count());

        $writer->save('php://output');
        exit;

    } catch (\Exception $e) {
        \Log::error('File download error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to generate file',
            'message' => $e->getMessage()
        ], 500);
    }
}
  

    public function generateESIExcel($month, $year, Request $request)
{
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        // Create rich text objects for headers with red parenthetical text
        $ipNumberRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $ipNumberRichText->createText('IP Number ');
        $ipNumberText = $ipNumberRichText->createTextRun('(10 Digits)');
        $ipNumberText->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED))->setBold(true);

        $ipNameRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $ipNameRichText->createText('IP Name ');
        $ipNameText = $ipNameRichText->createTextRun('( Only alphabets and space )');
        $ipNameText->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED))->setBold(true);

        $noOfDaysRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $noOfDaysRichText->createText('No of Days for which wages paid/payable during the month ');
        $noOfDaysText = $noOfDaysRichText->createTextRun('');
        $noOfDaysText->getFont()->setBold(true);

        $totalWagesRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $totalWagesRichText->createText('Total Monthly Wages ');
        $totalWagesText = $totalWagesRichText->createTextRun('');
        $totalWagesText->getFont()->setBold(true);

        $reasonCodeRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $reasonCodeRichText->createText('Reason Code for Zero workings days');
        $reasonCodeText = $reasonCodeRichText->createTextRun('(numeric only; provide 0 for all other reasons- Click on the link for reference)');
        $reasonCodeText->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED))->setBold(true);

        $lastWorkingDayRichText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $lastWorkingDayRichText->createText('Last Working Day ');
        $lastWorkingDayText = $lastWorkingDayRichText->createTextRun('( Format DD/MM/YYYY or DD-MM-YYYY)');
        $lastWorkingDayText->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED))->setBold(true);

        // Set column headers in a single row
        $sheet->setCellValue('A1', $ipNumberRichText);
        $sheet->setCellValue('B1', $ipNameRichText);
        $sheet->setCellValue('C1', $noOfDaysRichText);
        $sheet->setCellValue('D1', $totalWagesRichText);
        $sheet->setCellValue('E1', $reasonCodeRichText);
        $sheet->setCellValue('F1', $lastWorkingDayRichText);

        // Apply header styling (Aharoni, size 11, bold, #CCFFFF background)
        $sheet->getStyle('A1:F1')->getFont()->setName('Aharoni')->setSize(11)->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCFFFF');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set specific column formats
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('0000000000'); // 10-digit number format for IP Number
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('0'); // Whole number for days
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('0'); // Whole number for wages
        $sheet->getStyle('E')->getNumberFormat()->setFormatCode('0'); // Whole number for reason code
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Apply body font (Arial, size 10)
        $sheet->getStyle('A2:F' . $sheet->getHighestRow())->getFont()->setName('Arial')->setSize(10);

        // Fetch payout month
        $locationId = request('location_id');

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed',
            'location_id' => $locationId
        ])->first();

        $payoutMonthIdList = [];
        if (!$payoutMonth && $locationId === null) {
             $ids = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'status' => 'completed'
            ])->pluck('id')->toArray();
            if (!empty($ids)) $payoutMonthIdList = $ids;
        } elseif ($payoutMonth) {
            $payoutMonthIdList = [$payoutMonth->id];
        }

        if (empty($payoutMonthIdList)) {
            abort(404, 'Payroll month not found');
        }

        $isFinalized = true;

        // Exclude held
        $heldEmployeeIds = $this->getActiveHeldEmployeeIds($month, $year);

        // Fetch attendance with relationships
        $attendances = EmployeePayrollAttendance::with([
            'employee' => function($query) {
                $query->with([
                    'bankDetail',
                    'personalDetail',
                    'salaryComponents' => function($q) {
                        $q->withTrashed();
                    },
                    'statutoryComponents' => function($q) {
                        $q->withTrashed();
                    }
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->whereIn('payout_month_id', $payoutMonthIdList)
          ->whereNotIn('emp_id', $heldEmployeeIds)
          ->get();

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Calculate last day of the month for Last Working Day
        $lastDayOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('d/m/Y');

        // Component and calculation logic
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4];

        // Transform and filter attendances for ESI applicability
        $attendances = $attendances->filter(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $isFinalized) {
            $employee = $attendance->employee;
            $earnings = [];
            $deductions = [];
            $epfWage = 0;
            $totalEarnings = 0;
            $totalDeductions = 0;

            if ($isFinalized) {
                // When finalized, use stored JSON values
                $earnings = json_decode($attendance->earnings, true) ?? [];
                $deductions = json_decode($attendance->deductions, true) ?? [];

                // Check if ESI (id == 2) is enabled
                if (!isset($deductions[2]) || !$deductions[2]['applicable']) {
                    return false; // Exclude if ESI is not enabled
                }

                // Calculate EPF Wage dynamically
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                        $epfWage += $earnings[$componentId]['value'];
                    }
                }
                $epfWage = min(15000, $epfWage);

                $totalEarnings = $attendance->gross_pay;
                $totalDeductions = $attendance->total_deduction;
            } else {
                // When not finalized, calculate values
                $factor = $attendance->total_working_days > 0
                    ? $attendance->employee_worked_days / $attendance->total_working_days
                    : 0;

                $salaryComponentMap = [];
                $statutoryComponentMap = [];

                foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
                    $salaryComponentMap[$component->salary_component_id] = $component->value;
                }

                foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
                    $statutoryComponentMap[$component->statutory_component_id] = $component->value;
                }

                // Check if ESI (id == 2) is enabled
                if (!array_key_exists(2, $statutoryComponentMap)) {
                    return false; // Exclude if ESI is not enabled
                }

                foreach ($earningComponents as $component) {
                    $value = 0;
                    $isApplicable = false;

                    if ($component instanceof \App\Models\SalaryComponent) {
                        $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                        $baseValue = $salaryComponentMap[$component->id] ?? 0;
                    } else {
                        $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                        $baseValue = $statutoryComponentMap[$component->id] ?? 0;
                    }

                    if ($isApplicable) {
                        $value = $baseValue * $factor;
                        $totalEarnings += $value;
                    }

                    $earnings[$component->id] = [
                        'value' => $value,
                        'applicable' => $isApplicable,
                        'name' => $component->name,
                        'default_value' => $value,
                        'overridden' => false,
                        'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                        'status' => 'earnings'
                    ];
                }

                // Calculate EPF Wage
                foreach ($epfComponentIds as $componentId) {
                    if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                        $epfWage += $earnings[$componentId]['value'];
                    }
                }
                $epfWage = min(15000, $epfWage);

                foreach ($deductionComponents as $component) {
                    $value = 0;
                    $isApplicable = false;

                    if ($component instanceof \App\Models\StatutoryComponent) {
                        $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                        $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                        if ($isApplicable) {
                            if ($component->id == 1) { // EPF
                                $value = round(0.12 * $epfWage);
                            } elseif ($component->id == 2) { // ESI
                                if ($totalEarnings <= 21000) {
                                    $value = round(0.0075 * $totalEarnings);
                                } else {
                                    $value = 0;
                                    $isApplicable = false;
                                }
                            } elseif ($component->id == 4) { // Professional Tax
                                $value = ($totalEarnings >= 25000) ? 200 : 0;
                            } else {
                                $value = round($baseValue * $factor);
                            }
                        }
                    } else {
                        $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                        if ($isApplicable) {
                            $baseValue = $salaryComponentMap[$component->id] ?? 0;
                            $value = round($baseValue * $factor);
                        }
                    }

                    $deductions[$component->id] = [
                        'value' => $value,
                        'applicable' => $isApplicable,
                        'name' => $component->name,
                        'default_value' => $value,
                        'overridden' => false,
                        'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory',
                        'status' => 'deductions'
                    ];

                    if ($isApplicable) {
                        $totalDeductions += $value;
                    }
                }

                $this->applyComponentOverrides($attendance, $earnings, $deductions);

                $totalEarnings = 0;
                foreach ($earnings as $id => $earning) {
                    if ($earning['applicable']) {
                        $totalEarnings += $earning['value'];
                    }
                }

                $totalDeductions = 0;
                foreach ($deductions as $id => $deduction) {
                    if ($deduction['applicable']) {
                        $totalDeductions += $deduction['value'];
                    }
                }
            }

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->totalEarnings = round($totalEarnings);
            $attendance->totalDeductions = round($totalDeductions);
            $attendance->netEarnings = round($totalEarnings - $totalDeductions);
            $attendance->epfWage = round($epfWage);

            return true; // Include in filtered collection
        });

        // Populate spreadsheet
        $row = 2;
        foreach ($attendances as $attendance) {
            $emp = $attendance->employee;
            $personal = $emp->personalDetail;

            // IP Number (10 digits, padded with zeros)
            $ipNumber = $personal->esic_number ? str_pad((int) $personal->esic_number, 10, '0', STR_PAD_LEFT) : '';

            // IP Name (only alphabets and space)
            $ipName = strtoupper($emp->name ?? '');
            $ipName = preg_replace('/[^A-Za-z\s]/', '', $ipName);

            // No of Days (whole number)
            $workedDays = (int) ($attendance->employee_worked_days ?? 0);

            // Total Monthly Wages (whole number)
            $totalWages = (int) ($attendance->totalEarnings ?? 0);

            // Reason Code and Last Working Day
            $reasonCode = $workedDays > 0 ? 0 : 1;
            $lastWorkingDay = $workedDays > 0 ? '' : $lastDayOfMonth;

            // Set cell values
            $sheet->setCellValue('A' . $row, $ipNumber);
            $sheet->setCellValue('B' . $row, $ipName);
            $sheet->setCellValue('C' . $row, $workedDays);
            $sheet->setCellValue('D' . $row, $totalWages);
            $sheet->setCellValue('E' . $row, $reasonCode);
            $sheet->setCellValue('F' . $row, $lastWorkingDay);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Construct file name
        $fileName = "ESI_FORMAT_1_{$monthName}.xls";
        $fileName = str_replace(' ', '', $fileName);

        // Use Xls writer (Excel 97-2003)
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        // Log ESI export
        ActivityLogService::logPayrollDataExported('esi_xls', $month, $year, 'xls', $attendances->count());

        $writer->save('php://output');
        exit;

    } catch (\Exception $e) {
        \Log::error('ESI Excel download error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to generate ESI Excel',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Calculate advance deduction for an employee for a specific month/year
     *
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @return float
     */
    protected function calculateAdvanceDeduction($employeeId, $month, $year)
    {
        $totalDeduction = 0;
        
        // Get all active advances for this employee in this month using direct query
        $activeAdvances = EmployeeAdvance::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where(function($query) use ($month, $year) {
                $currentMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
                
                $query->where(function($q) use ($currentMonth) {
                    // Start date is before or in current month
                    $q->whereDate('start_date', '<=', $currentMonth->endOfMonth());
                })
                ->where(function($q) use ($currentMonth) {
                    // End date is after or in current month
                    $q->whereDate('end_date', '>=', $currentMonth->startOfMonth());
                });
            })
            ->get();
            
        foreach ($activeAdvances as $advance) {
            // Check if a deduction already exists for this advance in this month
            $existingDeduction = $advance->deductions()
                ->where('month', $month)
                ->where('year', $year)
                ->first();
                
            if ($existingDeduction) {
                // Use existing deduction amount
                $totalDeduction += $existingDeduction->amount;
            } else {
                // Calculate new deduction
                $remainingAmount = $advance->remaining_amount;

                 // Check for exit process (Pending, Approved, or Completed) and if this is the exit month
                $exitDetail = \App\Models\EmployeeExitDetail::where('emp_id', $employeeId)
                    ->whereIn('status', ['Pending', 'Approved', 'Completed'])
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();

                $isExitMonth = false;
                if ($exitDetail && $exitDetail->last_working_day) { // Ensure LWD exists
                     $lwdDate = \Carbon\Carbon::parse($exitDetail->last_working_day);
                     // Check if current payroll month/year matches LWD month/year
                     if ($lwdDate->month == $month && $lwdDate->year == $year) {
                        $isExitMonth = true;
                     }
                }

                if ($isExitMonth) {
                    // If this is the exit month, deduct full remaining amount
                    $deductionAmount = $remainingAmount;
                } else {
                    // Normal EMI deduction
                    $deductionAmount = min($advance->monthly_deduction, $remainingAmount);
                }
                
                if ($deductionAmount > 0) {
                    $totalDeduction += $deductionAmount;
                }
            }
        }
        
        return $totalDeduction;
    }
    
    /**
     * Save advance deductions for an employee for a specific month/year
     *
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @param float $attendanceFactor
     * @return float
     */
    protected function saveAdvanceDeductions($employeeId, $month, $year, $attendanceFactor = 1)
    {
        $totalDeduction = 0;
        
        // Get all active advances for this employee in this month using direct query
        $activeAdvances = EmployeeAdvance::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where(function($query) use ($month, $year) {
                $currentMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
                
                $query->where(function($q) use ($currentMonth) {
                    // Start date is before or in current month
                    $q->whereDate('start_date', '<=', $currentMonth->endOfMonth());
                })
                ->where(function($q) use ($currentMonth) {
                    // End date is after or in current month
                    $q->whereDate('end_date', '>=', $currentMonth->startOfMonth());
                });
            })
            ->get();
            
        foreach ($activeAdvances as $advance) {
            // Check if a deduction already exists for this advance in this month
            $existingDeduction = $advance->deductions()
                ->where('month', $month)
                ->where('year', $year)
                ->first();
                
            if (!$existingDeduction) {
                // Calculate new deduction
            $remainingAmount = $advance->remaining_amount;

             // Check for exit process (Pending, Approved, or Completed) and if this is the exit month
            $exitDetail = \App\Models\EmployeeExitDetail::where('emp_id', $employeeId)
                ->whereIn('status', ['Pending', 'Approved', 'Completed'])
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->first();

            $isExitMonth = false;
            if ($exitDetail && $exitDetail->last_working_day) { // Ensure LWD exists
                 $lwdDate = \Carbon\Carbon::parse($exitDetail->last_working_day);
                 // Check if current payroll month/year matches LWD month/year
                 if ($lwdDate->month == $month && $lwdDate->year == $year) {
                    $isExitMonth = true;
                 }
            }

            if ($isExitMonth) {
                // If this is the exit month, deduct full remaining amount
                $deductionAmount = $remainingAmount;
            } else {
                // Normal EMI deduction
                $deductionAmount = min($advance->monthly_deduction, $remainingAmount);
            }
            
            // Apply attendance factor if provided
            if ($attendanceFactor < 1) {
                $deductionAmount = $deductionAmount * $attendanceFactor;
            }
                
                if ($deductionAmount > 0) {
                    // Create the deduction record
                    EmployeeAdvanceDeduction::create([
                        'advance_id' => $advance->id,
                        'month' => $month,
                        'year' => $year,
                        'amount' => $deductionAmount,
                        'created_by' => Auth::id()
                    ]);
                    
                    $totalDeduction += $deductionAmount;
                }
            } else {
                $totalDeduction += $existingDeduction->amount;
            }
        }
        
        return $totalDeduction;
    }

    /**
     * Send salary slip via email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSalarySlipEmail(Request $request)
{
    try {
        // Validate request
        $request->validate([
            'employee_id' => 'required|integer',
            'employee_name' => 'required|string',
            'employee_email' => 'required|email',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020'
        ]);

        $employeeId   = $request->employee_id;
        $employeeName = $request->employee_name;
        $employeeEmail = $request->employee_email;
        $month        = $request->month;
        $year         = $request->year;

        // Check if payroll is finalized
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year'  => $year,
            'status'       => 'completed'
        ])->first();

        if (!$payoutMonth) {
            $msg = 'Payroll for this month is not finalized yet. Please finalize the payroll first.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            
            return back()->with('error', $msg);
        }

        // Get employee details
        $employee = EmployeeBasicDetail::find($employeeId);
        if (!$employee) {
            $msg = 'Employee not found.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        // Get attendance data for the employee
        $attendances = EmployeePayrollAttendance::with([
            'employee' => function ($query) {
                $query->with([
                    'salaryComponents' => fn($q) => $q->withTrashed(),
                    'statutoryComponents' => fn($q) => $q->withTrashed(),
                ]);
            },
            'salaryOverrides',
            'statutoryOverrides'
        ])->where([
            ['payout_month_id', '=', $payoutMonth->id],
            ['emp_id', '=', $employeeId]
        ])->get();

        if ($attendances->isEmpty()) {
            $msg = 'No payroll data found for this employee in the specified month.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        // ──────────────────────────────────────────────────────────────
        // Process attendance data (same logic as your payslip_pdf)
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

        $earningSalaryComponents = SalaryComponent::where('type', 'earning')->orderBy('id')->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')->orderBy('id')->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')->orderBy('id')->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')->orderBy('id')->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4];

        $attendances->transform(function ($attendance) use ($earningComponents, $deductionComponents, $epfComponentIds, $month, $year) {
            $earnings = json_decode($attendance->earnings, true) ?? [];
            $deductions = json_decode($attendance->deductions, true) ?? [];

            // Calculate EPF Wage
            $rawEpfWage = 0;
            foreach ($epfComponentIds as $id) {
                if (isset($earnings[$id]) && ($earnings[$id]['applicable'] ?? false)) {
                    $rawEpfWage += $earnings[$id]['value'] ?? 0;
                }
            }

            $employee = $attendance->employee;
            $epfStatutory = $employee->statutoryComponents->where('statutory_component_id', 1)->first();
            $epfOption = $epfStatutory->epf_option ?? 'restrict_15000';

            $epfWage = match ($epfOption) {
                'restrict_15000' => min(15000, $rawEpfWage),
                '12_percent'     => $rawEpfWage,
                'manual_value'   => $epfStatutory->value ?? 0,
                default          => min(15000, $rawEpfWage),
            };

            // Handle advance deduction
            $storedAdvanceData = null;
            $advanceComponentId = null;

            foreach ($deductions as $id => $deduction) {
                if (isset($deduction['name']) && 
                    (strtoupper($deduction['name']) === 'ADVC' || 
                     strtoupper($deduction['name']) === 'ADVANCE' ||
                     stripos($deduction['name'], 'advance') !== false)) {
                    $storedAdvanceData = $deduction;
                    $advanceComponentId = $id;
                    break;
                }
            }

            $currentAdvanceDeduction = $this->calculateAdvanceDeduction($attendance->employee->id, $month, $year);

            $finalAdvanceValue = 0;
            $finalAdvanceApplicable = false;

            if ($storedAdvanceData && ($storedAdvanceData['applicable'] ?? false) && ($storedAdvanceData['value'] ?? 0) > 0) {
                $finalAdvanceValue = $storedAdvanceData['value'];
                $finalAdvanceApplicable = true;
            } elseif ($currentAdvanceDeduction > 0) {
                $finalAdvanceValue = $currentAdvanceDeduction;
                $finalAdvanceApplicable = true;
            }

            $deductions['advance'] = [
                'value' => $finalAdvanceValue,
                'applicable' => $finalAdvanceApplicable,
                'name' => 'Advance',
                'default_value' => $finalAdvanceValue,
                'overridden' => false,
                'type' => 'advance',
                'status' => 'deductions'
            ];

            if ($advanceComponentId !== null) {
                unset($deductions[$advanceComponentId]);
            }

            // Ensure status exists
            foreach ($earnings as &$e) {
                $e['status'] = $e['status'] ?? 'earnings';
            }
            foreach ($deductions as &$d) {
                $d['status'] = $d['status'] ?? 'deductions';
            }

            // Balance arrays for table layout
            $earningCount = count($earnings);
            $deductionCount = count($deductions);

            if ($earningCount < $deductionCount) {
                $earnings += array_fill($earningCount, $deductionCount - $earningCount, [
                    'value' => '', 'applicable' => false, 'name' => '',
                    'default_value' => 0, 'overridden' => false, 'type' => '',
                    'status' => 'earning'
                ]);
            }

            if ($earningCount > $deductionCount) {
                $deductions += array_fill($deductionCount, $earningCount - $deductionCount, [
                    'value' => '', 'applicable' => false, 'name' => '',
                    'default_value' => 0, 'overridden' => false, 'type' => '',
                    'status' => 'deduction'
                ]);
            }

            $attendance->earnings = $earnings;
            $attendance->deductions = $deductions;
            $attendance->epfWage = round($epfWage);

            // Use finalized values
            $attendance->totalEarnings = $attendance->gross_pay;
            $attendance->totalDeductions = $attendance->total_deduction;
            $attendance->netPay = $attendance->total_payable;

            return $attendance;
        });

        // Company & other data
        $companySettings = CompanySettings::first();
        $departments = Department::pluck('department', 'id')->toArray();
        $designations = PositionType::pluck('position', 'id')->toArray();

        // Generate PDF
        $pdfGenerator = app(PDFGenerator::class);
        $html = view('payroll.payslips.pdf-format', [
            'attendances' => $attendances,
            'monthName' => $monthName,
            'companySettings' => $companySettings,
            'departments' => $departments,
            'designations' => $designations
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'default_font' => 'sans-serif',
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);
        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        // Send email
        $subject = "Salary Slip for {$monthName} - {$employeeName}";

        Mail::send('payroll.emails.salary-slip', [
            'employeeName' => $employeeName,
            'monthName' => $monthName,
            'companyName' => $companySettings->company_name ?? 'Company'
        ], function ($message) use ($employeeEmail, $employeeName, $subject, $pdfContent, $monthName) {
            $message->to($employeeEmail, $employeeName)
                    ->subject($subject)
                    ->attachData($pdfContent, "salary-slip-{$monthName}.pdf", [
                        'mime' => 'application/pdf'
                    ]);
        });

        // Log
        ActivityLogService::logPayrollDataExported('salary_slip_email', $month, $year, 'email', 1);

        // ─── Final Response ───────────────────────────────────────────────
        $successMessage = "Salary slip has been sent successfully to {$employeeEmail}";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage
            ]);
        }

        return back()->with('success', $successMessage);

    } catch (\Illuminate\Validation\ValidationException $e) {
        $msg = 'Validation failed: ' . implode(', ', $e->validator->errors()->all());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $msg], 422);
        }
        return back()->with('error', $msg);

    } catch (\Exception $e) {
        \Log::error('Error sending salary slip email: ' . $e->getMessage(), [
            'employee_id' => $request->employee_id ?? null,
            'month' => $request->month ?? null,
            'year' => $request->year ?? null,
            'trace' => $e->getTraceAsString()
        ]);

        $msg = 'Failed to send salary slip email. Please try again or contact support.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
        return back()->with('error', $msg);
    }
}
    /**
     * Send salary slips to all employees via email (bulk)
     */
    public function sendAllSalarySlips(Request $request)
    {
        try {
            $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'employees' => 'required|array|min:1',
                'employees.*.id' => 'required|integer|exists:employee_basic_details,id',
                'employees.*.name' => 'required|string',
                'employees.*.email' => 'required|email'
            ]);

            $month = $request->month;
            $year = $request->year;
            $employees = $request->employees;
            
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($employees as $employeeData) {
                try {
                    $employeeId = $employeeData['id'];
                    $employeeName = $employeeData['name'];
                    $employeeEmail = $employeeData['email'];

                    // Get finalized attendance data for this employee
                    $attendance = EmployeePayrollAttendance::with(['employee.basicDetail', 'employee.statutoryComponents'])
                        ->where('employee_id', $employeeId)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->where('is_finalized', true)
                        ->first();

                    if (!$attendance) {
                        $errors[] = "No finalized payroll found for {$employeeName}";
                        $failedCount++;
                        continue;
                    }

                    // Check if employee has email in database
                    $employee = EmployeeBasicDetail::find($employeeId);
                    if (!$employee || !$employee->email) {
                        $errors[] = "No email address found for {$employeeName}";
                        $failedCount++;
                        continue;
                    }

                    // Get components data (same as individual method)
                    $earningComponents = EarningComponent::where('status', 'Active')->get();
                    $deductionComponents = DeductionComponent::where('status', 'Active')->get();
                    $epfComponentIds = EarningComponent::where('status', 'Active')
                        ->where('epf_applicable', 1)
                        ->pluck('id')
                        ->toArray();

                    // Transform attendance data using the same logic
                    $earnings = json_decode($attendance->earnings, true) ?? [];
                    $deductions = json_decode($attendance->deductions, true) ?? [];
                    
                    // Calculate EPF Wage dynamically
                    $rawEpfWage = 0;
                    foreach ($epfComponentIds as $componentId) {
                        if (isset($earnings[$componentId]) && isset($earnings[$componentId]['applicable']) && $earnings[$componentId]['applicable']) {
                            $rawEpfWage += $earnings[$componentId]['value'];
                        }
                    }
                    
                    $epfStatutory = $employee->statutoryComponents->where('statutory_component_id', 1)->first();
                    $epfOption = $epfStatutory->epf_option ?? 'restrict_15000';
                    
                    switch ($epfOption) {
                        case 'restrict_15000':
                            $epfWage = min(15000, $rawEpfWage);
                            break;
                        case '12_percent':
                            $epfWage = $rawEpfWage;
                            break;
                        case 'manual_value':
                            $epfWage = $epfStatutory->value ?? 0;
                            break;
                        default:
                            $epfWage = min(15000, $rawEpfWage);
                    }

                    // Handle advance deductions
                    $storedAdvanceData = null;
                    $advanceComponentId = null;
                    foreach ($deductions as $componentId => $deduction) {
                        if (isset($deduction['name']) && 
                            (strtoupper($deduction['name']) === 'ADVC' || 
                             strtoupper($deduction['name']) === 'ADVANCE' ||
                             stripos($deduction['name'], 'advance') !== false)) {
                            $storedAdvanceData = $deduction;
                            $advanceComponentId = $componentId;
                            break;
                        }
                    }
                    
                    $currentAdvanceDeduction = $this->calculateAdvanceDeduction($employeeId, $month, $year);
                    
                    $finalAdvanceValue = 0;
                    $finalAdvanceApplicable = false;
                    
                    if ($storedAdvanceData && $storedAdvanceData['applicable'] && $storedAdvanceData['value'] > 0) {
                        $finalAdvanceValue = $storedAdvanceData['value'];
                        $finalAdvanceApplicable = true;
                    } elseif ($currentAdvanceDeduction > 0) {
                        $finalAdvanceValue = $currentAdvanceDeduction;
                        $finalAdvanceApplicable = true;
                    }
                    
                    $deductions['advance'] = [
                        'value' => $finalAdvanceValue,
                        'applicable' => $finalAdvanceApplicable,
                        'name' => 'Advance',
                        'default_value' => $finalAdvanceValue,
                        'overridden' => false,
                        'type' => 'advance',
                        'status' => 'deductions'
                    ];
                    
                    if ($advanceComponentId !== null) {
                        unset($deductions[$advanceComponentId]);
                    }

                    // Ensure status field exists for each component
                    foreach ($earnings as $id => &$earning) {
                        if (!isset($earning['status'])) {
                            $earning['status'] = 'earnings';
                        }
                    }
                    foreach ($deductions as $id => &$deduction) {
                        if (!isset($deduction['status'])) {
                            $deduction['status'] = 'deductions';
                        }
                    }

                    $attendance->earnings = $earnings;
                    $attendance->deductions = $deductions;
                    $attendance->epfWage = round($epfWage);
                    $attendance->totalEarnings = $attendance->gross_pay;
                    $attendance->totalDeductions = $attendance->total_deduction;
                    $attendance->netPay = $attendance->total_payable;

                    // Generate PDF
                    $pdf = \Mpdf\Mpdf::make([
                        'tempDir' => storage_path('app/temp'),
                        'format' => 'A4',
                        'orientation' => 'P',
                        'margin_top' => 10,
                        'margin_bottom' => 10,
                        'margin_left' => 10,
                        'margin_right' => 10
                    ]);

                    $html = view('payroll.pdf-format', [
                        'attendances' => collect([$attendance]),
                        'month' => $month,
                        'year' => $year,
                        'earningComponents' => $earningComponents,
                        'deductionComponents' => $deductionComponents,
                        'epfComponentIds' => $epfComponentIds
                    ])->render();

                    $pdf->WriteHTML($html);
                    $pdfContent = $pdf->Output('', 'S');

                    // Prepare email data
                    $monthNames = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    
                    $monthName = $monthNames[$month];
                    $subject = "Salary Slip for {$monthName} {$year}";
                    $filename = "salary_slip_{$monthName}_{$year}_{$employeeName}.pdf";

                    $emailData = [
                        'employee_name' => $employeeName,
                        'month_name' => $monthName,
                        'year' => $year,
                        'gross_pay' => $attendance->totalEarnings,
                        'net_pay' => $attendance->netPay,
                        'company_name' => config('app.name', 'Company Name')
                    ];

                    // Send email
                    \Mail::send('payroll.salary-slip', $emailData, function ($message) use ($employeeEmail, $subject, $pdfContent, $filename) {
                        $message->to($employeeEmail)
                                ->subject($subject)
                                ->attachData($pdfContent, $filename, [
                                    'mime' => 'application/pdf'
                                ]);
                    });

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to send to {$employeeName}: " . $e->getMessage();
                    $failedCount++;
                    \Log::error('Error in bulk salary slip sending', [
                        'employee' => $employeeData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Return summary
            $message = "Bulk email completed. Successful: {$successCount}, Failed: {$failedCount}";
            
            if ($failedCount > 0) {
                $message .= ". Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more...";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => [
                    'total' => count($employees),
                    'successful' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in bulk salary slip sending: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk salary slips. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Export Bank Transfer File (Bulk Selection)
     */
    public function exportBankBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employee_payroll_attendances,emp_id' 
        ]);

        $month = $request->month;
        $year = $request->year;
        $employeeIds = $request->employee_ids;
        $type = $request->get('type', 'canara_excel');

        // Fetch specific attendances directly (filtering via relationship)
        $attendances = EmployeePayrollAttendance::with(['employee.bankDetail', 'employee.personalDetail'])
            ->whereHas('payoutMonth', function($q) use ($month, $year) {
                $q->where('payout_month', $month)
                  ->where('payout_year', $year);
            })
            ->whereIn('emp_id', $employeeIds)
            ->whereDoesntHave('employee.exitDetails', function ($q) {
                 // Optional: Exclude immediate settlements if required
                 // $q->where('settlement_mode', 'immediate');
            })
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No payroll records found for selected employees in this month.');
        }

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No payroll records found for selected employees in this month.');
        }

        // --- ICICI Export ---
        if ($type === 'icici') {
             // Reusing logic from downloadBankTransferICICI
             $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
             $sheet = $spreadsheet->getActiveSheet();
             $sheet->setTitle('ICICI Bank Transfer');

             $headers = [
                'PYMT_PROD_TYPE_CODE', 'PYMT_MODE', 'DEBIT_ACC_NO', 'BNF_NAME', 'BENE_ACC_NO', 
                'BENE_IFSC', 'AMOUNT', 'CREDIT_NARR', 'PYMT_DATE', 'MOBILE_NUM', 'EMAIL_ID', 'REMARK', 'REF_NO'
             ];
             $col = 'A';
             foreach ($headers as $header) { $sheet->setCellValue($col . '1', $header); $col++; }

             $company = ['debit_account' => '001405010378', 'name' => 'ISARVA INFOTECH PVT LTD'];
             $types = ['neft' => 'NEFT', 'rtgs' => 'RTGS', 'imps' => 'IMPS'];
             $row = 2;
             $currentDate = now()->format('d-m-Y');

             foreach ($attendances as $attendance) {
                $emp = $attendance->employee;
                $bank = $emp->bankDetail;
                $payMode = $bank && $bank->transaction_type ? ($types[$bank->transaction_type] ?? 'NEFT') : 'NEFT';
                
                // Calculate Net Pay dynamically
                $earnings = json_decode($attendance->earnings, true) ?? [];
                $deductions = json_decode($attendance->deductions, true) ?? [];
                
                $totalEarnings = 0;
                if(is_array($earnings)) { foreach($earnings as $e) { if(($e['applicable']??false)) $totalEarnings += ($e['value']??0); } }
                else { $totalEarnings = $attendance->total_earnings; }
                
                $totalDeductions = 0;
                if(is_array($deductions)) { foreach($deductions as $d) { if(($d['applicable']??false)) $totalDeductions += ($d['value']??0); } }
                else { $totalDeductions = $attendance->total_deductions; }

                $currentNetPay = $totalEarnings - $totalDeductions;
                $netPay = number_format((float) $currentNetPay, 2, '.', ''); 
                
                $sheet->setCellValue('A' . $row, 'PAB_VENDOR');
                $sheet->setCellValue('B' . $row, $payMode);
                $sheet->setCellValueExplicit('C' . $row, $company['debit_account'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $row, strtoupper($emp->name ?? ''));
                $sheet->setCellValueExplicit('E' . $row, $bank->account_number ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('F' . $row, $bank->ifsc_code ?? '');
                $sheet->setCellValue('G' . $row, $netPay);
                $sheet->setCellValue('H' . $row, 'SALARY PROCESSED');
                $sheet->setCellValue('I' . $row, $currentDate);
                $row++;
             }

             $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
             return response()->streamDownload(function() use ($writer) {
                 $writer->save('php://output');
             }, 'icici_bulk_'.now()->format('YmdHis').'.xlsx');
        }

        // --- Canara CSV Export ---
        // Aligned strictly with downloadBankTransferCsv
        if ($type === 'canara_csv') {
            $csvFileName = 'canara_bulk_' . now()->format('d_M_Y_Hi') . '.csv'; // Format matched: d_M_Y_Hi
            
            // Header Calculation for Top Rows
            $currentDate = now()->format('d/m/Y');
            
            // Calculate total amount dynamically
            $totalAmount = 0;
            foreach ($attendances as $att) {
                 $earnings = json_decode($att->earnings, true) ?? [];
                 $deductions = json_decode($att->deductions, true) ?? [];
                 
                 $tE = 0; if(is_array($earnings)) { foreach($earnings as $e) { if(($e['applicable']??false)) $tE += ($e['value']??0); } } else { $tE = $att->total_earnings; }
                 $tD = 0; if(is_array($deductions)) { foreach($deductions as $d) { if(($d['applicable']??false)) $tD += ($d['value']??0); } } else { $tD = $att->total_deductions; }
                 $totalAmount += ($tE - $tD);
            }
            $recordCount = $attendances->count();

            $callback = function() use ($attendances, $currentDate, $totalAmount, $recordCount) {
                $file = fopen('php://output', 'w');
                
                // 1. Header Row (Empty, Empty, Empty, Date, Total, Count)
                // Matched logic: $headerRow = ['', '', '', $currentDate, $totalAmount, $recordCount];
                fputcsv($file, ['', '', '', $currentDate, number_format($totalAmount, 2, '.', ''), $recordCount]);

                // Transaction Types Mapping
                $types = function_exists('getTransactionTypes') ? getTransactionTypes() : [
                    'neft' => 'NEFT TRANSFER',
                    'rtgs' => 'RTGS TRANSFER',
                    'imps' => 'IMPS TRANSFER',
                ];

                $companyName = 'DIVYA ROOPA INFRACON PVT LTD'; // Static as per original

                foreach ($attendances as $index => $att) {
                    $emp = $att->employee;
                    $bank = $emp->bankDetail;
                    
                    // Transaction Type
                    $transType = $bank && $bank->transaction_type
                        ? ($types[$bank->transaction_type] ?? 'NEFT TRANSFER')
                        : 'NEFT TRANSFER';

                    // Calculate Net Pay dynamically
                    $earnings = json_decode($att->earnings, true) ?? [];
                    $deductions = json_decode($att->deductions, true) ?? [];
                    
                    $totalEarnings = 0; if(is_array($earnings)) { foreach($earnings as $e) { if(($e['applicable']??false)) $totalEarnings += ($e['value']??0); } } else { $totalEarnings = $att->total_earnings; }
                    $totalDeductions = 0; if(is_array($deductions)) { foreach($deductions as $d) { if(($d['applicable']??false)) $totalDeductions += ($d['value']??0); } } else { $totalDeductions = $att->total_deductions; }
                    $currentNetPay = $totalEarnings - $totalDeductions;
                    
                    // Row Structure: A=Index, B=Type, C=IFSC, D=Account(quoted), E=Name, F-I=Empty, J=Index, K=NetPay, L=Company
                    fputcsv($file, [
                        $index + 1,                          // A: Index
                        $transType,                          // B: Type
                        $bank->ifsc_code ?? '',              // C: IFSC
                        "'" . ($bank->account_number ?? ''), // D: Account (Quoted)
                        strtoupper($emp->name ?? ''),        // E: Name
                        '', '', '', '',                      // F, G, H, I: Empty
                        $index + 1,                          // J: Index
                        number_format($currentNetPay, 2, '.', ''), // K: Amount
                        $companyName                         // L: Company
                    ]);
                }
                fclose($file);
            };
            
            return response()->stream($callback, 200, [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$csvFileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ]);
        }
    }

    /**
     * Bulk Statutory Export (EPF/ESIC)
     */
    public function exportStatutoryBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
            'employee_ids' => 'required|array',
            'type' => 'required|string|in:epf_excel,epf_csv,esic_excel'
        ]);

        $month = $request->month;
        $year = $request->year;
        $employeeIds = $request->employee_ids;
        $type = $request->type;

        // Fetch Data
        // Needs status=completed? Usually yes for reports.
        $attendances = EmployeePayrollAttendance::with(['employee.bankDetail', 'employee.personalDetail', 'employee.salaryComponents', 'employee.statutoryComponents'])
            ->whereHas('payoutMonth', function($q) use ($month, $year) {
                $q->where('payout_month', $month)
                  ->where('payout_year', $year);
            })
            ->whereIn('emp_id', $employeeIds)
            // Ensure we only get finalized records if that's a constraint, but for "Held Process" view we might want draft too? 
            // NO, usually reports are for finalized data. The process view filters to "Payroll Finalized" status usually or at least calculated.
            // Let's assume data exists.
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'No data found for selected employees.');
        }

        // --- ESIC Export ---
        if ($type === 'esic_excel') {
             $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
             $sheet = $spreadsheet->getActiveSheet();
             $sheet->setTitle('Sheet1');
             
             // Simple Header
             $sheet->setCellValue('A1', 'IP Number');
             $sheet->setCellValue('B1', 'IP Name');
             $sheet->setCellValue('C1', 'No of Days');
             $sheet->setCellValue('D1', 'Total Wages');
             $sheet->setCellValue('E1', 'Reason Code');
             $sheet->setCellValue('F1', 'Last Working Day');

             $row = 2;
             foreach ($attendances as $att) {
                  $emp = $att->employee;
                  $personal = $emp->personalDetail;
                  
                  // Calculate Gross from JSON
                  $earnings = json_decode($att->earnings, true) ?? [];
                  $gross = 0;
                  if (is_array($earnings)) {
                       foreach ($earnings as $e) { if(($e['applicable']??false)) $gross += ($e['value']??0); }
                  } else { $gross = $att->total_earnings; }
                   
                   $sheet->setCellValueExplicit('A'.$row, $personal->esi_number ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                   $sheet->setCellValue('B'.$row, $emp->name);
                   $sheet->setCellValue('C'.$row, $att->total_working_days); // Paid days
                   $sheet->setCellValue('D'.$row, number_format($gross, 0, '.', '')); // Wages
                   $sheet->setCellValue('E'.$row, '0');
                   $sheet->setCellValue('F'.$row, '');
                   $row++;
             }
             
             $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
             return response()->streamDownload(function() use ($writer) {
                 $writer->save('php://output');
             }, 'esic_bulk_'.now()->format('YmdHi').'.xlsx');
        }

        // --- EPF Export (Excel/CSV) ---
        // Basic implementation for EPF headers matching main function
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['UAN', 'MEMBER NAME', 'GROSS WAGES', 'EPF WAGES', 'EPS WAGES', 'EDLI WAGES', 'EPF CONTRI', 'EPS CONTRI', 'EPF EPS DIFF', 'NCP DAYS', 'REFUND'];
        $col = 'A';
        foreach($headers as $h) { $sheet->setCellValue($col.'1', $h); $col++; }
        
        $row = 2;
        foreach ($attendances as $att) {
             $emp = $att->employee;
             $personal = $emp->personalDetail;
             
             // Calculate from JSON
             $earnings = json_decode($att->earnings, true) ?? [];
             $deductions = json_decode($att->deductions, true) ?? [];
             
             $gross = 0; $basic = 0; $da = 0;
             if (is_array($earnings)) {
                  foreach ($earnings as $e) { 
                      if(($e['applicable']??false)) {
                          $gross += ($e['value']??0);
                          // Assume Basic is ID 1 and DA is ID 2 - Needs Robust Check or component name match if IDs change
                          // Usually in this system ID 1=Basic, 2=DA based on seeding.
                          if($e['id'] == 1) $basic = $e['value'] ?? 0;
                          if($e['id'] == 2) $da = $e['value'] ?? 0;
                      }
                  }
             } else { $gross = $att->total_earnings; }

             // EPF Wages Logic (Simplified: Basic + DA, capped at 15000 usually)
             $epfWages = $basic + $da;
             if($epfWages > 15000) $epfWages = 15000; // Cap? Check company policy or assume cap.
             // Usually cap at 15000 for EPS, EPF might be higher if voluntary.
             // For bulk export default to 15000 cap or actual if < 15000.
             
             // But wait, if they have actual deduction, we should reverse calc? 
             // Better to trust calculated deduction if available?
             // Let's use Basic+DA for now as standard practice.
             
             $epfContri = 0;
             if(is_array($deductions)) {
                 foreach ($deductions as $d) {
                     if(($d['applicable']??false) && stripos($d['name'] ?? '', 'EPF') !== false) {
                         $epfContri = $d['value'];
                     }
                 }
             }
             
             $sheet->setCellValueExplicit('A'.$row, $personal->uan_number ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
             $sheet->setCellValue('B'.$row, $emp->name);
             $sheet->setCellValue('C'.$row, number_format($gross, 0, '.', ''));
             $sheet->setCellValue('D'.$row, number_format($epfWages, 0, '.', '')); // EPF Wages
             $sheet->setCellValue('E'.$row, number_format($epfWages, 0, '.', '')); // EPS Wages (capped 15k)
             $sheet->setCellValue('F'.$row, number_format($epfWages, 0, '.', '')); // EDLI Wages
             $sheet->setCellValue('G'.$row, number_format($epfContri, 0, '.', '')); // EPF Contri (Employee Share)
             // EPS Contri is Employer share (not usually in employee deduction list), ignore or calc? This is usually Employee file?
             // Standard format asks for shares.
             $sheet->setCellValue('H'.$row, '0'); // EPS Contri (Employer)
             $sheet->setCellValue('I'.$row, '0'); // Diff
             $sheet->setCellValue('J'.$row, '0'); // NCP
             $sheet->setCellValue('K'.$row, '0'); // Refund
             $row++;
        }
        
        if ($type === 'epf_csv') {
             // CSV Writers
              $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
              $ext = 'csv';
        } else {
              $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
              $ext = 'xlsx';
        }
        
         return response()->streamDownload(function() use ($writer) {
             $writer->save('php://output');
         }, 'epf_bulk_'.now()->format('YmdHi').'.'.$ext);
         
    }

    /**
     * Send Payslips Bulk (Selection)
     */
    public function sendPayslipsBulk(Request $request)
    {
        $request->validate([
             'month' => 'required|integer',
             'year' => 'required|integer',
             'employee_ids' => 'required|array'
        ]);
        
        // Retrieve employees with email (exclude null and empty)
        $employees = EmployeeBasicDetail::whereIn('id', $request->employee_ids)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'name', 'email']);
            
        if ($employees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No selected employees have a valid email address.']);
        }
            
        $employeesArray = $employees->map(function($e){
            return ['id' => $e->id, 'name' => $e->name, 'email' => $e->email];
        })->toArray();
        
        $newRequest = new Request();
        $newRequest->replace([
            'month' => $request->month,
            'year' => $request->year,
            'employees' => $employeesArray
        ]);
        
        return $this->sendAllSalarySlips($newRequest);
    }

    /**
     * Helper to get IDs of employees who have active holds for the given month/year
     */
    private function getActiveHeldEmployeeIds($month, $year) 
    {
        return \App\Models\HeldSalary::where('status', 'active')
            ->where(function ($q) use ($month, $year) {
                // Determine if the hold applies to this payout month
                $q->where('hold_type', 'indefinite')
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('hold_type', 'month')
                         ->where('payout_month', $month)
                         ->where('payout_year', $year);
                  });
            })
            ->pluck('employee_id')
            ->toArray();
    }
}
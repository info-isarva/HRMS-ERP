<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\FinancialYearHelper;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with employee statistics
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $selectedFY = FinancialYearHelper::getSelectedFinancialYear();
        
        // Basic Employee Statistics (always show total counts, not filtered by FY)
        $employeeCount = DB::table('employee_basic_details')->count();
        $activeCount = DB::table('employee_basic_details')
            ->whereNull('date_of_resignation')
            ->count();
        $resignedCount = DB::table('employee_basic_details')
            ->whereNotNull('date_of_resignation')
            ->count();

        // Enhanced analytics with correct table names
        $employeeAnalytics = $this->getEmployeeAnalytics();
        $departmentAnalytics = $this->getDepartmentAnalytics();
        $payrollAnalytics = $this->getPayrollAnalytics();
        $recentActivities = $this->getRecentActivities();
        $financialOverview = $this->getFinancialOverview();
        $attendanceOverview = $this->getAttendanceOverview();
        $upcomingEvents = $this->getUpcomingEvents();

        // Legacy variables for backward compatibility
        $recentJoinings = $this->getRecentJoiningsCount();
        $completedPayrolls = $this->getCompletedPayrollsCount();
        $inProgressPayrolls = $this->getInProgressPayrollsCount();
        $departmentCounts = $this->getDepartmentCounts();
        $payrollData = $this->getRecentPayrollData();
        $upcomingBirthdays = $this->getUpcomingBirthdaysCount();
        $upcomingBirthdayEmployees = $this->getUpcomingBirthdayEmployees();

        return view('dashboard.dashboard', compact(
            'employeeCount', 
            'activeCount', 
            'resignedCount', 
            'recentJoinings',
            'completedPayrolls',
            'inProgressPayrolls',
            'departmentCounts',
            'payrollData',
            'upcomingBirthdays',
            'upcomingBirthdayEmployees',
            'employeeAnalytics',
            'departmentAnalytics', 
            'payrollAnalytics',
            'financialOverview',
            'attendanceOverview',
            'recentActivities',
            'upcomingEvents'
        ));
    }

    /**
     * Get comprehensive employee analytics
     */
    private function getEmployeeAnalytics()
    {
        try {
            $currentYear = date('Y');
            $currentMonth = date('m');
            
            // Debug: Log actual database state
            $totalEmployees = DB::table('employee_basic_details')->count();
            $activeEmployees = DB::table('employee_basic_details')->whereNull('date_of_resignation')->count();
            error_log("Debug - Total employees: $totalEmployees, Active: $activeEmployees");
            
            // Monthly hiring trends (last 12 months) - Fixed to show real data
            $hiringTrends = DB::table('employee_basic_details')
                ->select(
                    DB::raw('YEAR(date_of_joining) as year'),
                    DB::raw('MONTH(date_of_joining) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('date_of_joining', '>=', Carbon::now()->subMonths(12))
                ->whereNotNull('date_of_joining')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                        'count' => $item->count,
                        'month' => $item->month,
                        'year' => $item->year
                    ];
                });

            // Debug: Log hiring data for troubleshooting
            if (app()->environment('local') || request()->get('debug')) {
                error_log('Hiring Trends Debug:');
                foreach ($hiringTrends as $trend) {
                    error_log("  {$trend['period']}: {$trend['count']} hires");
                }
            }

            // Resignation trends (last 12 months) - Fixed to show real data
            $resignationTrends = DB::table('employee_basic_details')
                ->select(
                    DB::raw('YEAR(date_of_resignation) as year'),
                    DB::raw('MONTH(date_of_resignation) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('date_of_resignation')
                ->where('date_of_resignation', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'period' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                        'count' => $item->count,
                        'month' => $item->month,
                        'year' => $item->year
                    ];
                });

            // Debug: Log resignation data for troubleshooting
            if (app()->environment('local') || request()->get('debug')) {
                error_log('Resignation Trends Debug:');
                foreach ($resignationTrends as $trend) {
                    error_log("  {$trend['period']}: {$trend['count']} resignations");
                }
            }

            // Debug: Log actual trends data
            error_log("Debug - Hiring trends: " . json_encode($hiringTrends->toArray()));
            error_log("Debug - Resignation trends: " . json_encode($resignationTrends->toArray()));

            // Always return real data - empty collections if no data exists
            if($hiringTrends->isEmpty()) {
                $hiringTrends = collect([]);
            }

            if($resignationTrends->isEmpty()) {
                $resignationTrends = collect([]);
            }

            // Age distribution - Real data only
            $ageDistribution = DB::table('employee_basic_details')
                ->select(
                    DB::raw('
                        CASE 
                            WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 25 THEN "18-25"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 26 AND 35 THEN "26-35"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 45 THEN "36-45"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 46 AND 55 THEN "46-55"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) > 55 THEN "55+"
                            ELSE "Unknown"
                        END as age_group
                    '),
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('date_of_birth')
                ->whereNull('date_of_resignation') // Only active employees
                ->groupBy('age_group')
                ->get();

            // Experience distribution - Real data only
            $experienceDistribution = DB::table('employee_basic_details')
                ->select(
                    DB::raw('
                        CASE 
                            WHEN TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) < 1 THEN "0-1 Year"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) BETWEEN 1 AND 3 THEN "1-3 Years"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) BETWEEN 4 AND 7 THEN "4-7 Years"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) BETWEEN 8 AND 15 THEN "8-15 Years"
                            WHEN TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) > 15 THEN "15+ Years"
                            ELSE "Unknown"
                        END as experience_group
                    '),
                    DB::raw('COUNT(*) as count')
                )
                ->whereNotNull('date_of_joining')
                ->whereNull('date_of_resignation') // Only active employees
                ->groupBy('experience_group')
                ->get();

            return [
                'hiring_trends' => $hiringTrends,
                'resignation_trends' => $resignationTrends,
                'age_distribution' => $ageDistribution,
                'experience_distribution' => $experienceDistribution,
                'turnover_rate' => $this->calculateTurnoverRate(),
                'growth_rate' => $this->calculateGrowthRate()
            ];
        } catch (\Exception $e) {
            // Log the error for debugging but return real empty data structure
            error_log('Employee Analytics Error: ' . $e->getMessage());
            
            return [
                'hiring_trends' => collect([]),
                'resignation_trends' => collect([]),
                'age_distribution' => collect([]),
                'experience_distribution' => collect([]),
                'turnover_rate' => 0,
                'growth_rate' => 0
            ];
        }
    }

    /**
     * Get department analytics
     */
    private function getDepartmentAnalytics()
    {
        try {
            $departmentData = DB::table('employee_basic_details as ebd')
                ->leftJoin('departments as d', 'ebd.department', '=', 'd.id')
                ->select(
                    'ebd.department as dept_id',
                    DB::raw('COALESCE(d.department, "Not Assigned") as dept_name'),
                    DB::raw('count(*) as total_count')
                )
                ->whereNull('ebd.date_of_resignation')
                ->groupBy('ebd.department', 'd.department')
                ->get();

            $departmentCounts = [];
            if($departmentData->count() > 0) {
                foreach($departmentData as $dept) {
                    $deptName = $dept->dept_name ?: 'Not Assigned';
                    $departmentCounts[] = [
                        'id' => $dept->dept_id,
                        'name' => $deptName,
                        'count' => $dept->total_count,
                        'percentage' => 0 // Will be calculated later
                    ];
                }
            }

            // Calculate percentages
            $totalEmployees = array_sum(array_column($departmentCounts, 'count'));
            if($totalEmployees > 0) {
                foreach($departmentCounts as &$dept) {
                    $dept['percentage'] = round(($dept['count'] / $totalEmployees) * 100, 1);
                }
            }

            return [
                'department_counts' => collect($departmentCounts),
                'total_departments' => count($departmentCounts)
            ];
        } catch (\Exception $e) {
            // Error handling - return empty but structured data
            return [
                'department_counts' => collect([]),
                'total_departments' => 0
            ];
        }
    }

    /**
     * Get comprehensive payroll analytics
     */
    private function getPayrollAnalytics()
    {
        $currentYear = date('Y');
        
        // Payroll status overview
        $payrollStatuses = DB::table('employee_payroll_attendance_payout_month_statuses')
            ->select('status', DB::raw('count(*) as count'))
            ->where('payout_year', $currentYear)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Monthly payroll processing timeline
        $payrollTimeline = DB::table('employee_payroll_attendance_payout_month_statuses')
            ->select('payout_month', 'payout_year', 'status', 'created_at', 'finalized_at')
            ->where('payout_year', $currentYear)
            ->orderBy('payout_month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->payout_month,
                    'year' => $item->payout_year,
                    'period' => Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M Y'),
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'finalized_at' => $item->finalized_at,
                    'processing_time' => $item->finalized_at ? 
                        Carbon::parse($item->created_at)->diffInDays(Carbon::parse($item->finalized_at)) : null
                ];
            });

        // OT and Incentive analytics
        $otIncentiveData = $this->getOtIncentiveAnalytics();

        return [
            'completed_payrolls' => $payrollStatuses['completed'] ?? 0,
            'in_progress_payrolls' => $payrollStatuses['progress'] ?? 0,
            'payroll_timeline' => $payrollTimeline,
            'ot_incentive_data' => $otIncentiveData,
            'processing_efficiency' => $this->calculateProcessingEfficiency($payrollTimeline)
        ];
    }

    /**
     * Get OT and Incentive analytics
     */
    private function getOtIncentiveAnalytics()
    {
        try {
            $currentYear = date('Y');
            
            // OT trends
            $otTrends = DB::table('employee_ot_details')
                ->select(
                    'payout_month',
                    'payout_year',
                    DB::raw('SUM(ot_hours) as total_hours'),
                    DB::raw('SUM(total_amount) as total_amount'),
                    DB::raw('COUNT(DISTINCT emp_id) as employee_count')
                )
                ->where('payout_year', $currentYear)
                ->groupBy('payout_year', 'payout_month')
                ->orderBy('payout_month')
                ->get();

            // Holiday work trends (Sunday work)
            $holidayTrends = DB::table('employee_holiday_payout_details')
                ->select(
                    'payout_month',
                    'payout_year',
                    DB::raw('SUM(holiday_work_days) as total_days'),
                    DB::raw('SUM(total_amount) as total_amount'),
                    DB::raw('COUNT(DISTINCT emp_id) as employee_count')
                )
                ->where('payout_year', $currentYear)
                ->groupBy('payout_year', 'payout_month')
                ->orderBy('payout_month')
                ->get();

            // Incentive trends
            $incentiveTrends = DB::table('employee_incentive_details')
                ->select(
                    'payout_month',
                    'payout_year',
                    DB::raw('SUM(incentive_days) as total_days'),
                    DB::raw('SUM(total_amount) as total_amount'),
                    DB::raw('COUNT(DISTINCT emp_id) as employee_count')
                )
                ->where('payout_year', $currentYear)
                ->groupBy('payout_year', 'payout_month')
                ->orderBy('payout_month')
                ->get();

            // Add fallback demo data if no OT/Incentive data exists
            if($otTrends->isEmpty()) {
                $otTrends = collect([]);
            }

            if($holidayTrends->isEmpty()) {
                $holidayTrends = collect([]);
            }

            if($incentiveTrends->isEmpty()) {
                $incentiveTrends = collect([]);
            }

            return [
                'ot_trends' => $otTrends,
                'holiday_trends' => $holidayTrends,
                'incentive_trends' => $incentiveTrends
            ];
        } catch (\Exception $e) {
            // Log error and return empty real data structure
            error_log('OT Incentive Analytics Error: ' . $e->getMessage());
            
            return [
                'ot_trends' => collect([]),
                'holiday_trends' => collect([]),
                'incentive_trends' => collect([])
            ];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        // Recent joinings (last 30 days)
        $recentJoinings = DB::table('employee_basic_details')
            ->select('name', 'employee_id', 'date_of_joining', 'department')
            ->whereRaw('date_of_joining >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
            ->orderBy('date_of_joining', 'desc')
            ->limit(10)
            ->get();

        // Recent resignations (last 30 days)
        $recentResignations = DB::table('employee_basic_details')
            ->select('name', 'employee_id', 'date_of_resignation', 'department')
            ->whereNotNull('date_of_resignation')
            ->whereRaw('date_of_resignation >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
            ->orderBy('date_of_resignation', 'desc')
            ->limit(10)
            ->get();

        return [
            'recent_joinings' => $recentJoinings,
            'recent_resignations' => $recentResignations,
            'joinings_count' => $recentJoinings->count(),
            'resignations_count' => $recentResignations->count()
        ];
    }

    /**
     * Get financial overview
     */
    private function getFinancialOverview()
    {
        try {
            $currentYear = date('Y');
            $currentMonth = date('m');

            // First, try to get salary component totals for current month
            $salaryData = DB::table('employee_payroll_attendances as epa')
                ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
                ->where('status.payout_year', $currentYear)
                ->where('status.payout_month', $currentMonth)
                ->where('status.status', 'completed')
                ->select(
                    DB::raw('SUM(epa.gross_pay) as total_gross'),
                    DB::raw('SUM(epa.total_deduction) as total_deductions'),
                    DB::raw('SUM(epa.total_payable) as total_net'),
                    DB::raw('COUNT(*) as employee_count')
                )
                ->first();

            // If no data for current month, get the most recent completed month
            if (!$salaryData || $salaryData->employee_count == 0) {
                $recentData = DB::table('employee_payroll_attendances as epa')
                    ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
                    ->where('status.status', 'completed')
                    ->select(
                        DB::raw('SUM(epa.gross_pay) as total_gross'),
                        DB::raw('SUM(epa.total_deduction) as total_deductions'),
                        DB::raw('SUM(epa.total_payable) as total_net'),
                        DB::raw('COUNT(*) as employee_count'),
                        'status.payout_month',
                        'status.payout_year'
                    )
                    ->groupBy('status.payout_year', 'status.payout_month')
                    ->orderBy('status.payout_year', 'desc')
                    ->orderBy('status.payout_month', 'desc')
                    ->first();

                if ($recentData) {
                    $salaryData = $recentData;
                }
            }

            // If still no data, create default structure with real zeros
            if (!$salaryData || $salaryData->employee_count == 0) {
                $salaryData = (object) [
                    'total_gross' => 0,
                    'total_deductions' => 0,
                    'total_net' => 0,
                    'employee_count' => 0
                ];
            }

            // Monthly financial trends (last 6 months) - deductions already include advances in total_deduction
            $financialTrends = DB::table('employee_payroll_attendances as epa')
                ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
                ->where('status.status', 'completed')
                ->where('status.payout_year', '>=', $currentYear - 1)
                ->select(
                    'status.payout_month',
                    'status.payout_year',
                    DB::raw('SUM(epa.gross_pay) as total_gross'),
                    DB::raw('SUM(epa.total_deduction) as total_deductions'),
                    DB::raw('SUM(epa.total_payable) as total_net')
                )
                ->groupBy('status.payout_year', 'status.payout_month')
                ->orderBy('status.payout_year', 'asc')
                ->orderBy('status.payout_month', 'asc')
                ->limit(6)
                ->get();

            // If no trends data, create empty collection 
            if ($financialTrends->isEmpty()) {
                $financialTrends = collect([]);
            }

            return [
                'current_month' => $salaryData,
                'financial_trends' => $financialTrends
            ];
        } catch (\Exception $e) {
            // Log error and return empty real data structure
            error_log('Financial Overview Error: ' . $e->getMessage());
            
            return [
                'current_month' => (object) [
                    'total_gross' => 0,
                    'total_deductions' => 0,
                    'total_net' => 0,
                    'employee_count' => 0
                ],
                'financial_trends' => collect([])
            ];
        }
    }

    /**
     * Get attendance overview
     */
    private function getAttendanceOverview()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        // Current month attendance summary
        $attendanceData = DB::table('employee_payroll_attendances as epa')
            ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
            ->where('status.payout_year', $currentYear)
            ->where('status.payout_month', $currentMonth)
            ->select(
                DB::raw('AVG(epa.employee_worked_days) as avg_attendance'),
                DB::raw('AVG(epa.total_working_days) as avg_working_days'),
                DB::raw('COUNT(*) as total_employees')
            )
            ->first();

        // Generate month name
        $monthName = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->format('F Y');
        
        // Handle no data case
        $hasData = $attendanceData && $attendanceData->total_employees > 0;
        
        if (!$hasData) {
            $attendanceData = (object) [
                'avg_attendance' => 0,
                'avg_working_days' => 0,
                'total_employees' => 0
            ];
        }

        $attendancePercentage = null;
        $attendanceMessage = 'Payroll attendance not saved';
        
        if ($hasData && $attendanceData->avg_working_days > 0) {
            $attendancePercentage = round(($attendanceData->avg_attendance / $attendanceData->avg_working_days) * 100, 1);
            $attendanceMessage = $attendancePercentage . '%';
        }

        return [
            'current_month_data' => $attendanceData,
            'attendance_percentage' => $attendancePercentage,
            'attendance_message' => $attendanceMessage,
            'month_name' => $monthName,
            'has_data' => $hasData
        ];
    }

    /**
     * Get upcoming events (birthdays, work anniversaries)
     */
    private function getUpcomingEvents()
    {
        // Upcoming birthdays (next 30 days)
        $upcomingBirthdays = DB::table('employee_basic_details')
            ->selectRaw('id, employee_id, name, date_of_birth, department')
            ->whereRaw("
                (MONTH(date_of_birth) = MONTH(CURRENT_DATE()) AND DAY(date_of_birth) >= DAY(CURRENT_DATE()))
                OR 
                (MONTH(date_of_birth) = MONTH(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                    AND DAY(date_of_birth) <= DAY(DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)))
            ")
            ->orderByRaw("
                CASE 
                    WHEN MONTH(date_of_birth) = MONTH(CURRENT_DATE()) THEN 0 
                    ELSE 1 
                END, 
                DAY(date_of_birth)
            ")
            ->limit(10)
            ->get();

        // Work anniversaries (next 30 days)
        $workAnniversaries = DB::table('employee_basic_details')
            ->selectRaw('
                id, employee_id, name, date_of_joining, department,
                TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) as years_of_service
            ')
            ->whereRaw("
                (MONTH(date_of_joining) = MONTH(CURRENT_DATE()) AND DAY(date_of_joining) >= DAY(CURRENT_DATE()))
                OR 
                (MONTH(date_of_joining) = MONTH(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH)) 
                    AND DAY(date_of_joining) <= DAY(DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)))
            ")
            ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_joining, CURDATE()) > 0')
            ->orderByRaw("
                CASE 
                    WHEN MONTH(date_of_joining) = MONTH(CURRENT_DATE()) THEN 0 
                    ELSE 1 
                END, 
                DAY(date_of_joining)
            ")
            ->limit(10)
            ->get();

        return [
            'upcoming_birthdays' => $upcomingBirthdays,
            'work_anniversaries' => $workAnniversaries,
            'birthdays_count' => $upcomingBirthdays->count(),
            'anniversaries_count' => $workAnniversaries->count()
        ];
    }

    /**
     * Calculate turnover rate
     */
    private function calculateTurnoverRate()
    {
        $totalEmployees = DB::table('employee_basic_details')->count();
        $resignationsThisYear = DB::table('employee_basic_details')
            ->whereYear('date_of_resignation', date('Y'))
            ->count();

        return $totalEmployees > 0 ? round(($resignationsThisYear / $totalEmployees) * 100, 2) : 0;
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate()
    {
        $joinigsThisYear = DB::table('employee_basic_details')
            ->whereYear('date_of_joining', date('Y'))
            ->count();
        
        $resignationsThisYear = DB::table('employee_basic_details')
            ->whereYear('date_of_resignation', date('Y'))
            ->count();

        $netGrowth = $joinigsThisYear - $resignationsThisYear;
        $totalEmployees = DB::table('employee_basic_details')->count();

        return $totalEmployees > 0 ? round(($netGrowth / $totalEmployees) * 100, 2) : 0;
    }

    /**
     * Calculate payroll processing efficiency
     */
    private function calculateProcessingEfficiency($payrollTimeline)
    {
        $completedPayrolls = $payrollTimeline->where('status', 'completed');
        $avgProcessingTime = $completedPayrolls->avg('processing_time');
        
        return [
            'avg_processing_days' => $avgProcessingTime ? round($avgProcessingTime, 1) : 0,
            'efficiency_score' => $avgProcessingTime ? min(100, max(0, 100 - ($avgProcessingTime * 5))) : 0
        ];
    }

    /**
     * Get recent joinings count (last 30 days)
     */
    private function getRecentJoiningsCount()
    {
        return DB::table('employee_basic_details')
            ->where('date_of_joining', '>=', Carbon::now()->subDays(30))
            ->count();
    }

    /**
     * Get completed payrolls count
     */
    private function getCompletedPayrollsCount()
    {
        return DB::table('employee_payroll_attendance_payout_month_statuses')
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Get in-progress payrolls count
     */
    private function getInProgressPayrollsCount()
    {
        return DB::table('employee_payroll_attendance_payout_month_statuses')
            ->where('status', 'progress')
            ->count();
    }

    /**
     * Get department-wise employee counts
     */
    private function getDepartmentCounts()
    {
        return DB::table('employee_basic_details')
            ->select('department as name', DB::raw('COUNT(*) as count'))
            ->whereNull('date_of_resignation')
            ->groupBy('department')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?: 'Not Assigned',
                    'count' => $item->count
                ];
            });
    }

    /**
     * Get recent payroll data
     */
    private function getRecentPayrollData()
    {
        return DB::table('employee_payroll_attendance_payout_month_statuses')
            ->select('payout_month', 'payout_year', 'status')
            ->orderBy('payout_year', 'desc')
            ->orderBy('payout_month', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get upcoming birthdays count (next 30 days)
     */
    private function getUpcomingBirthdaysCount()
    {
        $today = Carbon::now();
        $thirtyDaysFromNow = Carbon::now()->addDays(30);
        
        return DB::table('employee_basic_details')
            ->whereNull('date_of_resignation')
            ->where(function($query) use ($today, $thirtyDaysFromNow) {
                // Handle year boundary
                if ($today->year == $thirtyDaysFromNow->year) {
                    $query->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                        $today->format('m-d'),
                        $thirtyDaysFromNow->format('m-d')
                    ]);
                } else {
                    $query->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= ?", [$today->format('m-d')])
                          ->orWhereRaw("DATE_FORMAT(date_of_birth, '%m-%d') <= ?", [$thirtyDaysFromNow->format('m-d')]);
                }
            })
            ->count();
    }

    /**
     * Get upcoming birthday employees (next 30 days)
     */
    private function getUpcomingBirthdayEmployees()
    {
        $today = Carbon::now();
        $thirtyDaysFromNow = Carbon::now()->addDays(30);
        
        return DB::table('employee_basic_details')
            ->select('employee_id', 'name', 'date_of_birth')
            ->whereNull('date_of_resignation')
            ->where(function($query) use ($today, $thirtyDaysFromNow) {
                // Handle year boundary
                if ($today->year == $thirtyDaysFromNow->year) {
                    $query->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                        $today->format('m-d'),
                        $thirtyDaysFromNow->format('m-d')
                    ]);
                } else {
                    $query->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= ?", [$today->format('m-d')])
                          ->orWhereRaw("DATE_FORMAT(date_of_birth, '%m-%d') <= ?", [$thirtyDaysFromNow->format('m-d')]);
                }
            })
            ->orderByRaw("DATE_FORMAT(date_of_birth, '%m-%d')")
            ->get();
    }

    /**
     * Get analytics data for a specific month (AJAX endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnalyticsData(Request $request)
    {
        try {
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));
            
            // Validate inputs
            if (!is_numeric($month) || $month < 1 || $month > 12) {
                $month = date('m');
            }
            if (!is_numeric($year) || $year < 2020 || $year > 2030) {
                $year = date('Y');
            }
            
            // Get analytics for the specified month
            $employeeAnalytics = $this->getEmployeeAnalyticsForMonth($month, $year);
            $departmentAnalytics = $this->getDepartmentAnalyticsForMonth($month, $year);
            $financialOverview = $this->getFinancialOverviewForMonth($month, $year);
            $attendanceOverview = $this->getAttendanceOverviewForMonth($month, $year);
            $payrollAnalytics = $this->getPayrollAnalyticsForMonth($month, $year);
            $otHolidayAnalytics = $this->getOtHolidayAnalyticsForMonth($month, $year);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'employeeAnalytics' => $employeeAnalytics,
                    'departmentAnalytics' => $departmentAnalytics,
                    'financialOverview' => $financialOverview,
                    'attendanceOverview' => $attendanceOverview,
                    'payrollAnalytics' => array_merge($payrollAnalytics, $otHolidayAnalytics),
                    'month' => (int)$month,
                    'year' => (int)$year,
                    'monthName' => Carbon::createFromDate($year, $month, 1)->format('F Y')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching analytics data: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * Get employee analytics for specific month
     */
    private function getEmployeeAnalyticsForMonth($month, $year)
    {
        try {
            // Get hiring trends for the specific month
            $hiringCount = DB::table('employee_basic_details')
                ->whereMonth('date_of_joining', $month)
                ->whereYear('date_of_joining', $year)
                ->count();
            
            // Get resignation trends for the specific month
            $resignationCount = DB::table('employee_basic_details')
                ->whereNotNull('date_of_resignation')
                ->whereMonth('date_of_resignation', $month)
                ->whereYear('date_of_resignation', $year)
                ->count();
            
            // Get net employee growth for the month
            $netGrowth = $hiringCount - $resignationCount;
            
            // Get employee count at the end of the specified month
            $employeeCountAtMonth = DB::table('employee_basic_details')
                ->where(function($query) use ($month, $year) {
                    $query->where('date_of_joining', '<=', Carbon::createFromDate($year, $month, 1)->endOfMonth())
                          ->where(function($subQuery) use ($month, $year) {
                              $subQuery->whereNull('date_of_resignation')
                                       ->orWhere('date_of_resignation', '>', Carbon::createFromDate($year, $month, 1)->endOfMonth());
                          });
                })
                ->count();
            
            return [
                'hiring_count' => $hiringCount,
                'resignation_count' => $resignationCount,
                'net_growth' => $netGrowth,
                'total_employees' => $employeeCountAtMonth,
                'growth_percentage' => $employeeCountAtMonth > 0 ? round(($netGrowth / $employeeCountAtMonth) * 100, 2) : 0
            ];
            
        } catch (\Exception $e) {
            return [
                'hiring_count' => 0,
                'resignation_count' => 0,
                'net_growth' => 0,
                'total_employees' => 0,
                'growth_percentage' => 0
            ];
        }
    }
    
    /**
     * Get department analytics for specific month
     */
    private function getDepartmentAnalyticsForMonth($month, $year)
    {
        try {
            $departmentData = DB::table('employee_basic_details as ebd')
                ->leftJoin('departments as d', 'ebd.department', '=', 'd.id')
                ->select(
                    'ebd.department as dept_id',
                    DB::raw('COALESCE(d.department, "Not Assigned") as dept_name'),
                    DB::raw('count(*) as count')
                )
                ->where(function($query) use ($month, $year) {
                    $query->where('ebd.date_of_joining', '<=', Carbon::createFromDate($year, $month, 1)->endOfMonth())
                          ->where(function($subQuery) use ($month, $year) {
                              $subQuery->whereNull('ebd.date_of_resignation')
                                       ->orWhere('ebd.date_of_resignation', '>', Carbon::createFromDate($year, $month, 1)->endOfMonth());
                          });
                })
                ->groupBy('ebd.department', 'd.department')
                ->get();
            
            $departments = [];
            foreach($departmentData as $dept) {
                $departments[] = [
                    'name' => $dept->dept_name ?: 'Not Assigned',
                    'count' => $dept->count
                ];
            }
            
            return $departments;
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get financial overview for specific month
     */
    private function getFinancialOverviewForMonth($month, $year)
    {
        try {
            // Get salary component totals for specific month - deductions already include advances in total_deduction
            $salaryData = DB::table('employee_payroll_attendances as epa')
                ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
                ->where('status.payout_year', $year)
                ->where('status.payout_month', $month)
                ->where('status.status', 'completed')
                ->select(
                    DB::raw('SUM(epa.gross_pay) as total_gross'),
                    DB::raw('SUM(epa.total_deduction) as total_deductions'),
                    DB::raw('SUM(epa.total_payable) as total_net'),
                    DB::raw('COUNT(*) as employee_count')
                )
                ->first();

            // If no data, create default structure with real zeros
            if (!$salaryData || $salaryData->employee_count == 0) {
                $salaryData = (object) [
                    'total_gross' => 0,
                    'total_deductions' => 0,
                    'total_net' => 0,
                    'employee_count' => 0
                ];
            }
            
            // Calculate derived values for chart compatibility
            $totalSalary = $salaryData->total_gross;
            $netPayout = $salaryData->total_net;
            $totalDeductions = $salaryData->total_deductions;
            
            return [
                // For chart (using the expected field names)
                'total_salary' => round($totalSalary, 2),
                'net_payout' => round($netPayout, 2),
                'total_deductions' => round($totalDeductions, 2),
                
                // For metrics display (using database field names)
                'total_gross' => round($salaryData->total_gross, 2),
                'total_net' => round($salaryData->total_net, 2),
                'employee_count' => $salaryData->employee_count
            ];
            
        } catch (\Exception $e) {
            error_log('Financial Overview For Month Error: ' . $e->getMessage());
            
            return [
                'total_salary' => 0,
                'net_payout' => 0,
                'total_deductions' => 0,
                'total_gross' => 0,
                'total_net' => 0,
                'employee_count' => 0
            ];
        }
    }
    
    /**
     * Get attendance overview for specific month
     */
    private function getAttendanceOverviewForMonth($month, $year)
    {
        try {
            // Get attendance data for the specified month
            $attendanceData = DB::table('employee_payroll_attendances as epa')
                ->join('employee_payroll_attendance_payout_month_statuses as status', 'epa.payout_month_id', '=', 'status.id')
                ->where('status.payout_year', $year)
                ->where('status.payout_month', $month)
                ->where('status.status', 'completed')
                ->select(
                    DB::raw('AVG(epa.employee_worked_days) as avg_attendance'),
                    DB::raw('AVG(epa.total_working_days) as avg_working_days'),
                    DB::raw('COUNT(*) as total_employees')
                )
                ->first();

            // Generate month name
            $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
            
            // Handle no data case
            $hasData = $attendanceData && $attendanceData->total_employees > 0;
            
            if (!$hasData) {
                return [
                    'current_month_data' => (object) [
                        'avg_attendance' => 0,
                        'avg_working_days' => 0,
                        'total_employees' => 0
                    ],
                    'attendance_percentage' => null,
                    'attendance_message' => 'Payroll attendance not saved',
                    'month_name' => $monthName,
                    'has_data' => false
                ];
            }

            $attendancePercentage = $attendanceData->avg_working_days > 0 
                ? round(($attendanceData->avg_attendance / $attendanceData->avg_working_days) * 100, 1)
                : 0;

            return [
                'current_month_data' => $attendanceData,
                'attendance_percentage' => $attendancePercentage,
                'attendance_message' => $attendancePercentage . '%',
                'month_name' => $monthName,
                'has_data' => true
            ];
            
        } catch (\Exception $e) {
            error_log('Attendance Overview For Month Error: ' . $e->getMessage());
            $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
            return [
                'current_month_data' => (object) [
                    'avg_attendance' => 0,
                    'avg_working_days' => 0,
                    'total_employees' => 0
                ],
                'attendance_percentage' => null,
                'attendance_message' => 'Payroll attendance not saved',
                'month_name' => $monthName,
                'has_data' => false
            ];
        }
    }
    
    /**
     * Get payroll analytics for specific month
     */
    private function getPayrollAnalyticsForMonth($month, $year)
    {
        try {
            // Get payroll processing status for the month
            $payrollStatus = DB::table('employee_payroll_attendance_payout_month_statuses')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->first();
            
            // Get employee count processed
            $processedCount = DB::table('employee_payroll_attendance_payouts')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->count();
            
            return [
                'status' => $payrollStatus ? $payrollStatus->status : 'Not Processed',
                'processed_employees' => $processedCount,
                'finalized_at' => $payrollStatus ? $payrollStatus->finalized_at : null,
                'created_at' => $payrollStatus ? $payrollStatus->created_at : null
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'processed_employees' => 0,
                'finalized_at' => null,
                'created_at' => null
            ];
        }
    }

    /**
     * Get OT and Holiday analytics for specific month
     */
    private function getOtHolidayAnalyticsForMonth($month, $year)
    {
        try {
            // Get OT amount for the specific month
            $otAmount = DB::table('employee_ot_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->sum('total_amount');
            
            // Get Holiday work amount for the specific month
            $holidayAmount = DB::table('employee_holiday_payout_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->sum('total_amount');
            
            // Get Incentive amount for the specific month
            $incentiveAmount = DB::table('employee_incentive_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->sum('total_amount');
            
            // Get employee counts
            $otEmployeeCount = DB::table('employee_ot_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->distinct('emp_id')
                ->count();
            
            $holidayEmployeeCount = DB::table('employee_holiday_payout_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->distinct('emp_id')
                ->count();
            
            $incentiveEmployeeCount = DB::table('employee_incentive_details')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->distinct('emp_id')
                ->count();
            
            return [
                'ot_amount' => round($otAmount, 2),
                'holiday_amount' => round($holidayAmount, 2),
                'incentive_amount' => round($incentiveAmount, 2),
                'ot_employee_count' => $otEmployeeCount,
                'holiday_employee_count' => $holidayEmployeeCount,
                'incentive_employee_count' => $incentiveEmployeeCount,
                'total_extra_earnings' => round($otAmount + $holidayAmount + $incentiveAmount, 2)
            ];
            
        } catch (\Exception $e) {
            return [
                'ot_amount' => 0,
                'holiday_amount' => 0,
                'incentive_amount' => 0,
                'ot_employee_count' => 0,
                'holiday_employee_count' => 0,
                'incentive_employee_count' => 0,
                'total_extra_earnings' => 0
            ];
        }
    }

    // ...existing enhanced methods...
    /**
     * Get calendar events for the dashboard
     */
    public function getCalendarEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $events = [];

        // 1. Public Holidays
        $holidays = \App\Models\Holiday::whereBetween('date_holiday', [$start, $end])->get();
        foreach ($holidays as $holiday) {
            $events[] = [
                'title' => '🌴 ' . $holiday->name_holiday,
                'start' => $holiday->date_holiday,
                'className' => 'bg-danger text-white',
                'allDay' => true,
                'editable' => false
            ];
        }

        // 2. Employee Leaves (Approved only)
        $leaves = \App\Models\Leave::where('status', 'Approved')
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('date_from', [$start, $end])
                  ->orWhereBetween('date_to', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->where('date_from', '<', $start)
                          ->where('date_to', '>', $end);
                  });
            })
            ->get();
            
        foreach ($leaves as $leave) {
            $events[] = [
                'title' => '✈️ ' . $leave->employee_name . ' (' . $leave->leave_type . ')',
                'start' => $leave->date_from,
                'end' => \Carbon\Carbon::parse($leave->date_to)->addDay()->format('Y-m-d'),
                'className' => 'bg-warning text-dark',
                'allDay' => true,
                'editable' => false
            ];
        }

        // 3. Birthdays
        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);
        
        $employees = DB::table('employee_basic_details')
            ->whereNull('date_of_resignation')
            ->whereNotNull('date_of_birth')
            ->select('id', 'name', 'date_of_birth')
            ->get();

        foreach ($employees as $emp) {
            try {
                $dob = \Carbon\Carbon::parse($emp->date_of_birth);
                $years = array_unique([$startDate->year, $endDate->year]);
                
                foreach ($years as $year) {
                    $thisYearBday = $dob->copy()->year($year);
                    if ($thisYearBday->between($startDate, $endDate)) {
                        $events[] = [
                            'title' => '🎂 ' . $emp->name,
                            'start' => $thisYearBday->format('Y-m-d'),
                            'className' => 'bg-info text-white',
                            'allDay' => true,
                            'editable' => false
                        ];
                    }
                }
            } catch (\Exception $e) { }
        }
        
        // 4. Payroll Events
         $payrolls = DB::table('employee_payroll_attendance_payout_month_statuses')
            ->select(
                'payout_month', 
                'payout_year', 
                DB::raw('MAX(created_at) as created_at'), 
                DB::raw('MAX(finalized_at) as finalized_at')
            )
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                  ->orWhereBetween('finalized_at', [$start, $end]);
            })
            ->groupBy('payout_month', 'payout_year')
            ->get();
            
        foreach ($payrolls as $payroll) {
            // Create mostly unique IDs based on month/year
            if ($payroll->created_at >= $start && $payroll->created_at <= $end) {
                 $events[] = [
                    'title' => '💰 Payroll Started (' . date('M Y', mktime(0, 0, 0, $payroll->payout_month, 10, $payroll->payout_year)) . ')',
                    'start' => substr($payroll->created_at, 0, 10),
                    'className' => 'bg-primary text-white',
                    'allDay' => true,
                    'editable' => false
                ];
            }
            if ($payroll->finalized_at && $payroll->finalized_at >= $start && $payroll->finalized_at <= $end) {
                 $events[] = [
                    'title' => '✅ Payroll Finalized (' . date('M Y', mktime(0, 0, 0, $payroll->payout_month, 10, $payroll->payout_year)) . ')',
                    'start' => substr($payroll->finalized_at, 0, 10),
                    'className' => 'bg-success text-white',
                    'allDay' => true,
                    'editable' => false
                ];
            }
        }

        // 5. Custom Company Events
        // Fetch ALL custom events (including recurring that might generate instances in view range)
        $customEvents = \App\Models\CompanyCalendarEvent::get();

        $startDate = \Carbon\Carbon::parse($start);
        $endDate = \Carbon\Carbon::parse($end);

        foreach ($customEvents as $event) {
            $eventStart = \Carbon\Carbon::parse($event->start_date);
            $eventEnd = $event->end_date ? \Carbon\Carbon::parse($event->end_date) : $eventStart->copy();
            
            // Generate instances based on recurrence type
            $instances = [];
            
            if ($event->recurrence_type === 'none' || empty($event->recurrence_type)) {
                // Single event - check if it falls within view range
                if ($eventStart->lte($endDate) && $eventEnd->gte($startDate)) {
                    $instances[] = ['start' => $eventStart->copy(), 'end' => $eventEnd->copy()];
                }
            } else {
                // Recurring event - generate instances
                $recurrenceEndDate = $event->recurrence_end_date 
                    ? \Carbon\Carbon::parse($event->recurrence_end_date) 
                    : $endDate; // Default to view end if no recurrence end
                
                $currentStart = $eventStart->copy();
                $duration = $eventEnd->diffInDays($eventStart);
                
                while ($currentStart->lte($recurrenceEndDate) && $currentStart->lte($endDate)) {
                    $currentEnd = $currentStart->copy()->addDays($duration);
                    
                    // Check if this instance overlaps with view range
                    if ($currentEnd->gte($startDate) && $currentStart->lte($endDate)) {
                        $instances[] = ['start' => $currentStart->copy(), 'end' => $currentEnd->copy()];
                    }
                    
                    // Move to next occurrence
                    switch ($event->recurrence_type) {
                        case 'daily':
                            $currentStart->addDay();
                            break;
                        case 'weekly':
                            $currentStart->addWeek();
                            break;
                        case 'monthly':
                            $currentStart->addMonth();
                            break;
                        case 'yearly':
                            $currentStart->addYear();
                            break;
                    }
                }
            }
            
            // Add each instance as a calendar event
            foreach ($instances as $index => $instance) {
                $fullcalendarEnd = $instance['end']->addDay()->format('Y-m-d'); // Exclusive end date
                
                // Build start datetime string
                $startStr = $instance['start']->format('Y-m-d');
                if ($event->start_time) {
                    $startStr = $instance['start']->format('Y-m-d') . 'T' . $event->start_time;
                }
                
                $events[] = [
                    'id' => 'custom_' . $event->id . ($index > 0 ? '_' . $index : ''),
                    'title' => $event->title,
                    'start' => $startStr,
                    'end' => $fullcalendarEnd,
                    'className' => $event->event_class . ' text-white cursor-pointer',
                    'description' => $event->description,
                    'editable' => true,
                    'custom' => true,
                    'allDay' => empty($event->start_time),
                    // Pass extra data for edit modal
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'recurrence_type' => $event->recurrence_type ?? 'none',
                    'recurrence_end_date' => $event->recurrence_end_date,
                    'raw_start_date' => $event->start_date,
                    'raw_end_date' => $event->end_date
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * Store a new custom calendar event
     */
    /**
     * Store a new custom calendar event
     */
    public function storeCalendarEvent(Request $request)
    {
        try {
            $data = $request->all();
            
            // Convert DD-MM-YYYY to Y-Y-m-d for validation and storage
            if (!empty($data['start_date'])) {
                try {
                    $data['start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d');
                } catch (\Exception $e) { /* ignore invalid format, validation will catch it */ }
            }
            if (!empty($data['end_date'])) {
                try {
                    $data['end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['end_date'])->format('Y-m-d');
                } catch (\Exception $e) { /* ignore */ }
            }

            // Convert recurrence_end_date format
            if (!empty($data['recurrence_end_date'])) {
                try {
                    $data['recurrence_end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['recurrence_end_date'])->format('Y-m-d');
                } catch (\Exception $e) { }
            }

            // Manually validate using the modified data
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'end_time' => 'nullable|date_format:H:i',
                'event_class' => 'required|string',
                'description' => 'nullable|string',
                'recurrence_type' => 'nullable|in:none,daily,weekly,monthly,yearly',
                'recurrence_end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $validated = $validator->validated();
            $validated['created_by'] = Auth::id();
            
            // Add optional fields that might not be in validated array
            $validated['start_time'] = $data['start_time'] ?? null;
            $validated['end_time'] = $data['end_time'] ?? null;
            $validated['recurrence_type'] = $data['recurrence_type'] ?? 'none';
            $validated['recurrence_end_date'] = $data['recurrence_end_date'] ?? null;
            
            if (empty($validated['end_date'])) {
                $validated['end_date'] = $validated['start_date'];
            }

            $event = \App\Models\CompanyCalendarEvent::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event added successfully',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing custom calendar event
     */
    public function updateCalendarEvent(Request $request)
    {
        try {
            $data = $request->all();
            
             // Convert DD-MM-YYYY to Y-Y-m-d
             if (!empty($data['start_date'])) {
                try {
                    $data['start_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d');
                } catch (\Exception $e) { }
            }
            if (!empty($data['end_date'])) {
                try {
                    $data['end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['end_date'])->format('Y-m-d');
                } catch (\Exception $e) { }
            }

            // Convert recurrence_end_date format
            if (!empty($data['recurrence_end_date'])) {
                try {
                    $data['recurrence_end_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $data['recurrence_end_date'])->format('Y-m-d');
                } catch (\Exception $e) { }
            }

            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'id' => 'required|exists:company_calendar_events,id',
                'title' => 'required|string|max:255',
                'start_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'end_time' => 'nullable|date_format:H:i',
                'event_class' => 'required|string',
                'description' => 'nullable|string',
                'recurrence_type' => 'nullable|in:none,daily,weekly,monthly,yearly',
                'recurrence_end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            if ($validator->fails()) {
                 return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            $validated = $validator->validated();

            $event = \App\Models\CompanyCalendarEvent::findOrFail($validated['id']);
            
            // Add optional fields
            $validated['start_time'] = $data['start_time'] ?? null;
            $validated['end_time'] = $data['end_time'] ?? null;
            $validated['recurrence_type'] = $data['recurrence_type'] ?? 'none';
            $validated['recurrence_end_date'] = $data['recurrence_end_date'] ?? null;
            
            if (empty($validated['end_date'])) {
                $validated['end_date'] = $validated['start_date'];
            }

            $event->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a custom calendar event
     */
    public function deleteCalendarEvent(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:company_calendar_events,id',
            ]);

            $event = \App\Models\CompanyCalendarEvent::findOrFail($request->id);
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage()
            ], 500);
        }
    }
}
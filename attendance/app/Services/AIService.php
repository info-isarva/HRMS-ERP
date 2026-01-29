<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AIService
{
    /**
     * Get AI-powered attendance features
     */
    public function getAIFeatures()
    {
        $cacheKey = 'ai_features_' . date('Y-m-d');
        return Cache::remember($cacheKey, 3600, function () { // Cache for 1 hour
            return [
                'attendance_summaries' => $this->generateAttendanceSummaries(),
                'achievement_system' => $this->getAttendanceAchievements(),
                'generated_at' => now()
            ];
        });
    }

    /**
     * Generate automated attendance summaries
     */
    private function generateAttendanceSummaries()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => $this->getDailySummary($today),
            'yesterday' => $this->getDailySummary($yesterday),
            'this_week' => $this->getWeeklySummary($thisWeek),
            'this_month' => $this->getMonthlySummary($thisMonth),
            'department_comparison' => $this->getDepartmentComparison($today)
        ];
    }

    /**
     * Get daily attendance summary
     */
    private function getDailySummary($date)
    {
        $records = AttendanceRecord::whereDate('date', $date)->get();

        if ($records->isEmpty()) {
            return [
                'total_employees' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'attendance_rate' => 0,
                'summary_text' => 'No attendance data available for this date'
            ];
        }

        $totalEmployees = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $attendanceRate = round(($presentCount / $totalEmployees) * 100, 1);

        $summaryText = "Today: {$attendanceRate}% attendance ({$absentCount} absences)";

        return [
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'attendance_rate' => $attendanceRate,
            'summary_text' => $summaryText
        ];
    }

    /**
     * Get weekly attendance summary
     */
    private function getWeeklySummary($startDate)
    {
        $endDate = $startDate->copy()->endOfWeek();
        $records = AttendanceRecord::whereBetween('date', [$startDate, $endDate])->get();

        if ($records->isEmpty()) {
            return [
                'total_records' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'average_rate' => 0,
                'summary_text' => 'No attendance data available for this week'
            ];
        }

        $totalRecords = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $averageRate = round(($presentCount / $totalRecords) * 100, 1);

        $summaryText = "This week: {$averageRate}% average attendance ({$absentCount} absences)";

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'average_rate' => $averageRate,
            'summary_text' => $summaryText
        ];
    }

    /**
     * Get monthly attendance summary
     */
    private function getMonthlySummary($startDate)
    {
        $endDate = $startDate->copy()->endOfMonth();
        $records = AttendanceRecord::whereBetween('date', [$startDate, $endDate])->get();

        if ($records->isEmpty()) {
            return [
                'total_records' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'average_rate' => 0,
                'summary_text' => 'No attendance data available for this month'
            ];
        }

        $totalRecords = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $averageRate = round(($presentCount / $totalRecords) * 100, 1);

        $summaryText = "This month: {$averageRate}% average attendance ({$absentCount} absences)";

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'average_rate' => $averageRate,
            'summary_text' => $summaryText
        ];
    }

    /**
     * Get department-wise attendance comparison
     */
    private function getDepartmentComparison($date)
    {
        // Get attendance records with user and department info
        $records = AttendanceRecord::with(['user' => function($query) {
            $query->select('id', 'name', 'payroll_department_id');
        }])
        ->whereDate('date', $date)
        ->get();

        if ($records->isEmpty()) {
            return [
                'departments' => [],
                'summary_text' => 'No department data available'
            ];
        }

        $departmentStats = [];

        foreach ($records->groupBy('user.payroll_department_id') as $deptId => $deptRecords) {
            if (!$deptId) continue; // Skip if no department

            $total = $deptRecords->count();
            $present = $deptRecords->where('status', 'present')->count();
            $rate = round(($present / $total) * 100, 1);

            $departmentStats[] = [
                'department_id' => $deptId,
                'total_employees' => $total,
                'present_count' => $present,
                'attendance_rate' => $rate
            ];
        }

        // Sort by attendance rate (highest first)
        usort($departmentStats, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });

        $topDept = $departmentStats[0] ?? null;
        $summaryText = $topDept
            ? "Department {$topDept['department_id']} leads with {$topDept['attendance_rate']}% attendance"
            : 'Department comparison data available';

        return [
            'departments' => array_slice($departmentStats, 0, 3), // Top 3 departments
            'summary_text' => $summaryText
        ];
    }

    /**
     * Get attendance achievement system
     */
    private function getAttendanceAchievements()
    {
        return [
            'perfect_attendance_streaks' => $this->getPerfectAttendanceStreaks(),
            'monthly_achievements' => $this->getMonthlyAchievements(),
            'team_achievements' => $this->getTeamAchievements(),
            'recent_milestones' => $this->getRecentMilestones()
        ];
    }

    /**
     * Get employees with perfect attendance streaks
     */
    private function getPerfectAttendanceStreaks()
    {
        $users = User::where('role', 'staff')->get();
        $streaks = [];

        foreach ($users as $user) {
            $streak = $this->calculateAttendanceStreak($user->id);
            if ($streak >= 5) { // Only show streaks of 5+ days
                $streaks[] = [
                    'employee_name' => $user->name,
                    'payroll_id' => $user->payroll_id,
                    'streak_days' => $streak,
                    'achievement_level' => $this->getStreakLevel($streak)
                ];
            }
        }

        // Sort by streak length (highest first)
        usort($streaks, function($a, $b) {
            return $b['streak_days'] <=> $a['streak_days'];
        });

        return array_slice($streaks, 0, 5); // Top 5 streaks
    }

    /**
     * Calculate attendance streak for a user
     */
    private function calculateAttendanceStreak($userId)
    {
        $streak = 0;
        $checkDate = Carbon::today();

        // Check backwards from today
        while (true) {
            $record = AttendanceRecord::where('user_id', $userId)
                ->whereDate('date', $checkDate)
                ->where('status', 'present')
                ->first();

            if (!$record) {
                break; // Streak ends
            }

            $streak++;
            $checkDate = $checkDate->subDay();
        }

        return $streak;
    }

    /**
     * Get streak achievement level
     */
    private function getStreakLevel($streak)
    {
        if ($streak >= 30) return 'Legendary (30+ days)';
        if ($streak >= 20) return 'Master (20+ days)';
        if ($streak >= 15) return 'Expert (15+ days)';
        if ($streak >= 10) return 'Advanced (10+ days)';
        return 'Rising Star (5+ days)';
    }

    /**
     * Get monthly attendance achievements
     */
    private function getMonthlyAchievements()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $records = AttendanceRecord::where('date', '>=', $startOfMonth)->get();

        $userStats = [];
        foreach ($records->groupBy('user_id') as $userId => $userRecords) {
            $user = User::find($userId);
            if (!$user) continue;

            $totalDays = $userRecords->count();
            $presentDays = $userRecords->where('status', 'present')->count();
            $rate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

            if ($rate >= 95 && $totalDays >= 10) { // At least 10 working days
                $userStats[] = [
                    'employee_name' => $user->name,
                    'payroll_id' => $user->payroll_id,
                    'attendance_rate' => $rate,
                    'working_days' => $totalDays,
                    'achievement' => $this->getMonthlyAchievement($rate)
                ];
            }
        }

        // Sort by attendance rate (highest first)
        usort($userStats, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });

        return array_slice($userStats, 0, 3); // Top 3 performers
    }

    /**
     * Get monthly achievement level
     */
    private function getMonthlyAchievement($rate)
    {
        if ($rate >= 100) return 'Perfect Attendance Champion';
        if ($rate >= 98) return 'Excellence Award';
        if ($rate >= 95) return 'Consistency Award';
        return 'Reliability Award';
    }

    /**
     * Get team achievements
     */
    private function getTeamAchievements()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $records = AttendanceRecord::with(['user' => function($query) {
            $query->select('id', 'payroll_department_id');
        }])
        ->where('date', '>=', $startOfMonth)
        ->get();

        $departmentStats = [];
        foreach ($records->groupBy('user.payroll_department_id') as $deptId => $deptRecords) {
            if (!$deptId) continue;

            $totalRecords = $deptRecords->count();
            $presentRecords = $deptRecords->where('status', 'present')->count();
            $rate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 0;

            if ($rate >= 90 && $totalRecords >= 20) { // At least 20 records
                $departmentStats[] = [
                    'department_id' => $deptId,
                    'attendance_rate' => $rate,
                    'total_records' => $totalRecords,
                    'achievement' => $this->getTeamAchievement($rate)
                ];
            }
        }

        // Sort by attendance rate (highest first)
        usort($departmentStats, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });

        return array_slice($departmentStats, 0, 3); // Top 3 teams
    }

    /**
     * Get team achievement level
     */
    private function getTeamAchievement($rate)
    {
        if ($rate >= 98) return 'Elite Team Performance';
        if ($rate >= 95) return 'High Performance Team';
        if ($rate >= 90) return 'Consistent Team Award';
        return 'Reliable Team Award';
    }

    /**
     * Get recent attendance milestones
     */
    private function getRecentMilestones()
    {
        $milestones = [];

        // Check for recent perfect attendance streaks
        $users = User::where('role', 'staff')->get();
        foreach ($users as $user) {
            $streak = $this->calculateAttendanceStreak($user->id);

            // Check for milestone streaks (10, 20, 30, etc.)
            if (in_array($streak, [10, 20, 30, 50, 100])) {
                $milestones[] = [
                    'type' => 'streak_milestone',
                    'employee_name' => $user->name,
                    'achievement' => "{$streak}-day perfect attendance streak!",
                    'icon' => 'fire',
                    'color' => 'orange'
                ];
            }
        }

        // Check for monthly perfect attendance
        $startOfMonth = Carbon::now()->startOfMonth();
        $monthlyRecords = AttendanceRecord::where('date', '>=', $startOfMonth)->get();

        foreach ($monthlyRecords->groupBy('user_id') as $userId => $userRecords) {
            $user = User::find($userId);
            if (!$user) continue;

            $totalDays = $userRecords->count();
            $presentDays = $userRecords->where('status', 'present')->count();

            if ($totalDays >= 20 && $presentDays == $totalDays) {
                $milestones[] = [
                    'type' => 'monthly_perfect',
                    'employee_name' => $user->name,
                    'achievement' => "Perfect attendance this month ({$totalDays} days)!",
                    'icon' => 'star',
                    'color' => 'yellow'
                ];
            }
        }

        // Sort by achievement significance (you can customize this logic)
        usort($milestones, function($a, $b) {
            $priority = ['streak_milestone' => 3, 'monthly_perfect' => 2];
            return ($priority[$b['type']] ?? 1) <=> ($priority[$a['type']] ?? 1);
        });

        return array_slice($milestones, 0, 5); // Recent 5 milestones
    }
}

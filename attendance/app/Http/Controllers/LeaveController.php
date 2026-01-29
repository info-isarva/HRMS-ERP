<?php
namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Auth::user()->isStaff()
            ? Auth::user()->leaveApplications()->latest()->get()
            : LeaveApplication::latest()->get();
        return view('leaves.index', compact('leaves'));
    }

    public function create()
    {
        return view('leaves.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_half_day' => 'required|in:none,first_half,second_half',
            'end_half_day' => 'required|in:none,first_half,second_half',
            'reason' => 'required|string|max:255',
            'leave_type' => 'required|string|in:sick,casual,annual,maternity,paternity,personal',
        ]);

        $totalDays = $this->calculateLeaveDays(
            $request->start_date,
            $request->end_date,
            $request->start_half_day,
            $request->end_half_day
        );

        LeaveApplication::create([
            'user_id' => Auth::id(),
            'email_id' => Auth::user()->email,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_half_day' => $request->start_half_day,
            'end_half_day' => $request->end_half_day,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'leave_type' => $request->leave_type,
            'financial_year' => $this->getCurrentFinancialYear(),
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave applied successfully');
    }

    private function calculateLeaveDays($startDate, $endDate, $startHalfDay, $endHalfDay)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $publicHolidays = PublicHoliday::where('financial_year', $this->getCurrentFinancialYear())
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        $days = 0;
        $current = $start->copy();

        while ($current <= $end) {
            if (!$current->isSunday() && !in_array($current->toDateString(), $publicHolidays)) {
                if ($current->isSameDay($start) && $startHalfDay !== 'none') {
                    $days += 0.5;
                } elseif ($current->isSameDay($end) && $endHalfDay !== 'none') {
                    $days += 0.5;
                } else {
                    $days += 1;
                }
            }
            $current->addDay();
        }

        return $days;
    }

    private function getCurrentFinancialYear()
    {
        $month = now()->month;
        $year = now()->year;
        return $month >= 4 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\PayrollApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function __construct(private PayrollApiService $payrollApi)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $listResult = $this->payrollApi->getPayslips(
            $request->filled('month') ? (int) $request->month : null,
            $request->filled('year') ? (int) $request->year : null
        ) ?? ['success' => false, 'data' => [], 'employee' => null, 'message' => 'Could not load payslips.'];

        $allPayslips = collect($listResult['data'] ?? []);
        $employee = $listResult['employee'] ?? null;
        $apiError = ($listResult['success'] ?? false) ? null : ($listResult['message'] ?? 'Could not load payslips.');

        $yearOptions = $allPayslips->pluck('year')->map(fn ($y) => (int) $y)->unique()->sortDesc()->values()->all();
        if (empty($yearOptions)) {
            $yearOptions = range((int) now()->year, (int) now()->year - 6);
        }

        $selectedMonth = $request->filled('month')
            ? (int) $request->month
            : (int) ($allPayslips->first()['month'] ?? now()->month);
        $selectedYear = $request->filled('year')
            ? (int) $request->year
            : (int) ($allPayslips->first()['year'] ?? now()->year);

        $monthsForYear = $allPayslips
            ->filter(fn ($slip) => (int) $slip['year'] === $selectedYear)
            ->pluck('month')
            ->map(fn ($m) => (int) $m)
            ->unique()
            ->sort()
            ->values();

        if ($monthsForYear->isNotEmpty() && ! $monthsForYear->contains($selectedMonth)) {
            $selectedMonth = $monthsForYear->last();
        }

        $selectedSummary = $allPayslips->first(
            fn ($slip) => (int) $slip['month'] === $selectedMonth && (int) $slip['year'] === $selectedYear
        );

        $detail = null;
        if ($allPayslips->isNotEmpty() && $selectedSummary) {
            $detailResult = $this->payrollApi->getPayslipDetails($selectedMonth, $selectedYear);
            $detail = ($detailResult['success'] ?? false) ? ($detailResult['data'] ?? null) : null;
        }

        $stats = [
            'total' => $allPayslips->count(),
            'selected_period' => $selectedSummary['period_label'] ?? ($detail['period_label'] ?? null),
            'selected_net' => $selectedSummary['net_pay'] ?? ($detail['net_pay'] ?? null),
        ];

        return view('payslips.index', [
            'allPayslips' => $allPayslips,
            'employee' => $employee,
            'detail' => $detail,
            'apiError' => $apiError,
            'stats' => $stats,
            'yearOptions' => $yearOptions,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthsForYear' => $monthsForYear,
            'selectedSummary' => $selectedSummary,
        ]);
    }

    public function download(Request $request, int $month, int $year)
    {
        if ($month < 1 || $month > 12 || $year < 2000) {
            abort(404);
        }

        $response = $this->payrollApi->downloadPayslipPdf($month, $year, true);

        if (! $response) {
            return redirect()
                ->route('payslips.index', ['month' => $month, 'year' => $year])
                ->with('error', 'Payslip is not available for the selected period. It may not be finalized yet.');
        }

        $filename = sprintf('payslip-%02d-%d.pdf', $month, $year);

        return response($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function view(Request $request, int $month, int $year)
    {
        if ($month < 1 || $month > 12 || $year < 2000) {
            abort(404);
        }

        $response = $this->payrollApi->downloadPayslipPdf($month, $year, false);

        if (! $response) {
            return redirect()
                ->route('payslips.index', ['month' => $month, 'year' => $year])
                ->with('error', 'Payslip is not available for the selected period.');
        }

        $filename = sprintf('payslip-%02d-%d.pdf', $month, $year);

        return response($response->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}

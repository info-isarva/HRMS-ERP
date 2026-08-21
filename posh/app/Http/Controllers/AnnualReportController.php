<?php

namespace App\Http\Controllers;

use App\Models\PoshAnnualReport;
use App\Services\PoshComplianceService;
use Illuminate\Http\Request;

class AnnualReportController extends Controller
{
    public function __construct(protected PoshComplianceService $compliance) {}

    public function index(Request $request)
    {
        $reports = PoshAnnualReport::where('organization_id', $request->user()->organization_id)
            ->orderByDesc('report_year')
            ->get();

        return view('reports.annual-index', compact('reports'));
    }

    public function generate(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $report = $this->compliance->saveAnnualReport(
            $request->user()->organization_id,
            $year,
            $request->user()->id
        );

        return redirect()->route('reports.annual.show', $report)->with('success', 'Annual report generated.');
    }

    public function show(Request $request, PoshAnnualReport $report)
    {
        abort_unless($report->organization_id === $request->user()->organization_id, 403);

        return view('reports.annual-show', ['report' => $report, 'data' => $report->report_data]);
    }

    public function markSubmitted(Request $request, PoshAnnualReport $report)
    {
        abort_unless($report->organization_id === $request->user()->organization_id, 403);
        $report->update(['submitted_at' => now()]);

        return back()->with('success', 'Marked as submitted to District Officer.');
    }

    public function export(Request $request, PoshAnnualReport $report)
    {
        abort_unless($report->organization_id === $request->user()->organization_id, 403);

        return view('reports.annual-export', ['report' => $report, 'data' => $report->report_data]);
    }
}

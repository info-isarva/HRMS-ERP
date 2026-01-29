<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Models\FinancialYearSetting;
use App\Models\FinancialYearReport;
use App\Services\FinancialYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FinancialYearController extends Controller
{
    protected $financialYearService;

    public function __construct(FinancialYearService $financialYearService)
    {
        $this->financialYearService = $financialYearService;
    }

    /**
     * Display financial years index
     */
    public function index(Request $request)
    {
        $query = FinancialYear::query();
        
        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'current':
                    $query->where('is_current', true);
                    break;
                case 'closed':
                    $query->where('is_closed', true);
                    break;
                case 'open':
                    $query->where('is_closed', false);
                    break;
            }
        }
        
        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $financialYears = $query->orderBy('start_date', 'desc')->paginate(10);
        $currentFY = $this->financialYearService->getCurrentFinancialYear();
        
        return view('financial-years.index', compact('financialYears', 'currentFY'));
    }

    /**
     * Show financial year details
     */
    public function show(FinancialYear $financialYear)
    {
        $statistics = $this->financialYearService->getFinancialYearStatistics($financialYear);
        $reports = $financialYear->reports()->orderBy('generated_at', 'desc')->get();
        
        return view('financial-years.show', compact('financialYear', 'statistics', 'reports'));
    }

    /**
     * Create new financial year
     */
    public function create()
    {
        $settings = FinancialYearSetting::getSettings();
        return view('financial-years.create', compact('settings'));
    }

    /**
     * Store new financial year
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'name' => 'required|string|max:255|unique:financial_years,name',
            'description' => 'nullable|string',
            'is_current' => 'boolean',
        ]);

        try {
            $financialYear = FinancialYear::create($request->only([
                'name', 'start_date', 'end_date', 'description', 'is_current'
            ]));

            // If this is set as current, update others
            if ($request->is_current) {
                FinancialYear::where('id', '!=', $financialYear->id)
                    ->update(['is_current' => false]);
            }

            // Sync to attendance
            $this->financialYearService->syncFinancialYearToAttendance($financialYear);

            return redirect()->route('financial-years.index')
                ->with('success', 'Financial year created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create financial year: ' . $e->getMessage()]);
        }
    }

    /**
     * Close financial year
     */
    public function close(FinancialYear $financialYear)
    {
        try {
            if ($financialYear->is_closed) {
                return back()->withErrors(['error' => 'Financial year is already closed.']);
            }

            $this->financialYearService->closeFinancialYear($financialYear);

            return back()->with('success', 'Financial year closed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to close financial year: ' . $e->getMessage()]);
        }
    }

    /**
     * Set as current financial year
     */
    public function setCurrent(FinancialYear $financialYear)
    {
        try {
            if ($financialYear->is_closed) {
                return back()->withErrors(['error' => 'Cannot set a closed financial year as current.']);
            }

            // Update all FYs to not current
            FinancialYear::query()->update(['is_current' => false]);
            
            // Set this one as current
            $financialYear->update(['is_current' => true]);

            // Sync to attendance
            $this->financialYearService->syncFinancialYearToAttendance($financialYear);

            return back()->with('success', 'Financial year set as current successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to set current financial year: ' . $e->getMessage()]);
        }
    }

    /**
     * Financial year settings
     */
    public function settings()
    {
        $settings = FinancialYearSetting::getSettings();
        return view('financial-years.settings', compact('settings'));
    }

    /**
     * Update financial year settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'start_month' => 'required|integer|min:1|max:12',
            'auto_close_enabled' => 'boolean',
            'auto_close_days_after' => 'required|integer|min:1|max:365',
            'auto_create_next' => 'boolean',
            'create_next_days_before' => 'required|integer|min:1|max:365',
            'closing_policy' => 'nullable|string',
        ]);

        try {
            $settings = FinancialYearSetting::getSettings();
            $settings->update($request->only([
                'start_month',
                'auto_close_enabled',
                'auto_close_days_after',
                'auto_create_next',
                'create_next_days_before',
                'closing_policy',
            ]));

            // Update notification settings if provided
            if ($request->has('notification_settings')) {
                $settings->update(['notification_settings' => $request->notification_settings]);
            }

            return back()->with('success', 'Financial year settings updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate report
     */
    public function generateReport(Request $request, FinancialYear $financialYear)
    {
        $request->validate([
            'report_type' => 'required|string|in:annual_summary,payroll_summary,department_summary,monthly_summary',
            'format' => 'required|string|in:pdf,excel,csv',
        ]);

        try {
            $reportData = $this->generateReportData($financialYear, $request->report_type);
            
            $report = FinancialYearReport::create([
                'financial_year_id' => $financialYear->id,
                'report_type' => $request->report_type,
                'report_name' => $this->getReportName($request->report_type, $financialYear),
                'report_data' => $reportData,
                'file_type' => $request->format,
                'generated_at' => now(),
                'generated_by' => auth()->id(),
                'status' => 'completed',
            ]);

            // Generate and save file
            $filePath = $this->generateReportFile($report, $request->format);
            $report->update(['file_path' => $filePath, 'file_size' => Storage::size($filePath)]);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully.',
                'download_url' => route('financial-year.reports.download', $report->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download report
     */
    public function downloadReport(FinancialYearReport $report)
    {
        if (!$report->fileExists()) {
            abort(404, 'Report file not found.');
        }

        return Storage::download($report->file_path, $report->report_name . '.' . $report->file_type);
    }

    /**
     * API endpoint to get current financial year
     */
    public function apiCurrent()
    {
        $currentFY = $this->financialYearService->getCurrentFinancialYear();
        $settings = FinancialYearSetting::getSettings();

        return response()->json([
            'financial_year' => $currentFY,
            'settings' => $settings,
        ]);
    }

    /**
     * API endpoint to get financial year by date
     */
    public function apiByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        
        $financialYear = $this->financialYearService->getFinancialYearByDate($request->date);
        
        return response()->json(['financial_year' => $financialYear]);
    }

    /**
     * Run financial year maintenance tasks
     */
    public function runMaintenance()
    {
        try {
            $this->financialYearService->autoCloseExpiredFinancialYears();
            $this->financialYearService->autoCreateNextFinancialYear();
            
            return response()->json([
                'success' => true,
                'message' => 'Financial year maintenance completed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Maintenance failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate report data based on type
     */
    private function generateReportData(FinancialYear $financialYear, $reportType)
    {
        return match($reportType) {
            'annual_summary' => $this->financialYearService->getFinancialYearStatistics($financialYear),
            'payroll_summary' => $this->getPayrollSummaryReport($financialYear),
            'department_summary' => $this->getDepartmentSummaryReport($financialYear),
            'monthly_summary' => $this->getMonthlySummaryReport($financialYear),
            default => [],
        };
    }

    /**
     * Get report name
     */
    private function getReportName($reportType, $financialYear)
    {
        $names = [
            'annual_summary' => 'Annual Summary Report',
            'payroll_summary' => 'Payroll Summary Report',
            'department_summary' => 'Department-wise Summary Report',
            'monthly_summary' => 'Monthly Summary Report',
        ];
        
        return ($names[$reportType] ?? 'Report') . ' - ' . $financialYear->name;
    }

    /**
     * Generate report file
     */
    private function generateReportFile(FinancialYearReport $report, $format)
    {
        $fileName = 'reports/financial-year/' . $report->id . '_' . time() . '.' . $format;
        
        // Generate content based on format
        $content = match($format) {
            'csv' => $this->generateCSVContent($report),
            'excel' => $this->generateExcelContent($report),
            'pdf' => $this->generatePDFContent($report),
            default => json_encode($report->report_data),
        };
        
        Storage::put($fileName, $content);
        
        return $fileName;
    }

    /**
     * Generate CSV content
     */
    private function generateCSVContent(FinancialYearReport $report)
    {
        // Simple CSV generation - you can enhance this
        $data = $report->report_data;
        $csv = "Financial Year Report - {$report->report_name}\n\n";
        $csv .= "Generated At: " . $report->generated_at->format('d M Y H:i:s') . "\n\n";
        
        // Add data rows
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $csv .= "\n{$key}:\n";
                foreach ($value as $subKey => $subValue) {
                    $csv .= "{$subKey},{$subValue}\n";
                }
            } else {
                $csv .= "{$key},{$value}\n";
            }
        }
        
        return $csv;
    }

    /**
     * Generate Excel content (simplified)
     */
    private function generateExcelContent(FinancialYearReport $report)
    {
        // For now, return CSV format - you can integrate PhpSpreadsheet
        return $this->generateCSVContent($report);
    }

    /**
     * Generate PDF content (simplified)
     */
    private function generatePDFContent(FinancialYearReport $report)
    {
        // For now, return HTML format - you can integrate DomPDF or similar
        $data = $report->report_data;
        $html = "<html><body>";
        $html .= "<h1>Financial Year Report - {$report->report_name}</h1>";
        $html .= "<p>Generated At: " . $report->generated_at->format('d M Y H:i:s') . "</p>";
        $html .= "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
        $html .= "</body></html>";
        
        return $html;
    }

    /**
     * Get payroll summary report
     */
    private function getPayrollSummaryReport(FinancialYear $financialYear)
    {
        // Implementation for payroll summary
        return $this->financialYearService->getFinancialYearStatistics($financialYear)['payroll_summary'];
    }

    /**
     * Get department summary report
     */
    private function getDepartmentSummaryReport(FinancialYear $financialYear)
    {
        // Implementation for department summary
        return [];
    }

    /**
     * Get monthly summary report
     */
    private function getMonthlySummaryReport(FinancialYear $financialYear)
    {
        // Implementation for monthly summary
        return [];
    }

    /**
     * Switch financial year for user session
     */
    public function switchFinancialYear(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required|exists:financial_years,id'
        ]);

        $financialYear = FinancialYear::find($request->financial_year_id);
        
        // Store in session
        session(['selected_financial_year_id' => $financialYear->id]);
        
        return response()->json([
            'success' => true,
            'message' => "Switched to Financial Year: {$financialYear->name}",
            'financial_year' => [
                'id' => $financialYear->id,
                'name' => $financialYear->name,
                'start_date' => $financialYear->start_date->format('d M Y'),
                'end_date' => $financialYear->end_date->format('d M Y'),
                'is_current' => $financialYear->is_current,
                'is_closed' => $financialYear->is_closed,
            ]
        ]);
    }

    /**
     * Get current selected financial year info
     */
    public function getCurrentSelected()
    {
        $selectedFYId = session('selected_financial_year_id');
        $selectedFY = $selectedFYId ? FinancialYear::find($selectedFYId) : $this->financialYearService->getCurrentFinancialYear();
        
        if (!$selectedFY) {
            return response()->json(['error' => 'No financial year found'], 404);
        }

        return response()->json([
            'financial_year' => [
                'id' => $selectedFY->id,
                'name' => $selectedFY->name,
                'start_date' => $selectedFY->start_date->format('d M Y'),
                'end_date' => $selectedFY->end_date->format('d M Y'),
                'is_current' => $selectedFY->is_current,
                'is_closed' => $selectedFY->is_closed,
                'progress_percentage' => $selectedFY->getProgressPercentage(),
            ]
        ]);
    }
}

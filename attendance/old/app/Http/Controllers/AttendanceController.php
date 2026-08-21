<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Exports\AttendanceTemplateExport;
use App\Exports\AttendanceExport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\BiometricImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class AttendanceController extends Controller
{
    /**
     * Display the attendance upload page
     */
    public function index()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'today_attendance' => Attendance::where('date', today())->count(),
            'recent_uploads' => Attendance::where('source', 'biometric_excel')
                ->select('processed_at', DB::raw('count(*) as records'))
                ->groupBy('processed_at')
                ->orderBy('processed_at', 'desc')
                ->limit(5)
                ->get()
        ];

        // Get available biometric formats
        $biometricService = new BiometricImportService();
        $formats = $biometricService->getAvailableParsers();

        return view('attendances.index', compact('stats', 'formats'));
    }

    /**
     * Upload and process Excel file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:51200', // 50MB max
        ]);

        try {
            // Ensure temp directory exists
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Store the uploaded file temporarily
            $file = $request->file('excel_file');
            $fileName = 'attendance_upload_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp', $fileName);

            // Verify file was stored successfully
            $fullPath = storage_path('app/' . $filePath);
            if (!file_exists($fullPath)) {
                throw new \Exception("Failed to store uploaded file. File path: {$fullPath}");
            }

            // Import the data
            $import = new AttendanceImport();
            Excel::import($import, $fullPath);

            // Get results
            $results = $import->getResults();

            // Clean up temp file
            Storage::delete($filePath);

            if ($results['success']) {
                return redirect()->back()->with('success',
                    "Successfully processed {$results['processed']} attendance records from Excel file."
                );
            } else {
                $errorMessage = "Processed {$results['processed']} records with " . count($results['errors']) . " errors:\n";
                foreach ($results['errors'] as $error) {
                    $errorMessage .= "Row {$error['row']}: {$error['error']}\n";
                }

                return redirect()->back()->with('warning', $errorMessage);
            }

        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessage = "Validation errors found:\n";

            foreach ($failures as $failure) {
                $errorMessage .= "Row {$failure->row()}: " . implode(', ', $failure->errors()) . "\n";
            }

            return redirect()->back()->with('error', $errorMessage);

        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                'Error processing file: ' . $e->getMessage()
            );
        }
    }

    /**
     * Download sample Excel template
     */
    public function downloadTemplate()
    {
        $fileName = 'attendance_import_template_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new AttendanceTemplateExport(), $fileName);
    }

    /**
     * Generate sample data for template
     */
    private function generateSampleData()
    {
        $employees = Employee::limit(10)->get();
        $sampleData = [];

        // Header row
        $sampleData[] = [
            'payroll_id',
            'date',
            'check_in_time',
            'check_out_time',
            'shift_name',
            'department',
            'notes'
        ];

        // Sample data rows
        foreach ($employees as $employee) {
            $sampleData[] = [
                $employee->payroll_id,
                now()->format('Y-m-d'),
                '09:00',
                '17:00',
                'Regular Shift',
                $employee->department->name ?? 'General',
                'Sample attendance record'
            ];
        }

        return $sampleData;
    }

    /**
     * Show attendance records with filtering
     */
    public function records(Request $request)
    {
        $query = Attendance::with(['employee', 'shift']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_payroll_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $attendances = $query->orderBy('date', 'desc')
                            ->orderBy('employee_payroll_id')
                            ->paginate(50);

        // Only employees with a payroll_id belong in this filter (value must not be empty,
        // otherwise null payroll_id matches "All Employees" and looks pre-selected).
        $employees = Employee::select('id', 'payroll_id', 'name')
            ->whereNotNull('payroll_id')
            ->where('payroll_id', '!=', '')
            ->orderBy('name')
            ->get();
        $stats = $this->getAttendanceStats();

        return view('attendances.records', compact('attendances', 'employees', 'stats'));
    }

    /**
     * Get attendance statistics
     */
    private function getAttendanceStats()
    {
        return [
            'total_records' => Attendance::count(),
            'today_records' => Attendance::where('date', today())->count(),
            'present_today' => Attendance::where('date', today())->where('status', 'present')->count(),
            'late_today' => Attendance::where('date', today())->where('status', 'late')->count(),
            'absent_today' => Attendance::where('date', today())->where('status', 'absent')->count(),
            'by_source' => Attendance::select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->get()
                ->pluck('count', 'source')
        ];
    }

    /**
     * Delete attendance records by date range
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'source' => 'nullable|string'
        ]);

        $query = Attendance::whereBetween('date', [$request->date_from, $request->date_to]);

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $count = $query->count();

        if ($count > 0) {
            $query->delete();
            return redirect()->back()->with('success', "Deleted {$count} attendance records.");
        }

        return redirect()->back()->with('info', 'No records found to delete.');
    }

    /**
     * Export attendance data to Excel
     */
    public function export(Request $request)
    {
        $query = Attendance::query();

        // Apply filters if provided
        if ($request->filled('employee_id')) {
            $query->where('employee_payroll_id', $request->employee_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Get last 30 days if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->where('date', '>=', now()->subDays(30));
        }

        $query->orderBy('date', 'desc')->orderBy('employee_payroll_id');

        $fileName = 'attendance_export_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new AttendanceExport($query), $fileName);
    }

    /**
     * Upload biometric device file (multi-format support)
     */
    public function uploadBiometric(Request $request)
    {
        $request->validate([
            'biometric_file' => 'required|file|max:10240', // 10MB max
            'device_format' => 'required|string|in:zkteco,essl,realtime,generic_csv'
        ]);

        try {
            $biometricService = new BiometricImportService();

            // Ensure temp directory exists
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Store the uploaded file temporarily
            $file = $request->file('biometric_file');
            $fileName = 'biometric_upload_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store file and get the path
            $storedPath = $file->storeAs('temp', $fileName);
            $fullPath = storage_path('app/private/' . $storedPath);

            // Verify file was stored successfully
            if (!file_exists($fullPath)) {
                throw new \Exception("Failed to store uploaded file. Path: {$fullPath}");
            }

            // Import using the selected format
            $results = $biometricService->import($fullPath, $request->device_format);

            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($results['success']) {
                return redirect()->back()->with('success',
                    "Successfully processed {$results['processed']} records. " .
                    "New: {$results['imported']}, Updated: {$results['updated']}"
                );
            } else {
                $errorMessage = "Processed {$results['processed']} records with " . count($results['errors']) . " errors.\n\n";
                foreach (array_slice($results['errors'], 0, 10) as $error) {
                    $errorMessage .= "Employee {$error['employee_id']} ({$error['date']}): {$error['error']}\n";
                }
                if (count($results['errors']) > 10) {
                    $errorMessage .= "\n... and " . (count($results['errors']) - 10) . " more errors.";
                }

                return redirect()->back()->with('warning', $errorMessage);
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                'Error processing biometric file: ' . $e->getMessage()
            );
        }
    }

    /**
     * Auto-detect biometric file format
     */
    public function detectFormat(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240'
        ]);

        try {
            $biometricService = new BiometricImportService();

            // Ensure temp directory exists
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Store file temporarily
            $file = $request->file('file');
            $fileName = 'detect_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store file and get path
            $storedPath = $file->storeAs('temp', $fileName);
            $fullPath = storage_path('app/private/' . $storedPath);

            // Detect format
            $format = $biometricService->detectFormat($fullPath);

            // Clean up
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if ($format) {
                $parser = $biometricService->getParser($format);
                return response()->json([
                    'success' => true,
                    'format' => $format,
                    'format_name' => $parser->getFormatName()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not detect format automatically. Please select manually.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error detecting format: ' . $e->getMessage()
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs page
     */
    public function index()
    {
        // Check authentication and permission
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        if (!Auth::user()->can('activityLogs.view')) {
            return redirect()->route('home')->with('error', 'Access denied. You do not have permission to view activity logs.');
        }

        // Get filter options
        $users = User::select('user_id', 'name')->get();
        $activityTypes = ActivityLog::distinct()->pluck('activity_type');
        $modules = ActivityLog::distinct()->pluck('module');

        // Get analytics data
        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::whereDate('activity_timestamp', today())->count();
        $weeklyLogs = ActivityLog::where('activity_timestamp', '>=', now()->subDays(7))->count();
        $activeUsers = ActivityLog::distinct()
            ->where('activity_timestamp', '>=', now()->subDays(7))
            ->count('user_id');
        $securityEvents = ActivityLog::where('activity_type', 'LIKE', '%security%')
            ->orWhere('activity_type', 'LIKE', '%login%')
            ->orWhere('activity_type', 'LIKE', '%logout%')
            ->orWhere('activity_type', 'LIKE', '%auth%')
            ->count();

        return view('activitylogs.index', compact('users', 'activityTypes', 'modules', 'totalLogs', 'todayLogs', 'weeklyLogs', 'activeUsers', 'securityEvents'));
    }

    /**
     * Get activity logs data for DataTable
     */
    public function getActivityLogsData(Request $request)
    {
        try {
            // Check authentication and permission
            if (!Auth::check() || !Auth::user()->can('activityLogs.view')) {
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            $draw = $request->input('draw');
            $start = $request->input('start');
            $rowPerPage = $request->input('length');
            $columnIndex_arr = $request->input('order');
            $columnName_arr = $request->input('columns');
            $order_arr = $request->input('order');
            $search_arr = $request->input('search');

            $columnIndex = $columnIndex_arr[0]['column'] ?? 0;
            $columnName = $columnName_arr[$columnIndex]['data'] ?? 'activity_timestamp';
            $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
            $searchValue = $search_arr['value'] ?? '';

            // Build query
            $query = ActivityLog::query();

            // Apply filters
            if ($request->filled('search_query')) {
                $searchQuery = $request->search_query;
                $query->where(function($q) use ($searchQuery) {
                    $q->where('description', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('user_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('activity_type', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('module', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('ip_address', 'LIKE', "%{$searchQuery}%");
                });
            }

            if ($request->filled('user_filter')) {
                $query->where('user_id', $request->user_filter);
            }

            if ($request->filled('activity_type_filter')) {
                $query->where('activity_type', $request->activity_type_filter);
            }

            if ($request->filled('module_filter')) {
                $query->where('module', $request->module_filter);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->whereBetween('activity_timestamp', [$dateFrom, $dateTo]);
            }

            // Apply search
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('user_name', 'like', "%{$searchValue}%")
                      ->orWhere('description', 'like', "%{$searchValue}%")
                      ->orWhere('activity_type', 'like', "%{$searchValue}%")
                      ->orWhere('module', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('ip_address', 'like', "%{$searchValue}%");
                });
            }

            $totalRecords = ActivityLog::count();
            $totalRecordsWithFilter = $query->count();

            // Apply ordering and pagination
            $records = $query->orderBy($columnName, $columnSortOrder)
                           ->skip($start)
                           ->take($rowPerPage)
                           ->get();

            $data_arr = [];
            foreach ($records as $key => $record) {
                $data_arr[] = [
                    'id' => $record->id, // Add the ID field for DataTable
                    'no' => $start + $key + 1,
                    'activity_timestamp' => $record->activity_timestamp->format('d-m-Y H:i:s'),
                    'user_name' => $record->user_name ?? 'N/A',
                    'user_id' => $record->user_id ?? 'N/A',
                    'email' => $record->email ?? 'N/A',
                    'role_name' => $record->role_name ?? 'N/A',
                    'activity_type' => '<span class="badge ' . $this->getActivityTypeBadgeClass($record->activity_type) . '">' . $record->activity_type . '</span>',
                    'module' => '<span class="badge badge-secondary">' . $record->module . '</span>',
                    'description' => $this->truncateDescription($record->description),
                    'ip_address' => $record->ip_address ?? 'N/A',
                    'action' => '<button type="button" class="btn btn-sm btn-info view-details" data-id="' . $record->id . '">
                                    <i class="fa fa-eye"></i> View Details
                                </button>'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecordsWithFilter,
                'data' => $data_arr
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getActivityLogsData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while fetching data',
                'message' => $e->getMessage(),
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    /**
     * Get activity log details
     */
    public function getActivityLogDetails(Request $request)
    {
        try {
            // Check authentication and permission
            if (!Auth::check() || !Auth::user()->can('activityLogs.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }
            
            $logId = $request->input('id');
            
            if (!$logId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No log ID provided.'
                ], 400);
            }
            
            $log = ActivityLog::find($logId);
            
            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity log not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $log->id,
                    'activity_timestamp' => $log->activity_timestamp ? $log->activity_timestamp->format('d-m-Y H:i:s') : 'N/A',
                    'user_name' => $log->user_name,
                    'user_id' => $log->user_id,
                    'email' => $log->email,
                    'phone_number' => $log->phone_number,
                    'role_name' => $log->role_name,
                    'activity_type' => $log->activity_type,
                    'module' => $log->module,
                    'description' => $log->description,
                    'old_data' => $log->old_data,
                    'new_data' => $log->new_data,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'session_id' => $log->session_id
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getActivityLogDetails: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity log details. Please try again.'
            ], 500);
        }
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        try {
            // Check permission
            if (!Auth::check() || !Auth::user()->can('activityLogs.view')) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $format = $request->input('format', 'csv');
            
            // Build query with filters
            $query = ActivityLog::query();

            if ($request->filled('search_query')) {
                $searchQuery = $request->search_query;
                $query->where(function($q) use ($searchQuery) {
                    $q->where('description', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('user_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('activity_type', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('module', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('ip_address', 'LIKE', "%{$searchQuery}%");
                });
            }

            if ($request->filled('user_filter')) {
                $query->where('user_id', $request->user_filter);
            }

            if ($request->filled('activity_type_filter')) {
                $query->where('activity_type', $request->activity_type_filter);
            }

            if ($request->filled('module_filter')) {
                $query->where('module', $request->module_filter);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->whereBetween('activity_timestamp', [$dateFrom, $dateTo]);
            }

            $records = $query->orderBy('activity_timestamp', 'desc')->get();

            switch ($format) {
                case 'csv':
                    return $this->exportCSV($records);
                case 'excel':
                case 'xlsx':
                    return $this->exportExcel($records);
                case 'json':
                    return $this->exportJSON($records);
                case 'pdf':
                    return $this->exportPDF($records);
                default:
                    return $this->exportCSV($records);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear activity logs (soft delete with backup export)
     */
    public function clearLogs(Request $request)
    {
        try {
            // Check permission
            if (!in_array(Session::get('role_name'), ['Admin', 'Super Admin'])) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $cutoffDate = $request->input('cutoff_date');

            // Get logs to be deleted for export
            $query = ActivityLog::query();
            if ($cutoffDate) {
                $query->where('activity_timestamp', '<', $cutoffDate);
            }
            $logsToDelete = $query->get();

            if ($logsToDelete->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => $cutoffDate ? "No logs found older than {$cutoffDate}." : "No logs found to clear."
                ]);
            }

            // Export logs before deletion as backup
            $exportData = $this->prepareExportData($logsToDelete);
            $exportResult = $this->createBackupExport($exportData);

            if (!$exportResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create backup export. Operation cancelled to prevent data loss.'
                ], 500);
            }

            // Perform soft delete
            $deletedCount = $query->delete(); // This will be soft delete now

            // Log the manual clear activity
            ActivityLogService::log(
                'DELETE',
                'SYSTEM',
                "Manually cleared {$deletedCount} activity logs" . ($cutoffDate ? " older than {$cutoffDate}" : "") . ". Backup exported to: {$exportResult['filename']}",
                null,
                [
                    'action' => 'manual_clear',
                    'cutoff_date' => $cutoffDate,
                    'deleted_count' => $deletedCount,
                    'backup_filename' => $exportResult['filename'],
                    'performed_by' => Session::get('user_id')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$deletedCount} activity log records" . ($cutoffDate ? " older than {$cutoffDate}" : "") . ". Backup created: {$exportResult['filename']}",
                'backup_filename' => $exportResult['filename']
            ]);

        } catch (\Exception $e) {
            \Log::error('Activity logs clear failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Clear failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cleanup old activity logs
     */
    public function cleanup(Request $request)
    {
        try {
            // Check authentication and permissions
            $roleNames = Session::get('role_name');
            $userId = Session::get('user_id');
            
            if (!$userId || !$roleNames || !in_array($roleNames, ['Admin', 'Super Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.'
                ], 403);
            }

            // Define cutoff date (1 week ago instead of 1 month)
            $cutoffDate = Carbon::now()->subWeeks(1);
            
            // Count logs to be deleted
            $logsToDelete = ActivityLog::where('activity_timestamp', '<', $cutoffDate)->count();
            
            if ($logsToDelete === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No old logs found to cleanup.',
                    'deleted_count' => 0
                ]);
            }

            // Delete old logs
            $deletedCount = ActivityLog::where('activity_timestamp', '<', $cutoffDate)->delete();
            
            // Log the cleanup activity
            ActivityLogService::log(
                'CLEANUP',
                'SYSTEM',
                "Cleaned up {$deletedCount} old activity logs (older than 1 week)",
                null,
                [
                    'cutoff_date' => $cutoffDate->toDateTimeString(),
                    'deleted_count' => $deletedCount,
                    'performed_by' => $userId
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Old logs cleaned up successfully.',
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Activity logs cleanup failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get activity type badge class
     */
    private function getActivityTypeBadgeClass($activityType)
    {
        $badgeClasses = [
            'CREATE' => 'badge-success',
            'UPDATE' => 'badge-warning',
            'DELETE' => 'badge-danger',
            'LOGIN' => 'badge-info',
            'LOGOUT' => 'badge-secondary',
            'FAILED_LOGIN' => 'badge-danger',
            'PASSWORD_CHANGE' => 'badge-warning',
            'PROCESS' => 'badge-primary',
            'GENERATE' => 'badge-info',
            'UPLOAD' => 'badge-success',
            'EXPORT' => 'badge-warning',
            'SYNC' => 'badge-primary',
            'CALCULATE' => 'badge-primary'
        ];

        return $badgeClasses[$activityType] ?? 'badge-dark';
    }

    /**
     * Truncate description for table display
     */
    private function truncateDescription($description, $length = 100)
    {
        if (strlen($description) > $length) {
            return substr($description, 0, $length) . '...';
        }
        return $description;
    }

    /**
     * Export to CSV
     */
    private function exportCSV($records)
    {
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Timestamp',
                'User ID',
                'User Name',
                'Email',
                'Role',
                'Activity Type',
                'Module',
                'Description',
                'IP Address',
                'User Agent'
            ]);

            // Data rows
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->activity_timestamp->format('d-m-Y H:i:s'),
                    $record->user_id,
                    $record->user_name,
                    $record->email,
                    $record->role_name,
                    $record->activity_type,
                    $record->module,
                    $record->description,
                    $record->ip_address,
                    $record->user_agent
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel
     */
    private function exportExcel($records)
    {
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('HRMS Payroll System')
            ->setTitle('Activity Logs Export')
            ->setSubject('Activity Logs')
            ->setDescription('Exported activity logs from HRMS Payroll System');
        
        // Set column headers
        $headers = [
            'A1' => 'Timestamp',
            'B1' => 'User ID', 
            'C1' => 'User Name',
            'D1' => 'Email',
            'E1' => 'Role',
            'F1' => 'Activity Type',
            'G1' => 'Module',
            'H1' => 'Description',
            'I1' => 'IP Address',
            'J1' => 'User Agent'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style the header row
        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF4472C4',
                ],
            ],
            'font' => [
                'color' => [
                    'argb' => 'FFFFFFFF',
                ],
                'bold' => true,
                'size' => 12,
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(40);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(30);
        
        // Add data rows
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record->activity_timestamp->format('d-m-Y H:i:s'));
            $sheet->setCellValue('B' . $row, $record->user_id);
            $sheet->setCellValue('C' . $row, $record->user_name);
            $sheet->setCellValue('D' . $row, $record->email);
            $sheet->setCellValue('E' . $row, $record->role_name);
            $sheet->setCellValue('F' . $row, $record->activity_type);
            $sheet->setCellValue('G' . $row, $record->module);
            $sheet->setCellValue('H' . $row, $record->description);
            $sheet->setCellValue('I' . $row, $record->ip_address);
            $sheet->setCellValue('J' . $row, $record->user_agent);
            $row++;
        }
        
        // Auto-fit all columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and output
        $writer = new Xlsx($spreadsheet);
        
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function() use ($writer) {
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF using DomPDF
     */
    private function exportPDF($records)
    {
        // Prepare data array for view (avoid embedding long user agent raw strings directly if huge)
        $exportRows = $records->map(function($record){
            return [
                'timestamp' => $record->activity_timestamp->format('d-m-Y H:i:s'),
                'user_id' => $record->user_id,
                'user_name' => $record->user_name,
                'email' => $record->email,
                'role_name' => $record->role_name,
                'activity_type' => $record->activity_type,
                'module' => $record->module,
                'description' => $record->description,
                'ip_address' => $record->ip_address,
                'user_agent' => $record->user_agent ? substr($record->user_agent,0,140) : null,
            ];
        });

        // Render PDF view
        $pdf = Pdf::loadView('activitylogs.export_pdf', [
            'rows' => $exportRows,
            'generated_at' => now()->format('d-m-Y H:i:s'),
            'total' => $records->count()
        ])->setPaper('a4', 'portrait');

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export to JSON
     */
    private function exportJSON($records)
    {
        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.json';
        
        $data = $records->map(function($record) {
            return [
                'timestamp' => $record->activity_timestamp->format('d-m-Y H:i:s'),
                'user_id' => $record->user_id,
                'user_name' => $record->user_name,
                'email' => $record->email,
                'role_name' => $record->role_name,
                'activity_type' => $record->activity_type,
                'module' => $record->module,
                'description' => $record->description,
                'old_data' => $record->old_data,
                'new_data' => $record->new_data,
                'ip_address' => $record->ip_address,
                'user_agent' => $record->user_agent,
                'session_id' => $record->session_id
            ];
        });

        return response()->json($data)
                        ->header('Content-Type', 'application/json')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Prepare export data from activity logs
     */
    private function prepareExportData($logs)
    {
        return $logs->map(function($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->activity_timestamp->format('d-m-Y H:i:s'),
                'user_id' => $log->user_id,
                'user_name' => $log->user_name,
                'email' => $log->email,
                'phone_number' => $log->phone_number,
                'role_name' => $log->role_name,
                'activity_type' => $log->activity_type,
                'module' => $log->module,
                'description' => $log->description,
                'old_data' => $log->old_data,
                'new_data' => $log->new_data,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'session_id' => $log->session_id,
                'created_at' => $log->created_at?->format('d-m-Y H:i:s'),
                'updated_at' => $log->updated_at?->format('d-m-Y H:i:s')
            ];
        });
    }

    /**
     * Create backup export file
     */
    private function createBackupExport($data)
    {
        try {
            $filename = 'activity_logs_backup_' . date('Y-m-d_H-i-s') . '.json';
            $backupPath = storage_path('backups/activity_logs');
            
            // Ensure directory exists
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filePath = $backupPath . '/' . $filename;
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $filePath
            ];
            
        } catch (\Exception $e) {
            \Log::error('Backup export creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

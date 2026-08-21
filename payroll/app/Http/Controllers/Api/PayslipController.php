<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmployeePayslipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayslipController extends Controller
{
    public function __construct(private EmployeePayslipService $payslips)
    {
    }

    /**
     * GET /api/payslips
     */
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $employee = $this->payslips->employeeForUser($user);

            if (! $employee) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'employee' => null,
                    'message' => 'No payroll record found for this email.',
                ]);
            }

            $payslips = $this->payslips->listForUser(
                $user,
                $request->integer('month') ?: null,
                $request->integer('year') ?: null
            );

            return response()->json([
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                ],
                'data' => $payslips->values(),
                'meta' => [
                    'count' => $payslips->count(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Sanctum payslip list failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payslips.',
            ], 500);
        }
    }

    /**
     * GET /api/payslips/{month}/{year}
     */
    public function show(Request $request, int $month, int $year)
    {
        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(['success' => false, 'message' => 'Invalid period.'], 422);
        }

        $user = $request->user();

        try {
            $detail = $this->payslips->detailForUser($user, $month, $year);

            if (! $detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip not available for this period.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $detail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Sanctum payslip detail failed', [
                'email' => $user->email,
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payslip details.',
            ], 500);
        }
    }

    /**
     * GET /api/payslips/{month}/{year}/pdf
     */
    public function pdf(Request $request, int $month, int $year)
    {
        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(['success' => false, 'message' => 'Invalid period.'], 422);
        }

        $user = $request->user();
        $download = $request->boolean('download', true);

        try {
            return $this->payslips->pdfForUser($user, $month, $year, $download);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not available for this period.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Sanctum payslip PDF failed', [
                'email' => $user->email,
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payslip PDF.',
            ], 500);
        }
    }
}

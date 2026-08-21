<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayslipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayslipApiController extends Controller
{
    public function __construct(private PayslipService $payslips)
    {
    }

    private function validateSyncToken(Request $request): bool
    {
        $expected = env('ATTENDANCE_SYNC_TOKEN', env('JWT_HMAC_SECRET'));

        return $request->input('sync_token') === $expected
            || $request->bearerToken() === $expected;
    }

    /**
     * List payslips for an employee (attendance → payroll).
     *
     * GET /api/payslips?employee_email=&month=&year=&sync_token=
     */
    public function index(Request $request)
    {
        if (! $this->validateSyncToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'employee_email' => 'required|email',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        try {
            $employee = $this->payslips->employeeForEmail($request->employee_email);

            if (! $employee) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'employee' => null,
                    'message' => 'No payroll record found for this email.',
                ]);
            }

            $payslips = $this->payslips->listForEmployeeEmail(
                $request->employee_email,
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
            Log::error('Payslip API list failed', [
                'email' => $request->employee_email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payslips.',
            ], 500);
        }
    }

    /**
     * Full payslip detail for employee/month/year.
     *
     * GET /api/payslips/show?employee_email=&month=&year=&sync_token=
     */
    public function show(Request $request)
    {
        if (! $this->validateSyncToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'employee_email' => 'required|email',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $detail = $this->payslips->detailForEmployeeEmail(
                $request->employee_email,
                (int) $request->month,
                (int) $request->year
            );

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
            Log::error('Payslip API show failed', [
                'email' => $request->employee_email,
                'month' => $request->month,
                'year' => $request->year,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payslip details.',
            ], 500);
        }
    }

    /**
     * Download payslip PDF for employee/month/year.
     *
     * GET /api/payslips/pdf?employee_email=&month=&year=&sync_token=
     */
    public function pdf(Request $request)
    {
        if (! $this->validateSyncToken($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'employee_email' => 'required|email',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'download' => 'nullable|boolean',
        ]);

        try {
            $employee = $this->payslips->employeeForEmail($request->employee_email);

            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found in payroll.',
                ], 404);
            }

            return $this->payslips->pdfResponse(
                $employee,
                (int) $request->month,
                (int) $request->year,
                $request->boolean('download', true)
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not available for this period. Payroll may not be finalized yet.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Payslip API PDF failed', [
                'email' => $request->employee_email,
                'month' => $request->month,
                'year' => $request->year,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payslip PDF.',
            ], 500);
        }
    }
}

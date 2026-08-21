<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeePayrollAttendance;
use App\Models\CompanySettings;
use App\Services\PDFGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Form16ApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $employee = EmployeeBasicDetail::where('email', $user->email)->first();

            if (!$employee) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No employee record found.',
                ]);
            }

            $records = EmployeePayrollAttendance::with('payoutMonth')
                ->where('emp_id', $employee->id)
                ->get();

            $financialYears = [];
            foreach ($records as $record) {
                if (!$record->payoutMonth) continue;
                $month = (int)$record->payoutMonth->payout_month;
                $year = (int)$record->payoutMonth->payout_year;

                if ($month >= 4) {
                    $fy = $year . '-' . ($year + 1);
                } else {
                    $fy = ($year - 1) . '-' . $year;
                }

                if (!in_array($fy, $financialYears)) {
                    $financialYears[] = $fy;
                }
            }

            rsort($financialYears);

            $data = array_map(function($fy) {
                return [
                    'year' => $fy,
                    'label' => 'Financial Year ' . $fy,
                ];
            }, $financialYears);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Sanctum Form 16 list failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Form 16 years list.',
            ], 500);
        }
    }

    public function pdf(Request $request, string $year)
    {
        $user = $request->user();

        try {
            $employee = EmployeeBasicDetail::with('personalDetail')->where('email', $user->email)->first();

            if (!$employee) {
                abort(404, 'Employee record not found.');
            }

            if (!preg_match('/^(\d{4})-(\d{4})$/', $year, $matches)) {
                abort(400, 'Invalid financial year format.');
            }
            $startYear = (int)$matches[1];
            $endYear = (int)$matches[2];

            $records = EmployeePayrollAttendance::with('payoutMonth')
                ->where('emp_id', $employee->id)
                ->whereHas('payoutMonth', function($q) use ($startYear, $endYear) {
                    $q->where(function($sub) use ($startYear) {
                        $sub->where('payout_year', $startYear)->where('payout_month', '>=', 4);
                    })->orWhere(function($sub) use ($endYear) {
                        $sub->where('payout_year', $endYear)->where('payout_month', '<=', 3);
                    });
                })
                ->get();

            if ($records->isEmpty()) {
                abort(404, 'No payroll data found for the selected financial year.');
            }

            // Sum up values
            $grossSalary = 0;
            $totalTds = 0;
            $totalPf = 0;

            foreach ($records as $record) {
                $grossSalary += (float)$record->gross_pay;

                $deductionsList = json_decode($record->deductions, true) ?: [];
                foreach ($deductionsList as $ded) {
                    $shortName = strtolower($ded['short_name'] ?? '');
                    if ($shortName === 'tds') {
                        $totalTds += (float)($ded['value'] ?? 0);
                    }
                    if ($shortName === 'pf' || $shortName === 'epf') {
                        $totalPf += (float)($ded['value'] ?? 0);
                    }
                }
            }

            // Find TDS regime to determine standard deduction
            $statutoryComponent = $employee->statutoryComponents()->where('statutory_component_id', 3)->first();
            $regime = $statutoryComponent ? $statutoryComponent->tds_regime : 'new';
            $standardDeduction = ($regime === 'new' && $startYear >= 2024) ? 75000 : 50000;

            $companySettings = CompanySettings::first();

            $pdfGenerator = app(PDFGenerator::class);
            $html = view('payroll.pdf.form16', compact(
                'employee',
                'year',
                'records',
                'grossSalary',
                'totalTds',
                'totalPf',
                'standardDeduction',
                'companySettings',
                'regime'
            ))->render();

            return $pdfGenerator->createPDF($html, "Form_16_{$employee->employee_id}_{$year}.pdf", false);
        } catch (\Throwable $e) {
            Log::error('Sanctum Form 16 PDF generation failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to generate Form 16 PDF.');
        }
    }
}

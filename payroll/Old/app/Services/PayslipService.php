<?php

namespace App\Services;

use App\Http\Controllers\PayrollController;
use App\Helper\NumberHelper;
use App\Models\CompanySettings;
use App\Models\Department;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeePayrollAttendance;
use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Models\PositionType;
use App\Services\PDFGenerator;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PayslipService
{
    /**
     * List finalized payslips for an employee (by payroll master email).
     */
    public function listForEmployeeEmail(string $email, ?int $month = null, ?int $year = null): Collection
    {
        $employee = EmployeeBasicDetail::where('email', $email)->first();

        if (! $employee) {
            return collect();
        }

        $rows = EmployeePayrollAttendance::query()
            ->select('employee_payroll_attendances.*')
            ->join(
                'employee_payroll_attendance_payout_month_statuses as payout_months',
                'payout_months.id',
                '=',
                'employee_payroll_attendances.payout_month_id'
            )
            ->where('employee_payroll_attendances.emp_id', $employee->id)
            ->where('payout_months.status', 'completed')
            ->when($month, fn ($q) => $q->where('payout_months.payout_month', $month))
            ->when($year, fn ($q) => $q->where('payout_months.payout_year', $year))
            ->orderByDesc('payout_months.payout_year')
            ->orderByDesc('payout_months.payout_month')
            ->get();

        return $rows->map(function (EmployeePayrollAttendance $row) use ($employee) {
            $pm = $row->payoutMonth;
            $period = Carbon::createFromDate($pm->payout_year, $pm->payout_month, 1);

            return [
                'id' => $row->id,
                'employee_id' => $employee->employee_id,
                'employee_db_id' => $employee->id,
                'month' => (int) $pm->payout_month,
                'year' => (int) $pm->payout_year,
                'period_label' => $period->format('F Y'),
                'gross_pay' => round((float) ($row->gross_pay ?? 0), 2),
                'total_deduction' => round((float) ($row->total_deduction ?? 0), 2),
                'net_pay' => round((float) ($row->total_payable ?? 0), 2),
                'working_days' => (float) $row->total_working_days,
                'days_worked' => (float) $row->employee_worked_days,
                'is_finalized' => (bool) $row->is_finalized,
            ];
        });
    }

    public function employeeForEmail(string $email): ?EmployeeBasicDetail
    {
        return EmployeeBasicDetail::where('email', $email)->first();
    }

    /**
     * Full payslip breakdown for one month (attendance portal preview).
     */
    public function detailForEmployeeEmail(string $email, int $month, int $year): ?array
    {
        $employee = $this->employeeForEmail($email);

        if (! $employee) {
            return null;
        }

        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year,
            'status' => 'completed',
        ])->first();

        if (! $payoutMonth) {
            return null;
        }

        $attendance = EmployeePayrollAttendance::where([
            'payout_month_id' => $payoutMonth->id,
            'emp_id' => $employee->id,
        ])->first();

        if (! $attendance) {
            return null;
        }

        $earnings = $this->formatLineItems(json_decode($attendance->earnings, true) ?? []);
        $deductions = $this->formatLineItems(json_decode($attendance->deductions, true) ?? []);

        $departments = Department::pluck('department', 'id');
        $designations = PositionType::pluck('position', 'id');
        $company = CompanySettings::where('id', 1)->first();
        $baseUrl = rtrim(config('app.url') ?: 'https://payrolldev.isarva.in', '/');

        $netPay = round((float) ($attendance->total_payable ?? 0));
        $period = Carbon::createFromDate($year, $month, 1);

        return [
            'month' => $month,
            'year' => $year,
            'period_label' => $period->format('F Y'),
            'employee' => [
                'name' => strtoupper($employee->name),
                'employee_id' => $employee->employee_id,
                'designation' => $designations[$employee->designation] ?? '—',
                'department' => $departments[$employee->department] ?? '—',
                'date_of_joining' => $employee->date_of_joining
                    ? Carbon::parse($employee->date_of_joining)->format('d-m-Y')
                    : '—',
            ],
            'attendance' => [
                'working_days' => (float) $attendance->total_working_days,
                'days_worked' => (float) $attendance->employee_worked_days,
            ],
            'earnings' => $earnings,
            'deductions' => $deductions,
            'total_earnings' => round((float) ($attendance->gross_pay ?? 0)),
            'total_deductions' => round((float) ($attendance->total_deduction ?? 0)),
            'net_pay' => $netPay,
            'net_pay_words' => NumberHelper::numberToWords($netPay),
            'company' => [
                'name' => $company->company_name ?? 'Company',
                'address' => $company->address ?? '',
                'logo_url' => $company && $company->logo_image
                    ? $baseUrl.'/'.ltrim($company->logo_image, '/')
                    : null,
            ],
        ];
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $items
     * @return array<int, array{name: string, amount: float}>
     */
    private function formatLineItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => ($item['applicable'] ?? false) && ! empty(trim($item['name'] ?? '')))
            ->map(fn ($item) => [
                'name' => $item['name'],
                'amount' => round((float) ($item['value'] ?? 0)),
            ])
            ->values()
            ->all();
    }

    /**
     * Stream payslip PDF (reuses existing PayrollController generator).
     */
    public function pdfResponse(EmployeeBasicDetail $employee, int $month, int $year, bool $download = true): Response
    {
        ob_start();
        app(PayrollController::class)->payslip_pdf(
            $employee,
            $month,
            $year,
            app(PDFGenerator::class)
        );
        $content = ob_get_clean();

        $filename = sprintf(
            'payslip-%s-%02d-%d.pdf',
            $employee->employee_id ?: $employee->id,
            $month,
            $year
        );

        $disposition = $download ? 'attachment' : 'inline';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }
}

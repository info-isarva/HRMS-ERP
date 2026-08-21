<?php

namespace App\Services;

use App\Models\EmployeeBasicDetail;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class EmployeePayslipService
{
    public function __construct(private PayslipService $payslips)
    {
    }

    public function employeeForUser(User $user): ?EmployeeBasicDetail
    {
        return $this->payslips->employeeForEmail($user->email);
    }

    public function listForUser(User $user, ?int $month = null, ?int $year = null): Collection
    {
        return $this->payslips->listForEmployeeEmail($user->email, $month, $year);
    }

    public function detailForUser(User $user, int $month, int $year): ?array
    {
        return $this->payslips->detailForEmployeeEmail($user->email, $month, $year);
    }

    public function pdfForUser(User $user, int $month, int $year, bool $download = true): Response
    {
        $employee = $this->employeeForUser($user);

        if (! $employee) {
            abort(404, 'Employee not found in payroll.');
        }

        return $this->payslips->pdfResponse($employee, $month, $year, $download);
    }
}

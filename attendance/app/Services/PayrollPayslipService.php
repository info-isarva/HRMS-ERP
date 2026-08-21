<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayrollPayslipService
{
    protected string $baseUrl;

    protected string $syncToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('PAYROLL_SYNC_URL', env('PAYROLL_API_BASE_URL', 'https://payrolldev.isarva.in')), '/');
        if (str_ends_with($this->baseUrl, '/api')) {
            $this->baseUrl = substr($this->baseUrl, 0, -4);
        }
        $this->syncToken = env('PAYROLL_SYNC_TOKEN', env('ATTENDANCE_SYNC_TOKEN', ''));
    }

    /**
     * @return array{success: bool, data: array, employee: ?array, message?: string}
     */
    public function listForEmail(string $email, ?int $month = null, ?int $year = null): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl.'/api/payslips', array_filter([
                'employee_email' => $email,
                'month' => $month,
                'year' => $year,
                'sync_token' => $this->syncToken,
            ]));

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Payroll payslip list API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'email' => $email,
            ]);

            return [
                'success' => false,
                'data' => [],
                'employee' => null,
                'message' => 'Unable to load payslips from payroll.',
            ];
        } catch (\Throwable $e) {
            Log::error('Payroll payslip list exception', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            return [
                'success' => false,
                'data' => [],
                'employee' => null,
                'message' => 'Payroll service is temporarily unavailable.',
            ];
        }
    }

    /**
     * Fetch payslip PDF binary from payroll.
     */
    public function fetchPdf(string $email, int $month, int $year, bool $download = true): ?\Illuminate\Http\Client\Response
    {
        try {
            $response = Http::timeout(60)->get($this->baseUrl.'/api/payslips/pdf', [
                'employee_email' => $email,
                'month' => $month,
                'year' => $year,
                'download' => $download ? 1 : 0,
                'sync_token' => $this->syncToken,
            ]);

            if ($response->successful() && str_contains($response->header('Content-Type') ?? '', 'pdf')) {
                return $response;
            }

            Log::error('Payroll payslip PDF API failed', [
                'status' => $response->status(),
                'email' => $email,
                'month' => $month,
                'year' => $year,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Payroll payslip PDF exception', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            return null;
        }
    }

    /**
     * @return array{success: bool, data?: array, message?: string}
     */
    public function detailForEmail(string $email, int $month, int $year): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl.'/api/payslips/show', [
                'employee_email' => $email,
                'month' => $month,
                'year' => $year,
                'sync_token' => $this->syncToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'success' => false,
                'message' => 'Payslip details not available.',
            ];
        } catch (\Throwable $e) {
            Log::error('Payroll payslip detail exception', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            return [
                'success' => false,
                'message' => 'Payroll service is temporarily unavailable.',
            ];
        }
    }

    /**
     *
     * @return array<int, array{month: int, year: int, label: string, value: string}>
     */
    public function periodOptions(int $count = 24): array
    {
        $options = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < $count; $i++) {
            $options[] = [
                'month' => (int) $cursor->month,
                'year' => (int) $cursor->year,
                'label' => $cursor->format('F Y'),
                'value' => $cursor->format('Y-m'),
            ];
            $cursor->subMonth();
        }

        return $options;
    }
}

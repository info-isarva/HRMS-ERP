<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->with(['employee', 'shift'])->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Date',
            'Check In',
            'Check Out',
            'Total Hours',
            'Status',
            'Shift',
            'Scheduled Start',
            'Scheduled End',
            'Late (min)',
            'Early Departure (min)',
            'OT (hrs)',
            'UT (hrs)',
            'Source',
            'Processed At'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->employee_payroll_id,
            $attendance->employee->name ?? 'N/A',
            $attendance->date instanceof Carbon ? $attendance->date->format('Y-m-d') : $attendance->date,
            $attendance->check_in_time,
            $attendance->check_out_time,
            $attendance->total_hours,
            ucfirst(str_replace('_', ' ', $attendance->status)),
            $attendance->shift->name ?? 'N/A',
            $attendance->scheduled_start_time,
            $attendance->scheduled_end_time,
            $attendance->late_arrival_minutes ?? 0,
            $attendance->early_departure_minutes ?? 0,
            $attendance->overtime_hours ?? 0,
            $attendance->undertime_hours ?? 0,
            ucfirst(str_replace('_', ' ', $attendance->source)),
            $attendance->processed_at?->format('Y-m-d H:i:s') ?? $attendance->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => ['horizontal' => 'center']
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 20,
            'C' => 12,
            'D' => 10,
            'E' => 10,
            'F' => 12,
            'G' => 15,
            'H' => 18,
            'I' => 15,
            'J' => 15,
            'K' => 10,
            'L' => 18,
            'M' => 10,
            'N' => 10,
            'O' => 18,
            'P' => 18,
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AttendanceTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        $employees = Employee::with('department')->limit(10)->get();
        $data = [];

        foreach ($employees as $employee) {
            $data[] = [
                $employee->payroll_id,
                now()->format('Y-m-d'),
                '09:00',
                '17:00',
                'Regular Shift',
                $employee->department->name ?? 'General',
                'Sample attendance record'
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'payroll_id',
            'date',
            'check_in_time',
            'check_out_time',
            'shift_name',
            'department',
            'notes'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
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
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 20,
            'F' => 20,
            'G' => 30,
        ];
    }
}

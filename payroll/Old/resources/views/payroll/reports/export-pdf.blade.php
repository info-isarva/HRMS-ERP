<!DOCTYPE html>
<html>
<head>
    <title>Payroll Report - {{ now()->format('F Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; font-size: 9px; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .bg-success { background-color: #d4edda; }
        .bg-danger { background-color: #f8d7da; }
        .page-break { page-break-after: always; }
        .footer { margin-top: 20px; font-size: 9px; text-align: center; }
        .month-header { background-color: #667eea; color: white; padding: 10px; margin-bottom: 10px; font-size: 14px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Report</h1>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
        <p>Period: 
            @foreach($selectedMonths as $month)
                {{ $month['label'] }}@if(!$loop->last), @endif
            @endforeach
        </p>
    </div>

    @php
        $isConsolidated = count($groupedAttendances) === 1 && $groupedAttendances->keys()->first() === 'All Months';
    @endphp

    @foreach($groupedAttendances as $monthLabel => $attendances)
        @if(!$isConsolidated)
            <div class="month-header">{{ $monthLabel }}</div>
        @endif
        
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Employee</th>
                    <th class="text-center" style="width: 40px;">Work Days</th>
                    <th class="text-center" style="width: 40px;">Total Days</th>
                    
                    @foreach($earningComponents as $component)
                        <th class="text-center" style="width: 50px;">{{ $component->short_name }}</th>
                    @endforeach
                    
                    <th class="text-center" style="width: 60px;">Gross Pay</th>
                    <th class="text-center" style="width: 60px;">EPF Wages</th>
                    
                    @foreach($deductionComponents as $component)
                        <th class="text-center" style="width: 50px;">{{ $component->short_name }}</th>
                    @endforeach
                    
                    <th class="text-center" style="width: 50px;">ADV</th>
                    <th class="text-center" style="width: 60px;">Total Deductions</th>
                    <th class="text-center" style="width: 60px;">Net Pay</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td style="font-size: 8px;">{{ $attendance->employee->name }} ({{ $attendance->employee->employee_id }})</td>
                        <td class="text-center">{{ $attendance->employee_worked_days }}</td>
                        <td class="text-center">{{ $attendance->total_working_days }}</td>
                        
                        @foreach($earningComponents as $component)
                            <td class="text-end">
                                @php
                                    $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                                    $componentData = $earnings[$component->id] ?? null;
                                    $value = 0;
                                    if ($componentData && isset($componentData['value'])) {
                                        $value = $componentData['value'];
                                    }
                                @endphp
                                {{ number_format($value, 2) }}
                            </td>
                        @endforeach
                        
                        <td class="text-end">{{ number_format($attendance->gross_pay, 2) }}</td>
                        <td class="text-end">{{ number_format($attendance->epf_wage, 2) }}</td>
                        
                        @foreach($deductionComponents as $component)
                            <td class="text-end">
                                @php
                                    $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                                    $componentData = $deductions[$component->id] ?? null;
                                    $value = 0;
                                    if ($componentData && isset($componentData['value'])) {
                                        $value = $componentData['value'];
                                    }
                                @endphp
                                {{ number_format($value, 2) }}
                            </td>
                        @endforeach
                        
                        <td class="text-end">
                            @php
                                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                                $advanceValue = $deductions['advance']['value'] ?? 0;
                            @endphp
                            {{ number_format($advanceValue, 2) }}
                        </td>
                        
                        <td class="text-end">{{ number_format($attendance->total_deduction, 2) }}</td>
                        <td class="text-end">{{ number_format($attendance->total_payable, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td>@if($isConsolidated)Grand @endif Totals</td>
                    <td class="text-center">{{ $attendances->sum('employee_worked_days') }}</td>
                    <td class="text-center">{{ $attendances->sum('total_working_days') }}</td>
                    
                    @foreach($earningComponents as $component)
                        <td class="text-end">
                            @php
                                $total = 0;
                                foreach($attendances as $attendance) {
                                    $earnings = is_array($attendance->earnings) ? $attendance->earnings : (json_decode($attendance->earnings, true) ?? []);
                                    $componentData = $earnings[$component->id] ?? null;
                                    if ($componentData && isset($componentData['value'])) {
                                        $total += $componentData['value'];
                                    }
                                }
                            @endphp
                            {{ number_format($total, 2) }}
                        </td>
                    @endforeach
                    
                    <td class="text-end">{{ number_format($attendances->sum('gross_pay'), 2) }}</td>
                    <td class="text-end">{{ number_format($attendances->sum('epf_wage'), 2) }}</td>
                    
                    @foreach($deductionComponents as $component)
                        <td class="text-end">
                            @php
                                $total = 0;
                                foreach($attendances as $attendance) {
                                    $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                                    $componentData = $deductions[$component->id] ?? null;
                                    if ($componentData && isset($componentData['value'])) {
                                        $total += $componentData['value'];
                                    }
                                }
                            @endphp
                            {{ number_format($total, 2) }}
                        </td>
                    @endforeach
                    
                    <td class="text-end">
                        @php
                            $totalAdvance = 0;
                            foreach($attendances as $attendance) {
                                $deductions = is_array($attendance->deductions) ? $attendance->deductions : (json_decode($attendance->deductions, true) ?? []);
                                $totalAdvance += $deductions['advance']['value'] ?? 0;
                            }
                        @endphp
                        {{ number_format($totalAdvance, 2) }}
                    </td>
                    
                    <td class="text-end">{{ number_format($attendances->sum('total_deduction'), 2) }}</td>
                    <td class="text-end">{{ number_format($attendances->sum('total_payable'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
        
        @if(!$loop->last && !$isConsolidated)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        <p>Generated by {{ Auth::user()->name }} on {{ now()->format('d M Y H:i') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</body>
</html>
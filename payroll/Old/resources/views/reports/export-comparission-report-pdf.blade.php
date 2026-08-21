<!DOCTYPE html>
<html>
<head>
    <title>Payroll Comparison Report - {{ $comparisonData['first_month_name'] }} vs {{ $comparisonData['second_month_name'] }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 15px;
            line-height: 1.3;
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 20px; 
            color: #333;
            font-weight: bold;
        }
        .header p { 
            margin: 3px 0; 
            color: #666;
            font-size: 10px;
        }
        .summary-section {
            margin-bottom: 25px;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            float: left;
            width: 16.66%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            box-sizing: border-box;
            min-height: 60px;
        }
        .summary-card h3 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #333;
            font-weight: bold;
        }
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-top: 3px;
        }
        .summary-card .change {
            font-size: 10px;
            margin-top: 3px;
        }
        .change.positive { color: #28a745; }
        .change.negative { color: #dc3545; }
        .change.neutral { color: #6c757d; }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .month-summary {
            width: 100%;
            margin-bottom: 20px;
        }
        .month-card {
            float: left;
            width: 50%;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            box-sizing: border-box;
            min-height: 120px;
        }
        .month-card h2 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
            text-align: center;
            font-weight: bold;
        }
        .month-stats {
            width: 100%;
        }
        .month-stat {
            float: left;
            width: 33.33%;
            text-align: center;
            padding: 5px;
            box-sizing: border-box;
        }
        .month-stat .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .month-stat .value {
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 8px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 4px; 
            text-align: center; 
            vertical-align: middle;
        }
        .table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
            font-size: 8px;
        }
        .table td.employee-col {
            text-align: left;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 6px 4px;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .bg-success { background-color: #d4edda; }
        .bg-danger { background-color: #f8d7da; }
        .bg-warning { background-color: #fff3cd; }
        .bg-info { background-color: #d1ecf1; }
        .page-break { page-break-after: always; }
        .footer { 
            margin-top: 20px; 
            font-size: 8px; 
            text-align: center; 
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .status-badge {
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .status-not-joined {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-left {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .change-badge {
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .change-positive {
            background-color: #d4edda;
            color: #155724;
        }
        .change-negative {
            background-color: #f8d7da;
            color: #721c24;
        }
        .totals-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payroll Comparison Report</h1>
        <p><strong>{{ $comparisonData['first_month_name'] }} vs {{ $comparisonData['second_month_name'] }}</strong></p>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
        <p>Total Employees: {{ $comparisonData['summary']['total_employees'] }}</p>
    </div>

    <!-- Monthly Summary Section -->
    <div class="summary-section">
        <h2 style="margin-bottom: 15px; color: #333;">Monthly Summary</h2>
        <div class="month-summary clearfix">
            <div class="month-card">
                <h2>{{ $comparisonData['first_month_name'] }}</h2>
                <div class="month-stats clearfix">
                    <div class="month-stat">
                        <div class="label">Employees</div>
                        <div class="value">{{ $comparisonData['totals']['first_month']['employee_count'] }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Gross Pay</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['gross_pay'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Net Pay</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['net_pay'], 0) }}</div>
                    </div>
                </div>
                <div class="month-stats clearfix" style="margin-top: 10px;">
                    <div class="month-stat">
                        <div class="label">EPF</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['epf'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">ESIC</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['esic'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Deductions</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['total_deductions'], 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="month-card">
                <h2>{{ $comparisonData['second_month_name'] }}</h2>
                <div class="month-stats clearfix">
                    <div class="month-stat">
                        <div class="label">Employees</div>
                        <div class="value">{{ $comparisonData['totals']['second_month']['employee_count'] }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Gross Pay</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['gross_pay'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Net Pay</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['net_pay'], 0) }}</div>
                    </div>
                </div>
                <div class="month-stats clearfix" style="margin-top: 10px;">
                    <div class="month-stat">
                        <div class="label">EPF</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['epf'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">ESIC</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['esic'], 0) }}</div>
                    </div>
                    <div class="month-stat">
                        <div class="label">Deductions</div>
                        <div class="value">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['total_deductions'], 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Changes Summary -->
    <div class="summary-section">
        <h2 style="margin-bottom: 15px; color: #333;">Changes Summary</h2>
        <div class="summary-cards clearfix">
            <div class="summary-card">
                <h3>Employee Count</h3>
                <div class="value">{{ $comparisonData['summary']['employee_count_change'] >= 0 ? '+' : '' }}{{ $comparisonData['summary']['employee_count_change'] }}</div>
            </div>
            <div class="summary-card">
                <h3>Gross Pay</h3>
                <div class="value">{{ $comparisonData['summary']['gross_pay_change'] >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['gross_pay_change']), 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>EPF</h3>
                <div class="value">{{ $comparisonData['summary']['epf_change'] >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['epf_change']), 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>ESIC</h3>
                <div class="value">{{ $comparisonData['summary']['esic_change'] >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['esic_change']), 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>Deductions</h3>
                <div class="value">{{ $comparisonData['summary']['deduction_change'] >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['deduction_change']), 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>Net Pay</h3>
                <div class="value">{{ $comparisonData['summary']['net_pay_change'] >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['net_pay_change']), 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison Table -->
    <div class="page-break"></div>
    <h2 style="margin-bottom: 10px; color: #333; font-size: 14px;">Detailed Employee Comparison</h2>
    <table class="table">
        <thead>
            <tr style="background-color: #333; color: white;">
                <th rowspan="2" style="background-color: #333; color: white;">Employee</th>
                <th colspan="5" style="background-color: #007bff; color: white;">{{ $comparisonData['first_month_name'] }}</th>
                <th colspan="5" style="background-color: #17a2b8; color: white;">{{ $comparisonData['second_month_name'] }}</th>
                <th colspan="5" style="background-color: #28a745; color: white;">Changes</th>
            </tr>
            <tr>
                <!-- First Month -->
                <th style="background-color: #007bff; color: white;">Gross</th>
                <th style="background-color: #007bff; color: white;">EPF</th>
                <th style="background-color: #007bff; color: white;">ESIC</th>
                <th style="background-color: #007bff; color: white;">Deductions</th>
                <th style="background-color: #007bff; color: white;">Net</th>
                <!-- Second Month -->
                <th style="background-color: #17a2b8; color: white;">Gross</th>
                <th style="background-color: #17a2b8; color: white;">EPF</th>
                <th style="background-color: #17a2b8; color: white;">ESIC</th>
                <th style="background-color: #17a2b8; color: white;">Deductions</th>
                <th style="background-color: #17a2b8; color: white;">Net</th>
                <!-- Changes -->
                <th style="background-color: #28a745; color: white;">Gross</th>
                <th style="background-color: #28a745; color: white;">EPF</th>
                <th style="background-color: #28a745; color: white;">ESIC</th>
                <th style="background-color: #28a745; color: white;">Deductions</th>
                <th style="background-color: #28a745; color: white;">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comparisonData['employees'] as $employee)
            <tr>
                <td class="employee-col">
                    {{ $employee['name'] }}<br>
                    <small style="color: #666;">ID: {{ $employee['employee_id'] }}</small>
                </td>
                
                <!-- First Month Data -->
                @if($employee['first_month']['status'] == 'not_joined')
                    <td colspan="5" class="text-center">
                        <span class="status-badge status-not-joined">Not Joined</span>
                    </td>
                @else
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['first_month']['gross_pay'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['first_month']['epf'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['first_month']['esic'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['first_month']['total_deductions'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['first_month']['net_pay'], 0) }}</td>
                @endif
                
                <!-- Second Month Data -->
                @if($employee['second_month']['status'] == 'left')
                    <td colspan="5" class="text-center">
                        <span class="status-badge status-left">Employee Left</span>
                    </td>
                @else
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['second_month']['gross_pay'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['second_month']['epf'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['second_month']['esic'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['second_month']['total_deductions'], 0) }}</td>
                    <td>{{ get_currency_symbol() }}{{ number_format($employee['second_month']['net_pay'], 0) }}</td>
                @endif
                
                <!-- Changes -->
                @if($employee['first_month']['status'] == 'not_joined')
                    <td colspan="5" class="text-center">
                        <span class="status-badge status-active">New Joinee</span>
                    </td>
                @elseif($employee['second_month']['status'] == 'left')
                    <td colspan="5" class="text-center">
                        <span class="status-badge status-left">Employee Left</span>
                    </td>
                @else
                    @php
                        $grossChange = $employee['second_month']['gross_pay'] - $employee['first_month']['gross_pay'];
                        $epfChange = $employee['second_month']['epf'] - $employee['first_month']['epf'];
                        $esicChange = $employee['second_month']['esic'] - $employee['first_month']['esic'];
                        $deductionChange = $employee['second_month']['total_deductions'] - $employee['first_month']['total_deductions'];
                        $netChange = $employee['second_month']['net_pay'] - $employee['first_month']['net_pay'];
                    @endphp
                    <td>
                        <span class="change-badge {{ $grossChange >= 0 ? 'change-positive' : 'change-negative' }}">
                            {{ $grossChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($grossChange, 0) }}
                        </span>
                    </td>
                    <td>
                        <span class="change-badge {{ $epfChange >= 0 ? 'change-positive' : 'change-negative' }}">
                            {{ $epfChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($epfChange, 0) }}
                        </span>
                    </td>
                    <td>
                        <span class="change-badge {{ $esicChange >= 0 ? 'change-positive' : 'change-negative' }}">
                            {{ $esicChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($esicChange, 0) }}
                        </span>
                    </td>
                    <td>
                        <span class="change-badge {{ $deductionChange >= 0 ? 'change-positive' : 'change-negative' }}">
                            {{ $deductionChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($deductionChange, 0) }}
                        </span>
                    </td>
                    <td>
                        <span class="change-badge {{ $netChange >= 0 ? 'change-positive' : 'change-negative' }}">
                            {{ $netChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($netChange, 0) }}
                        </span>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
        
        <!-- Totals Row -->
        <tfoot>
            <tr class="totals-row">
                <td class="text-center"><strong>TOTALS</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['gross_pay'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['epf'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['esic'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['total_deductions'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['net_pay'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['gross_pay'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['epf'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['esic'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['total_deductions'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['net_pay'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['gross_pay_change'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['epf_change'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['esic_change'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['deduction_change'], 0) }}</strong></td>
                <td><strong>{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['net_pay_change'], 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated by {{ Auth::user()->name ?? 'System' }} on {{ now()->format('d M Y H:i') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }} - Confidential Document</p>
    </div>
</body>
</html>

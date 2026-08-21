<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytical Payroll Comparison Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .logo-section {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
        }
        
        .logo {
            width: 80px;
            height: auto;
        }
        
        .title-section {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding-left: 20px;
        }
        
        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }
        
        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .info-section {
            display: table-cell;
            width: 200px;
            vertical-align: middle;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .summary-card {
            display: table-cell;
            width: 19%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            margin-right: 1.25%;
        }
        
        .summary-card:last-child {
            margin-right: 0;
        }
        
        .card-value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .card-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin: 25px 0 15px 0;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .comparison-table th {
            background: #007bff;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .comparison-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .comparison-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .change-positive {
            color: #28a745;
            font-weight: bold;
        }
        
        .change-negative {
            color: #dc3545;
            font-weight: bold;
        }
        
        .change-neutral {
            color: #6c757d;
        }
        
        .monthly-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        .monthly-data-table th {
            background: #28a745;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
        }
        
        .monthly-data-table td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        
        .monthly-data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .currency {
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        .text-end {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer-note {
            font-size: 10px;
            color: #666;
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            @if(isset($companySettings->logo_image) && !empty($companySettings->logo_image))
                <img src="{{ public_path($companySettings->logo_image) }}" alt="Company Logo" class="logo">
            @else
                <img src="{{ public_path('assets/img/logo.png') }}" alt="Company Logo" class="logo">
            @endif
        </div>
        <div class="title-section">
            <h1 class="report-title">Analytical Payroll Comparison Report</h1>
            <p class="report-subtitle">
                Financial Year: {{ $selectedFY ? $selectedFY->name : 'All Years' }}
            </p>
        </div>
        <div class="info-section">
            <strong>Generated On:</strong><br>
            {{ date('d M Y, h:i A') }}<br><br>
            <strong>Report Period:</strong><br>
            {{ $selectedFY ? $selectedFY->start_date->format('M Y') . ' - ' . $selectedFY->end_date->format('M Y') : 'All Available Data' }}
        </div>
    </div>

    @if(count($monthlyData) > 0)
        <!-- Summary Cards -->
        @php
            $latestMonth = array_values($monthlyData)[0];
            $totalMonths = count($monthlyData);
            $totalEmployees = array_sum(array_column($monthlyData, 'employee_count')) / $totalMonths;
            $totalGrossPay = array_sum(array_column($monthlyData, 'gross_pay'));
            $totalDeductions = array_sum(array_column($monthlyData, 'total_deductions'));
            $totalNetPay = array_sum(array_column($monthlyData, 'net_pay'));
        @endphp
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-value">{{ number_format($totalMonths) }}</div>
                <div class="card-label">Total Months</div>
            </div>
            <div class="summary-card">
                <div class="card-value">{{ number_format($totalEmployees, 0) }}</div>
                <div class="card-label">Avg Employees</div>
            </div>
            <div class="summary-card">
                <div class="card-value currency">{{ get_currency_symbol() }} {{ number_format($totalGrossPay, 0) }}</div>
                <div class="card-label">Total Gross Pay</div>
            </div>
            <div class="summary-card">
                <div class="card-value currency">{{ get_currency_symbol() }} {{ number_format($totalDeductions, 0) }}</div>
                <div class="card-label">Total Deductions</div>
            </div>
            <div class="summary-card">
                <div class="card-value currency">{{ get_currency_symbol() }} {{ number_format($totalNetPay, 0) }}</div>
                <div class="card-label">Total Net Pay</div>
            </div>
        </div>

        <!-- Month-wise Detailed Data -->
        <h2 class="section-title">Month-wise Payroll Data</h2>
        <table class="monthly-data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Employees</th>
                    <th>Gross Pay ({{ get_currency_symbol() }})</th>
                    <th>EPF ({{ get_currency_symbol() }})</th>
                    <th>ESI ({{ get_currency_symbol() }})</th>
                    <th>PT ({{ get_currency_symbol() }})</th>
                    <th>TDS ({{ get_currency_symbol() }})</th>
                    <th>Advance ({{ get_currency_symbol() }})</th>
                    <th>Total Deductions ({{ get_currency_symbol() }})</th>
                    <th>Net Pay ({{ get_currency_symbol() }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyData as $month => $data)
                <tr>
                    <td><strong>{{ $data['label'] }}</strong></td>
                    <td>{{ number_format($data['employee_count']) }}</td>
                    <td class="currency">{{ number_format($data['gross_pay'], 2) }}</td>
                    <td class="currency">{{ number_format($data['epf'], 2) }}</td>
                    <td class="currency">{{ number_format($data['esi'], 2) }}</td>
                    <td class="currency">{{ number_format($data['pt'], 2) }}</td>
                    <td class="currency">{{ number_format($data['tds'], 2) }}</td>
                    <td class="currency">{{ number_format($data['advance'], 2) }}</td>
                    <td class="currency">{{ number_format($data['total_deductions'], 2) }}</td>
                    <td class="currency"><strong>{{ number_format($data['net_pay'], 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($comparisons) > 0)
        <!-- Month-over-Month Comparisons -->
        <div class="page-break"></div>
        <h2 class="section-title">Month-over-Month Comparison Analysis</h2>
        
        <table class="comparison-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Comparison Period</th>
                    <th style="width: 12%;">Employees</th>
                    <th style="width: 15%;">Gross Pay ({{ get_currency_symbol() }})</th>
                    <th style="width: 12%;">EPF ({{ get_currency_symbol() }})</th>
                    <th style="width: 12%;">ESI ({{ get_currency_symbol() }})</th>
                    <th style="width: 12%;">PT ({{ get_currency_symbol() }})</th>
                    <th style="width: 12%;">TDS ({{ get_currency_symbol() }})</th>
                    <th style="width: 10%;">Net Pay ({{ get_currency_symbol() }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comparisons as $comparison)
                <tr>
                    <td>
                        <strong>{{ $comparison['current_month'] }}</strong><br>
                        <small>vs {{ $comparison['previous_month'] }}</small>
                    </td>
                    <td class="text-center">
                        <div class="{{ $comparison['employee_count']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['employee_count']['is_increase'] ? '+' : '' }}{{ number_format($comparison['employee_count']['difference']) }}
                        </div>
                        <small>({{ $comparison['employee_count']['percentage'] > 0 ? '+' : '' }}{{ $comparison['employee_count']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['gross_pay']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['gross_pay']['is_increase'] ? '+' : '' }}{{ number_format($comparison['gross_pay']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['gross_pay']['percentage'] > 0 ? '+' : '' }}{{ $comparison['gross_pay']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['epf']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['epf']['is_increase'] ? '+' : '' }}{{ number_format($comparison['epf']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['epf']['percentage'] > 0 ? '+' : '' }}{{ $comparison['epf']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['esi']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['esi']['is_increase'] ? '+' : '' }}{{ number_format($comparison['esi']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['esi']['percentage'] > 0 ? '+' : '' }}{{ $comparison['esi']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['pt']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['pt']['is_increase'] ? '+' : '' }}{{ number_format($comparison['pt']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['pt']['percentage'] > 0 ? '+' : '' }}{{ $comparison['pt']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['tds']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['tds']['is_increase'] ? '+' : '' }}{{ number_format($comparison['tds']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['tds']['percentage'] > 0 ? '+' : '' }}{{ $comparison['tds']['percentage'] }}%)</small>
                    </td>
                    <td class="text-end">
                        <div class="{{ $comparison['net_pay']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                            {{ $comparison['net_pay']['is_increase'] ? '+' : '' }}{{ number_format($comparison['net_pay']['difference'], 2) }}
                        </div>
                        <small>({{ $comparison['net_pay']['percentage'] > 0 ? '+' : '' }}{{ $comparison['net_pay']['percentage'] }}%)</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @else
        <!-- No Data Available -->
        <div class="text-center" style="margin: 50px 0;">
            <h3 style="color: #666;">No Payroll Data Available</h3>
            <p style="color: #999;">
                There is no completed payroll data available for the selected financial year 
                <strong>{{ $selectedFY ? $selectedFY->name : 'Current' }}</strong>.
            </p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer-note">
        <p>This report was generated automatically by the HRMS Payroll System on {{ date('d M Y \a\t h:i A') }}.</p>
        <p>All monetary values are in {{ get_currency_name() }} ({{ get_currency_symbol() }}). Positive changes are shown in green, negative changes in red.</p>
    </div>
</body>
</html>
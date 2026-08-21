<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form 16 - {{ $employee->name }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .main-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            font-size: 9px;
            margin: 2px 0;
        }
        
        table.gov-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        table.gov-table td, table.gov-table th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.gov-table th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }
        
        .section-header {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 10px;
            text-decoration: underline;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- PART A PAGE -->
    <div class="text-center">
        <div class="main-title"><sup>1</sup>FORM NO. 16</div>
        <div class="subtitle">[See rule 31(1)(a)]</div>
        <div class="main-title">PART A</div>
        <div style="font-size: 8.5px; margin-top: 5px; font-weight: bold; padding: 0 10px;">
            Certificate under section 203 of the Income-tax Act, 1961 for tax deducted at source on salary paid to an employee under section 192 or pension or interest income of specified senior citizen under section 194P.
        </div>
    </div>

    <table class="gov-table">
        <tr>
            <td style="width: 50%;" class="bold text-center">Certificate No.</td>
            <td style="width: 50%;" class="bold text-center">Last updated on</td>
        </tr>
        <tr>
            <td class="text-center">F16-{{ $employee->employee_id }}-{{ str_replace('-', '', $year) }}</td>
            <td class="text-center">{{ date('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="gov-table">
        <tr>
            <td style="width: 50%; height: 50px;">
                <div class="bold">Name and address of the Employer/Specified Bank</div>
                <div style="margin-top: 5px;">
                    <strong>{{ $companySettings->company_name ?? 'Company Name' }}</strong><br>
                    {{ $companySettings->address ?? 'Company Address' }}
                </div>
            </td>
            <td style="width: 50%; height: 50px;">
                <div class="bold">Name and address of the Employee/ Specified senior citizen</div>
                <div style="margin-top: 5px;">
                    <strong>{{ $employee->name }}</strong><br>
                    {{ $employee->personalDetail->address_line_1 ?? '' }} {{ $employee->personalDetail->address_line_2 ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="gov-table">
        <tr>
            <td style="width: 25%;" class="bold text-center">PAN of the Deductor</td>
            <td style="width: 25%;" class="bold text-center">TAN of the Deductor</td>
            <td style="width: 25%;" class="bold text-center">PAN of the Employee/specified senior citizen</td>
            <td style="width: 25%;" class="bold text-center">Employee Reference No./ Pension Payment order No. provided by the Employer (If available)</td>
        </tr>
        <tr>
            <td class="text-center bold">{{ $companySettings->company_pan ?? 'N/A' }}</td>
            <td class="text-center bold">{{ $companySettings->company_tan ?? 'N/A' }}</td>
            <td class="text-center bold">{{ $employee->personalDetail->pan_number ?? 'N/A' }}</td>
            <td class="text-center">{{ $employee->employee_id }}</td>
        </tr>
    </table>

    <table class="gov-table">
        <tr>
            <td style="width: 40%;">
                <div class="bold">CIT (TDS)</div>
                <div style="font-size: 8.5px; color: #555; margin-top: 3px;">
                    Address: Commissioner of Income Tax (TDS)<br>
                    City: {{ $companySettings->city ?? '' }}<br>
                    Pin Code: {{ $companySettings->postal_code ?? '' }}
                </div>
            </td>
            <td style="width: 20%;" class="text-center">
                <div class="bold">Assessment Year</div>
                <div style="margin-top: 5px; font-weight: bold;">
                    @php
                        $years = explode('-', $year);
                        echo ((int)$years[0] + 1) . '-' . substr(((int)$years[1] + 1), -2);
                    @endphp
                </div>
            </td>
            <td style="width: 40%;" class="text-center">
                <div class="bold">Period with the Employer</div>
                <table style="width: 100%; border: none; margin-top: 5px;">
                    <tr>
                        <td style="border: none; width: 50%;" class="bold">From</td>
                        <td style="border: none; width: 50%;" class="bold">To</td>
                    </tr>
                    <tr>
                        <td style="border: none;">01/04/{{ $years[0] }}</td>
                        <td style="border: none;">31/03/{{ $years[1] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="bold text-center" style="margin-top: 15px; margin-bottom: 5px;">Summary of amount paid/credited and tax deducted at source thereon in respect of the employee</div>
    
    <table class="gov-table">
        <thead>
            <tr>
                <th>Quarter(s)</th>
                <th>Receipt Numbers of original quarterly statement of TDS under sub section (3) of section 200</th>
                <th>Amount paid/credited</th>
                <th>Amount of tax deducted (Rs.)</th>
                <th>Amount of tax deposited/ remitted (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Split gross, tds into quarters
                $qData = [
                    1 => ['gross' => 0, 'tds' => 0, 'months' => [4,5,6]],
                    2 => ['gross' => 0, 'tds' => 0, 'months' => [7,8,9]],
                    3 => ['gross' => 0, 'tds' => 0, 'months' => [10,11,12]],
                    4 => ['gross' => 0, 'tds' => 0, 'months' => [1,2,3]]
                ];
                
                foreach ($records as $rec) {
                    $m = (int)$rec->payoutMonth->payout_month;
                    $g = (float)$rec->gross_pay;
                    $t = 0;
                    $dedList = json_decode($rec->deductions, true) ?: [];
                    foreach ($dedList as $d) {
                        if (strtolower($d['short_name'] ?? '') === 'tds') {
                            $t = (float)($d['value'] ?? 0);
                        }
                    }
                    
                    foreach ($qData as $qNum => &$qInfo) {
                        if (in_array($m, $qInfo['months'])) {
                            $qInfo['gross'] += $g;
                            $qInfo['tds'] += $t;
                        }
                    }
                }
            @endphp
            
            @foreach($qData as $qNum => $qInfo)
                <tr>
                    <td class="text-center">Q{{ $qNum }}</td>
                    <td class="text-center">-</td>
                    <td class="text-right">{{ number_format($qInfo['gross'], 2) }}</td>
                    <td class="text-right">{{ number_format($qInfo['tds'], 2) }}</td>
                    <td class="text-right">{{ number_format($qInfo['tds'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="bold" style="background-color: #f2f2f2;">
                <td colspan="2" class="text-center">Total (Rs.)</td>
                <td class="text-right">{{ number_format($grossSalary, 2) }}</td>
                <td class="text-right">{{ number_format($totalTds, 2) }}</td>
                <td class="text-right">{{ number_format($totalTds, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="bold text-center" style="margin-top: 15px; margin-bottom: 5px;">II. DETAILS OF TAX DEDUCTED AND DEPOSITED IN THE CENTRAL GOVERNMENT ACCOUNT THROUGH CHALLAN</div>
    <div class="text-center" style="font-size: 8px; margin-bottom: 10px;">(The deductor to provide payment wise details of tax deducted and deposited with respect to the deductee)</div>

    <table class="gov-table">
        <thead>
            <tr>
                <th rowspan="2">Sl. No.</th>
                <th rowspan="2">Tax Deposited in respect of the deductee (Rs.)</th>
                <th colspan="4">Challan Identification Number (CIN)</th>
            </tr>
            <tr>
                <th>BSR Code of the Bank Branch</th>
                <th>Date on which tax deposited (dd/mm/yyyy)</th>
                <th>Challan Serial Number</th>
                <th>Status of matching With OLTAS</th>
            </tr>
        </thead>
        <tbody>
            @php $slNo = 1; @endphp
            @foreach($records as $rec)
                @php
                    $t = 0;
                    $dedList = json_decode($rec->deductions, true) ?: [];
                    foreach ($dedList as $d) {
                        if (strtolower($d['short_name'] ?? '') === 'tds') {
                            $t = (float)($d['value'] ?? 0);
                        }
                    }
                @endphp
                @if($t > 0)
                    <tr>
                        <td class="text-center">{{ $slNo++ }}</td>
                        <td class="text-right">{{ number_format($t, 2) }}</td>
                        <td class="text-center">9900001</td>
                        <td class="text-center">05/{{ str_pad($rec->payoutMonth->payout_month, 2, '0', STR_PAD_LEFT) }}/{{ $rec->payoutMonth->payout_year }}</td>
                        <td class="text-center">{{ 1000 + $slNo }}</td>
                        <td class="text-center">Matched</td>
                    </tr>
                @endif
            @endforeach
            <tr class="bold" style="background-color: #f2f2f2;">
                <td class="text-center">Total (Rs.)</td>
                <td class="text-right">{{ number_format($totalTds, 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">Verification</div>
    <p>I, <strong>{{ $companySettings->contact_person ?? 'HR Admin' }}</strong>, working in the capacity of HR Admin do hereby certify that the information given above is true, complete and correct and is based on the books of account, documents, TDS statements, TDS deposited and other available records.</p>
    
    <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; border: none;">Place: {{ $companySettings->city ?? '' }}</td>
            <td style="width: 50%; text-align: right; border: none;">(Signature of person responsible for deduction of tax)</td>
        </tr>
        <tr>
            <td style="border: none;">Date: {{ date('d/m/Y') }}</td>
            <td style="border: none; text-align: right;">Full Name: {{ $companySettings->contact_person ?? 'HR Admin' }}</td>
        </tr>
    </table>


    <!-- PART B PAGE -->
    <div class="page-break"></div>
    <div class="text-center">
        <div class="main-title">PART B (Annexure-I)</div>
        <div class="subtitle">In relation to employees for tax deduction under section 192</div>
        <div class="bold">Details of salary paid and any other income and tax deducted</div>
    </div>

    <table class="gov-table">
        <tr>
            <td style="width: 5%;" class="text-center bold">A</td>
            <td style="width: 65%;" class="bold">Whether opting out of taxation u/s 115BAC(1A)?</td>
            <td style="width: 30%;" class="text-center bold">{{ $regime === 'old' ? 'YES' : 'NO' }}</td>
        </tr>
    </table>

    <table class="gov-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 65%;">Particulars</th>
                <th style="width: 30%;" class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bold">1.</td>
                <td class="bold">Gross Salary</td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td>(a)</td>
                <td>Salary as per provisions contained in section 17(1)</td>
                <td class="text-right">{{ number_format($grossSalary, 2) }}</td>
            </tr>
            <tr>
                <td>(b)</td>
                <td>Value of perquisites under section 17(2) (as per Form No. 12BA)</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr>
                <td>(c)</td>
                <td>Profits in lieu of salary under section 17(3) (as per Form No. 12BA)</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr class="bold">
                <td>(d)</td>
                <td>Total</td>
                <td class="text-right">{{ number_format($grossSalary, 2) }}</td>
            </tr>
            <tr>
                <td>(e)</td>
                <td>Reported total amount of salary received from other employer(s)</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr>
                <td class="bold">2.</td>
                <td class="bold">Less: Allowances to the extent exempt under section 10</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr class="bold" style="background-color: #f9f9f9;">
                <td>3.</td>
                <td>Total amount of salary received from current employer [1(d)-2]</td>
                <td class="text-right">{{ number_format($grossSalary, 2) }}</td>
            </tr>
            <tr>
                <td class="bold">4.</td>
                <td class="bold">Less: Deductions under section 16</td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td>(a)</td>
                <td>Standard deduction under section 16(ia)</td>
                <td class="text-right">{{ number_format($standardDeduction, 2) }}</td>
            </tr>
            <tr>
                <td>(b)</td>
                <td>Entertainment allowance under section 16(ii)</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr>
                <td>(c)</td>
                <td>Tax on employment under section 16(iii)</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr class="bold">
                <td>5.</td>
                <td>Total amount of deductions under section 16 [4(a)+4(b)+4(c)]</td>
                <td class="text-right">{{ number_format($standardDeduction, 2) }}</td>
            </tr>
            <tr class="bold" style="background-color: #f2f2f2;">
                <td>6.</td>
                <td>Income chargeable under the head "Salaries" [(3+1(e)-5]</td>
                <td class="text-right">
                    @php $incomeSalaries = max(0, $grossSalary - $standardDeduction); @endphp
                    {{ number_format($incomeSalaries, 2) }}
                </td>
            </tr>
            <tr>
                <td class="bold">7.</td>
                <td>Add: Any other income reported by the employee</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr class="bold">
                <td>8.</td>
                <td>Gross total income (6+7)</td>
                <td class="text-right">{{ number_format($incomeSalaries, 2) }}</td>
            </tr>
            <tr>
                <td class="bold">9.</td>
                <td class="bold">Deductions under Chapter VI-A</td>
                <td class="text-right"></td>
            </tr>
            <tr>
                <td>(a)</td>
                <td>Deduction in respect of contributions to provident fund etc. under section 80C</td>
                <td class="text-right">{{ number_format($totalPf, 2) }}</td>
            </tr>
            <tr class="bold">
                <td>10.</td>
                <td>Aggregate of deductible amount under Chapter VI-A</td>
                <td class="text-right">{{ number_format($totalPf, 2) }}</td>
            </tr>
            <tr class="bold" style="background-color: #f2f2f2;">
                <td>11.</td>
                <td>Total taxable income (8-10)</td>
                <td class="text-right">
                    @php $taxableIncome = max(0, $incomeSalaries - $totalPf); @endphp
                    {{ number_format($taxableIncome, 2) }}
                </td>
            </tr>
            <tr class="bold">
                <td>12.</td>
                <td>Tax on total income</td>
                <td class="text-right">{{ number_format($totalTds, 2) }}</td>
            </tr>
            <tr class="bold" style="background-color: #f2f2f2;">
                <td>13.</td>
                <td>Net tax payable</td>
                <td class="text-right">{{ number_format($totalTds, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">Verification</div>
    <p>I, <strong>{{ $companySettings->contact_person ?? 'HR Admin' }}</strong>, working in the capacity of HR Admin do hereby certify that the information given above is true, complete and correct and is based on the books of account, documents, TDS statements, and other available records.</p>
    
    <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; border: none;">Place: {{ $companySettings->city ?? '' }}</td>
            <td style="width: 50%; text-align: right; border: none;">(Signature of person responsible for deduction of tax)</td>
        </tr>
        <tr>
            <td style="border: none;">Date: {{ date('d/m/Y') }}</td>
            <td style="border: none; text-align: right;">Full Name: {{ $companySettings->contact_person ?? 'HR Admin' }}</td>
        </tr>
    </table>

</body>
</html>

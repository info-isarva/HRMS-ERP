<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payslip</title>
    <style>
    .payslip-card {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      
    }

    .payslip-card h2, .payslip-card h4 {
      text-align: center;
      margin: 5px 0;
    }

    .payslip-section {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
      flex-wrap: wrap;
    }

    .payslip-section div {
      width: 48%;
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }

    th {
      background: #f0f0f0;
    }

    .total-row {
      font-weight: bold;
    }

    .net-pay {
      text-align: center;
      margin-top: 20px;
      font-size: 18px;
    }

    /* .signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
    } */

    .signature-block {
      width: 40%;
      text-align: center;
    }

    .signature-line {
      border-top: 1px solid #000;
      margin-top: 40px;
    }

    .footer-note {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
      color: #555;
    }
    .no-border {
            border: none;
        }
  </style>
  @php
    use App\Helper\NumberHelper;
@endphp
    </head>
    <body>
    <div class="payslip-card">
                <h2>Payslip</h2>
                <br />
                <div style="text-align:center; padding-bottom: 40px;">
                @php
                    $logoPath = null;
                    if (!empty($companySettings->logo_image)) {
                        // Check if current logo is SVG
                        if (str_ends_with(strtolower($companySettings->logo_image), '.svg')) {
                            // Try to find PNG or JPG version
                            $basePath = pathinfo($companySettings->logo_image, PATHINFO_DIRNAME);
                            $baseName = pathinfo($companySettings->logo_image, PATHINFO_FILENAME);
                            
                            // Look for PNG first, then JPG
                            $pngPath = $basePath . '/' . str_replace('.svg', '.png', basename($companySettings->logo_image));
                            $jpgPath = $basePath . '/' . str_replace('.svg', '.jpg', basename($companySettings->logo_image));
                            
                            if (file_exists(public_path($pngPath))) {
                                $logoPath = $pngPath;
                            } elseif (file_exists(public_path($jpgPath))) {
                                $logoPath = $jpgPath;
                            } elseif (file_exists(public_path('assets/img/logo.png'))) {
                                $logoPath = 'assets/img/logo.png';
                            }
                        } else {
                            $logoPath = $companySettings->logo_image;
                        }
                    }
                @endphp
                
                @if($logoPath && file_exists(public_path($logoPath)))
                    <img src="{{ public_path($logoPath) }}" height="60" style="object-fit: cover;">
                @else
                    <div style="width: 60px; height: 60px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; margin: 0 auto;">
                        LOGO
                    </div>
                @endif
                    <h4>{{ $companySettings->company_name }}</h4>
                    <div style="padding: 0 24%;">
                        <p style="text-align:center;">{{ $companySettings->address }}</p>
                    </div>
                </div>
                <table class="no-border">
                    <tr><td class="no-border" style="width: 50%;"><div>Date of Joining: <strong>{{ date('d-m-Y', strtotime($attendances[0]->employee['date_of_joining'])) }}</strong></div></td>
                <td class="no-border"><div>Designation: <strong>{{ $designations[$attendances[0]->employee['designation']] ?? 'Unknown' }}</strong></div></td>
                </tr>
                    <tr><td class="no-border"><div>Employee Name: <strong>{{ strtoupper($attendances[0]->employee['name']) }}</strong></div></td>
                <td class="no-border"><div>Worked Days: <strong>{{ $attendances[0]->employee_worked_days }}</strong></div></td></tr>
                    <tr><td class="no-border"><div>Pay Period: <strong>{{ $monthName }}</strong></div></td><td class="no-border"><div>Department: <strong>{{ $departments[$attendances[0]->employee['department']] ?? 'Unknown' }}</strong></div></td></tr>
                </table>

                <table>
                    <thead>
                        <tr>
                            <th>Earnings</th>
                            <th>Amount</th>
                            <th>Deductions</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                      @php
                        // Collect applicable earnings and deductions separately
                        $applicableEarnings = collect($attendances[0]->earnings)->filter(function($earning) {
                            return $earning['applicable'] && !empty(trim($earning['name']));
                        })->values();
                        
                        $applicableDeductions = collect($attendances[0]->deductions)->filter(function($deduction) {
                            return $deduction['applicable'] && !empty(trim($deduction['name']));
                        })->values();
                        
                        $maxRows = max($applicableEarnings->count(), $applicableDeductions->count());
                      @endphp
                      
                      @for($i = 0; $i < $maxRows; $i++)
                        <tr>
                            <td>{{ $applicableEarnings->get($i)['name'] ?? '' }}</td>
                            <td>{{ $applicableEarnings->get($i) ? get_currency_symbol() . ' ' . round($applicableEarnings->get($i)['value']) : '' }}</td>
                            <td>{{ $applicableDeductions->get($i)['name'] ?? '' }}</td>
                            <td>{{ $applicableDeductions->get($i) ? get_currency_symbol() . ' ' . round($applicableDeductions->get($i)['value']) : '' }}</td>
                        </tr>
                      @endfor
                      
                      @if($maxRows > 0)
                        <tr class="total-row">
                            <td><strong>Total Earnings</strong></td>
                            <td><strong>{{ get_currency_symbol() }}{{ number_format($attendances[0]->totalEarnings, 0) }}/-</strong></td>
                            <td><strong>Total Deductions</strong></td>
                            <td><strong>{{ get_currency_symbol() }}{{ number_format($attendances[0]->totalDeductions, 0) }}/-</strong></td>
                        </tr>
                      @endif
                    </tbody>
                </table>

                <div class="net-pay">
                    <strong>Net Pay: {{ get_currency_symbol() }}{{ number_format($attendances[0]->netPay, 0) }}/-</strong><br>
                    <em>{{ \App\Helper\NumberHelper::numberToWords($attendances[0]->netPay) }}</em>
                </div>

                <!-- <div class="signatures">
        <div class="signature-block">
          Employer Signature
          <div class="signature-line"></div>
        </div>
        <div class="signature-block">
          Employee Signature
          <div class="signature-line"></div>
        </div>
      </div> -->

                <p class="footer-note">This is a system generated payslip, hence no signature is required</p>
            </div>
    </body>
    </html>
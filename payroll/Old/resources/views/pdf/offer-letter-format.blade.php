same error coming please debug deeper<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Offer Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            margin: 40px;
        }

        .header {
            text-align: right;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        

        .company-info {
            font-size: 14px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }

        .signature-section {
            margin-top: 40px;
        }

        .footer {
            font-size: 12px;
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .no-border {
            border: none;
        }

        .text-cntr {
            text-align: center;
        }
    </style>
</head>

<body>

    <htmlpageheader name="myheader">

        <div style="
        text-align: left;
        
        border-bottom: 1px solid #000;
        
        min-height: 100px;
    ">
            <table class="no-border">
                <tr>
                    <td style="width:70%;" class="no-border">
                        <div class="company-info">
                            <strong>{{ $companySettings->company_name }}</strong><br />
                            {{ $companySettings->address.", ".$companySettings->city.' - '.$companySettings->postal_code }}
                        </div>
                    </td>
                    <td class="no-border" style="width:30%;">
                        <div style="padding-left:30%;">
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
                                <div style="width: 60px; height: 60px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                    LOGO
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

        </div>

    </htmlpageheader>

    <htmlpagefooter name="myfooter">
        <div style="text-align: center; font-size: 10pt;border-top: 1px solid #000;padding: 10px 0;">
            Page {PAGENO} of {nb}
        </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />


    <!-- Your full form content here (tables, declaration, etc) -->
   <section>
   <table class="no-border">
        <tr>
            <td class="no-border" style="width: 80%">
                <div>HRD/Offer/{{$employee->id}}</div>
            </td>
            <td class="no-border" style="width: 20%">
                <div style="text-align:right;">{{date('d/m/Y', strtotime($now))}}</div>
            </td>
        </tr>
    </table>

    <h5 class="title">EMPLOYMENT OFFER LETTER</h5>

    <p>Dear {{strtoupper($employee->name)}},</p>

    <p>We are pleased to offer you a position with {{ $companySettings->company_name }} commencing on <strong>{{ date('d F Y', strtotime($employee->date_of_joining)) }}</strong>. Your position with the Company will be <strong>{{'“'.$designations[$employee->designation]. '”'}}</strong>
        based at Mangalore (India). Please note that the employment terms contained in this letter are subject to
        Company policy.</p>

    <div class="section-title">The terms of employment are as under:</div>

    <ul>
        <li><strong>Date of Joining:</strong> {{ date('d F Y', strtotime($employee->date_of_joining)) }}</li>
        <li><strong>CTC:</strong> {{ get_currency_symbol() }} {{number_format($netPayAnualy, 2)}} - Per Annum ({{$inWords}})</li>
    </ul>
    <p>* The breakup of salary is mentioned as Annexure A.</p>

    <p>Please refer to Annexure A for compensation details. Your annual salary will be subject to deductions as per
        prevailing tax laws and company policies.</p>

    <div class="section-title">Appointment:</div>

    <p>Your date of appointment is effective from the date of joining {{ date('d/m/Y', strtotime($employee->date_of_joining)) }}.</p>

    <p>
        a) You will be on probation for a period of six months from the date of your appointment, which may be extended
        at the sole discretion of the Company. If found suitable, you will be confirmed. During probation, {{ $companySettings->company_name }} reserves the right to terminate employment without notice.
    </p>

    <div class="section-title">Increment and Probation:</div>

    <p>Your growth and salary increase will depend solely on your performance and contribution. Salary increases are
        normally given on a periodic basis based on company policies.</p>

    <div class="section-title">Notice Period:</div>

    <p>Your employment can be terminated with 90 days’ notice on either side. No leave will be sanctioned during the
        notice period.</p>

    <div class="section-title">Transfer:</div>

    <p>Your services may be transferred to any of our units or departments in India or abroad. Compensation applicable
        to the specific location will be payable to you.</p>

    <div class="section-title">Confidentiality:</div>

    <p>
        You shall not disclose any confidential information of the company to unauthorized persons during or after your
        employment. Public communication regarding company affairs requires prior management approval.
    </p>

    <p>
        Upon termination, all company property must be returned.
    </p>

    <div class="section-title">Indemnity:</div>

    <p>
        You shall indemnify the company against any claims arising from previous employment or intellectual property
        issues.
    </p>

    <div class="section-title">Other Terms & Conditions:</div>

    <p>Absence for a continuous period of 4 days without prior approval of your superior (including overstay of leave /
        training) would be treated as abandonment of service.</p>

    <p>While in the employment of the Company, you are in no way allowed to be employed by any other Company on a
        temporary or part time basis or offer your services with or without pay to any person, legal entity or public
        authority or to be occupied in your own business without the prior consent of the Company.</p>

    <p>You confirm that you have disclosed fully to the Company all your business interests whether or not they are
        similar to or in conflict with the business or activities of the Company. You agree to disclose fully to the
        Company any such interests or circumstances which may arise during your employment.</p>

    <p>You will be required to apply and maintain the highest standards of personal conduct and integrity and comply
        with all company policies and procedures. All acts subversive of good conduct and discipline like
        insubordination, gross negligence, corruption, fraud, misappropriation etc. would warrant strong disciplinary
        action from the company.</p>

    <p>As a Company employee, you will be expected to abide by Company rules and regulations, including submitting
        weekly time records to your supervisor. You also agree to maintain the confidentiality for all confidential and
        proprietary information of the Company and agree, as a condition of your employment, to the bound by the
        Company’s Confidentiality, Intellectual Property Rights and Non-Compete Agreement. You will also be governed by
        the rules and regulations of the company as applicable to your category of employees.</p>

    <p>A duplicate of this letter is enclosed for your records. In the absence of our receiving your signed acceptance
        within 7 days from the date of this letter, this offer will be deemed to have been rejected by you and shall
        lapse.</p>

    <p>Provided that the above-mentioned period of 7 days may be extended in writing by any person/persons of the
        Company who have been specially authorized in this regard.</p>

    <p>In token of your acceptance of this offer, kindly sign and return the duplicate copy at the earliest to the
        undersigned at:</p>


    <div class="signature-section">
        <strong>
            HR Department<br />
            {{ $companySettings->company_name }}
        </strong>

    </div>
   </section>
<section style="page-break-before: always;">
<div class="section-title text-cntr">ENDORSEMENT</div>

<p>
    I hereby confirm acceptance of the above appointment on the terms and conditions stipulated. I undertake to
    comply with Company policies and the Code of Conduct.
</p>

<p>
    My date of joining {{ $companySettings->company_name }} will be __________________________.
</p>

<p style="font-size: 18px;">{{strtoupper($employee->name)}}</p>
 <p><strong>PLACE:</strong></p>
 <p><strong>     DATE:</strong></p>
 <p><strong>       SIGNATURE OF THE CANDIDATE:
    </strong>

</p>
</section>
    <section style="page-break-before: always;">
    <div class="section-title text-cntr">Annexure A</div>

<table>
    <tr>
        <td colspan="2" style="text-align:center">
            <p style="text-align:center"><strong>{{ $companySettings->company_name }}</strong><br />
            {{ $companySettings->address }}</p>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center">
            <p>Private & Confidential</p>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center">
            <p><strong>SALARY DETAILS</strong></p>
        </td>
    </tr>
    <tr>
        <td class="no-border" style="width:60%"><strong>NAME:</strong> {{strtoupper($employee->name)}}</td>
        <td class="no-border" style="width:40%"><strong>DESIGNATION:</strong> {{ $designations[$employee->designation] }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>PARTICULARS</th>
            <th>MONTHLY AMOUNT ({{ get_currency_symbol() }})</th>
            <th>ANNUAL AMOUNT ({{ get_currency_symbol() }})</th>
        </tr>
    </thead>
    <tbody>
   

    @foreach($earnings as $index => $component)
       @php
       // Ensure value is numeric before calculating
       $numericValue = is_numeric($component['value']) ? (float)$component['value'] : 0;
            $annualValue = $numericValue * 12;
       @endphp
       @if($component['applicable'])
       <tr>
               <td>{{ $component['name'] }}</td>
               <td>{{ number_format($numericValue, 2) }}</td>
               <td>{{ number_format($annualValue, 2) }}</td>
           </tr>
           @endif
    @endforeach
   
    @php
    $totalEarnings_annually =$totalEarnings * 12;
    @endphp
    <tr>
        <td><strong>MONTHLY GROSS (MG)</strong></td>
        <td><strong>{{ number_format($totalEarnings, 2) }} /-</strong></td>
        <td><strong>{{ number_format($totalEarnings_annually, 2) }} /-</strong></td>
    </tr>
    @foreach($deductions as $index => $component)
       @php
       // Ensure value is numeric before calculating
       $numericValue = is_numeric($component['value']) ? (float)$component['value'] : 0;
        $annualValue = $numericValue * 12;
       @endphp
       @if($component['applicable'])
        <tr>
               <td>{{ $component['name'] }}</td>
               <td>{{ number_format($numericValue, 2) }} </td>
               <td>{{ number_format($annualValue, 2) }}</td>
           </tr>
           @endif
    @endforeach
   
        <tr>
            <td><strong>TOTAL COST TO COMPANY</strong></td>
            <td><strong>{{ get_currency_symbol() }} {{number_format($netPay, 2)}} /-</strong></td>
            <td><strong>{{ get_currency_symbol() }} {{number_format($netPayAnualy, 2)}} /-</strong></td>
        </tr>
    </tbody>
</table>

<p>
    <em>Note:</em> Payment of components is subject to applicable tax and statutory deductions. Salary components
    are governed by company policies and statutory guidelines.
</p>

<p>Yours sincerely,<br /><br />
    For {{ $companySettings->company_name }}</p>

<p>I have read, understood, and agree to the terms and conditions set forth in this offer letter.<br />
    Date – ______________________</p>
    </section>
   


</body>

</html>
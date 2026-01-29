<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Experience Letter</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #000;
            margin: 40px;
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 40px;
        }
        .content {
            font-size: 16px;
        }
        .signature {
            margin-top: 60px;
        }
        .signature p {
            margin: 5px 0;
        }
        .company-info {
            font-size: 14px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            margin: 20px 0;
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
            </td>            <td class="no-border" style="width:30%;">
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
    <h5 class="header">
        Experience Letter
    </h5>

    <div class="content">
        <p>Dear  {{ strtoupper($employee->name) }},</p>

        <p>
            At the outset, we would like to thank you for being with us for a good tenure.
        </p>

        <p>
            This letter is to certify that <strong>{{strtoupper($employee->name)}}</strong> was employed with our company, <strong>{{ $companySettings->company_name }}</strong>, from <strong>{{ date('d F Y', strtotime($employee->date_of_joining)) }}</strong> to <strong>{{ date('d F Y', strtotime($employee->date_of_resignation)) }}</strong>. Your designation at the time of your relieving was <strong>{{ $designations[$employee->designation] ?? 'Unknown' }}</strong>.
        </p>

        <p>
            During your association with us, we found that you were a highly motivated individual, self-starter and incredibly committed team player with strong conceptual knowledge in your area.
        </p>

        <p>
            We wish you all the best and success in all your future endeavors.
        </p>

        <div class="signature">
            <p>For {{ $companySettings->company_name }},</p>
            <br><br>
            <p><strong>Managing Director</strong></p>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Joining Form</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        /* .form-container { */
            /* border: 3px solid black; */
            /* padding: 20px;
            max-width: 900px;
            margin: auto;
        } */

        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }

        .left-section {
            width: 100%;
        }

        .photo-box {
            width: 150px;
            height: 180px;
            border: 1px solid black;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }

        td {
            padding: 5px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            text-transform: uppercase;
        }

        .bordered {
            border: 1px solid #000;
            min-height: 20px;
        }

        .declaration {
            font-size: 16px;
            text-align: justify;
            line-height: 2rem;
        }

        .signature-section td {
            padding-top: 20px;
        }

        .approvals td {
            text-align: center;
            padding: 20px 10px;
            border: 1px solid black;
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

    <div class="form-container">
        <section>
            <h5 class="header">Joining Form</h5>
            <div class="row">
                <div class="left-section">
                    <table>
                        <tr>
                            <td colspan="3" class="section-title">Personal Details:</td>
                        </tr>
                        <tr>
                            <td style="width: 80%;">
                                <table>
                                    <tr>
                                        <td style="width:30%">Name</td>
                                        <td style="width:50%">{{strtoupper($employee->name)}}</td>
                                    </tr>
                                    <tr>
                                        <td>Father Name</td>
                                        <td><span>{{$employee->personalDetail->father_name}}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Date of Birth</td>
                                        <td>{{date('d-m-Y', strtotime($employee->date_of_birth))}}</td>
                                    </tr>
                                    <tr>
                                        <td>Mobile No</td>
                                        <td>{{$employee->contact_number}}</td>
                                    </tr>
                                    <tr>
                                        <td>E-Mail ID</td>
                                        <td>{{$employee->email}}</td>
                                    </tr>
                                    <tr>
                                        <td>Permanent Address</td>
                                        <td style="height:30px;">{{$employee->personalDetail->address}}</td>
                                    </tr>
                                    <tr>
                                        <td>Temporary Address</td>
                                        <td style="height:30px;">{{$employee->personalDetail->temporary_address}}</td>
                                    </tr>
                                </table>
                            </td>
                            <!-- <td>
                                <div style="padding: 20px">
                                    <img src="{{ public_path('assets/images/1133766497.jpg') }}" width="120"
                                        height="120" style="object-fit: cover;">
                                </div>
                            </td> -->
                        </tr>


                    </table>
                </div>

            </div>
            <br />
            <table>
                <tr>
                    <td colspan="2" class="section-title">Emergency Details:</td>
                </tr>
                <tr>
                    <td style="width:30%">Blood Group</td>
                    <td style="width:70%">{{$employee->personalDetail->blood_group ? getBloodGroups()[$employee->personalDetail->blood_group] : '---'}}</td>
                </tr>
                <tr>
                    <td style="width:30%">Contact Person In Case of Emergency</td>
                    <td style="width:70%">{{$employee->personalDetail->emergency_contact_name ? $employee->personalDetail->emergency_contact_name : '---'}}</td>
                </tr>
                <tr>
                    <td style="width:30%">Contact No</td>
                    <td>{{$employee->personalDetail->emergency_contact_number ? $employee->personalDetail->emergency_contact_number : '---'}}</td>
                </tr>
            </table>
            <br />
            <table>
                <tr>
                    <td class="section-title">Declaration:</td>
                </tr>
                <tr>
                    <td class="declaration">
                        I declare that the information given, herein above, is true & correct to the best of my
                        knowledge &
                        belief & nothing material has been concealed. I understand that the above information is found
                        false
                        or incorrect, at any time during the course of my employment, my service will be terminated
                        forthwith without any notice or compensation.
                    </td>
                </tr>
            </table>

            <table class="signature-section">
                <tr>
                    <td style="width:20%">Date:</td>
                    <td style="width:50%">{{$now}}</td>
                    <td style="text-align:center;">
                        <div></div>
                    </td>
                </tr>
                <tr>
                    <td style="width:20%">Place:</td>
                    <td style="width:50%">{{$companySettings->city}}</td>
                    <td style="width:30%">Signature of Applicant</td>

                </tr>
            </table>
        </section>

        <section style="page-break-before: always;">
            <table>
                <tr>
                    <td colspan="6" class="section-title">For Office Use Only</td>
                </tr>
                <tr>
                    <td style="width:25%">For the Post Of</td>
                    <td style="width:25%"></td>
                    <td style="width:25%">Gross Salary</td>
                    <td style="width:25%">Rs. {{ number_format($netPayAnualy, 2) }} /-</td>

                </tr>
                <tr>
                    <td>Reporting Date</td>
                    <td>{{$now}}</td>
                    <td>PF No</td>
                    <td>{{$employee->personalDetail->pf_account_number}}</td>

                </tr>
                <tr>
                    <td>Employee ID</td>
                    <td>{{$employee->employee_id}}</td>
                    <td>ESIC No</td>
                    <td>{{$employee->personalDetail->esic_number}}</td>
                </tr>
            </table>
            <br/>
            <table style="width:100%;">
                <tr>
                    <td colspan="3" style="height:40px;">Approved By :</td>

                </tr>
                <tr>

                    <td class="bordered" style="text-align:center;">Dept Head</td>
                    <td class="bordered" style="text-align:center;">Admin/HR</td>
                    <td class="bordered" style="text-align:center;">M.D</td>
                </tr>
                <tr>
                    <td class="bordered" style="height:80px; width: 33%;"></td>
                    <td class="bordered" style="height:80px; width: 33%;"></td>
                    <td class="bordered" style="height:80px; width: 33%;"></td>
                </tr>
            </table>
        </section>

    </div>

</body>

</html>
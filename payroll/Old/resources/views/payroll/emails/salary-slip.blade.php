<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $monthName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .content p {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .highlight {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .highlight strong {
            color: #007bff;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
        }
        .note strong {
            color: #856404;
        }
        .attachment-info {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .attachment-info i {
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $companyName ?? 'Company Name' }}</h1>
            <p>Payroll Department</p>
        </div>

        <div class="content">
            <h2>Dear {{ $employeeName }},</h2>
            
            <p>We hope this email finds you well.</p>
            
            <p>Please find attached your salary slip for <strong>{{ $monthName }}</strong>.</p>
            
            <div class="highlight">
                <strong>📄 Salary Slip for {{ $monthName }}</strong><br>
                Your detailed salary breakdown is attached as a PDF document.
            </div>

            <div class="attachment-info">
                <strong>📎 Attachment Details:</strong><br>
                • File Name: salary-slip-{{ $monthName }}.pdf<br>
                • Format: PDF Document<br>
                • Contains: Complete salary breakdown, earnings, deductions, and net pay
            </div>

            <p>The salary slip contains the following information:</p>
            <ul>
                <li>Your personal and employment details</li>
                <li>Detailed breakdown of earnings</li>
                <li>All applicable deductions</li>
                <li>Net salary amount</li>
                <li>Pay period information</li>
            </ul>

            <div class="note">
                <strong>Important Notes:</strong><br>
                • Please keep this salary slip for your records<br>
                • This is a system-generated document and does not require a signature<br>
                • For any queries regarding your salary, please contact the HR department<br>
                • If you notice any discrepancies, please report them immediately
            </div>

            <p>If you have any questions or need clarification regarding your salary slip, please don't hesitate to contact our HR department.</p>
            
            <p>Thank you for your continued service and dedication.</p>
            
            <p><strong>Best regards,</strong><br>
            Payroll Department<br>
            {{ $companyName ?? 'Company Name' }}</p>
        </div>

        <div class="footer">
            <p><em>This is an automated email. Please do not reply to this email address.</em></p>
            <p>© {{ date('Y') }} {{ $companyName ?? 'Company Name' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
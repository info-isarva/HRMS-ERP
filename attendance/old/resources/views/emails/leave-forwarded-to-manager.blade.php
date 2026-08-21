<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Forwarded</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            padding: 20px 0;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        
        .header {
            background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.025em;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 25px 30px;
        }
        
        .employee-info {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            padding: 20px;
            margin: 0 0 25px 0;
            border-radius: 12px;
            position: relative;
        }
        
        .employee-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #059669, #0d9488);
            border-radius: 2px;
        }
        
        .employee-info h3 {
            margin: 0 0 15px 0;
            color: #14532d;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .leave-summary {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .leave-summary h3 {
            margin: 0 0 20px 0;
            color: #1e293b;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            line-height: 1.2;
        }
        
        .leave-info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px 16px;
        }
        
        .leave-info-table td {
            width: 50%;
            vertical-align: top;
        }
        
        .info-item {
            background: linear-gradient(135deg, #fafafc 0%, #ffffff 100%);
            padding: 18px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            text-align: center;
            transition: all 0.2s ease;
            width: 100%;
            box-sizing: border-box;
        }
        
        .info-item .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #059669;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .info-item .value {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
        }
        
        .info-item .icon {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
            line-height: 1;
            vertical-align: middle;
        }
        
        .forwarding-note {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.1);
        }
        
        .forwarding-note .label {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #92400e;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        
        .forwarding-note .label .icon {
            margin-right: 8px;
        }
        
        .note-text {
            font-style: italic;
            color: #374151;
            line-height: 1.5;
        }
        
        .reason-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.1);
        }
        
        .reason-box .label {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #0369a1;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        
        .reason-text {
            font-style: italic;
            color: #374151;
            line-height: 1.5;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn {
            display: inline-block;
            padding: 16px 32px;
            margin: 0;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%) !important;
            color: #ffffff !important;
            border: none !important;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #047857 0%, #0f766e 50%, #0e7490 100%) !important;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(5, 150, 105, 0.4);
        }
        
        a.btn-primary {
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .company-info {
            margin-top: 15px;
            color: #9ca3af;
            font-size: 12px;
        }
        
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px 0;
            }
            
            .container {
                margin: 0 10px;
                border-radius: 12px;
            }
            
            .leave-info-table {
                border-spacing: 8px 12px;
            }
            
            .leave-info-table td {
                width: 100%;
                display: block;
            }
            
            .leave-info-table tr {
                display: block;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content, .footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Leave Application Forwarded</h1>
            <p>A leave request has been forwarded to you for approval</p>
        </div>
        
        <div class="content">
            <div class="employee-info">
                <h3>👤 Employee Information</h3>
                <p><strong>{{ $employee->name }}</strong>'s leave application has been forwarded to you for review and approval.</p>
                <p><strong>Email:</strong> {{ $employee->email }}</p>
                @if($employee->employee_id)
                    <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
                @endif
            </div>
            
            <div class="leave-summary">
                <div style="margin-bottom: 20px;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tr>
                            <td style="vertical-align: middle;">
                                <h3 style="margin: 0; display: flex; align-items: center;"><span style="margin-right: 8px; display: inline-block; vertical-align: middle;">📋</span><span style="vertical-align: middle;">Leave Details</span></h3>
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <a href="{{ url('/leaves/' . $leave->id) }}" 
                                   class="btn btn-primary"
                                   style="display: inline-block; padding: 8px 16px; margin: 0; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 12px; text-align: center; background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%) !important; color: #ffffff !important; border: none; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); letter-spacing: 0.3px; vertical-align: middle;">
                                   📁 Review & Approve
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <table class="leave-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🏷️</span>
                                <div class="label">Leave Type</div>
                                <div class="value">{{ $leaveType->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">📅</span>
                                <div class="label">Total Days</div>
                                <div class="value">{{ $leave->total_days }} day{{ $leave->total_days > 1 ? 's' : '' }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🚀</span>
                                <div class="label">Start Date</div>
                                <div class="value">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🏁</span>
                                <div class="label">End Date</div>
                                <div class="value">{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            @if($forwardingNote)
            <div class="forwarding-note">
                <div class="label"><span class="icon">📝</span>HR Forwarding Note</div>
                <div class="note-text">{{ $forwardingNote }}</div>
            </div>
            @endif
            
            <div class="reason-box">
                <div class="label"><span class="icon">💭</span> Reason for Leave</div>
                <div class="reason-text">{{ $leave->reason }}</div>
            </div>
            
            @if($leave->emergency_contact_name)
            <div class="leave-summary">
                <h3 style="display: flex; align-items: center; justify-content: center;"><span style="margin-right: 8px; display: inline-block; vertical-align: middle;">🆘</span><span style="vertical-align: middle;">Emergency Contact</span></h3>
                <table class="leave-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🔷</span>
                                <div class="label">Contact Name</div>
                                <div class="value">{{ $leave->emergency_contact_name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">📞</span>
                                <div class="label">Phone Number</div>
                                <div class="value">{{ $leave->emergency_contact_phone ?: 'Not Provided' }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            @endif
            
            <div style="text-align: center; margin: 20px 0;">
                <span class="status-badge">
                    ⏳ Awaiting Your Approval
                </span>
            </div>
            
            <div style="background-color: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #14532d; font-size: 13px;">
                    <strong>📌 Action Required:</strong> As the reporting manager, please review this leave application and approve or reject it through the HRMS system. 
                    Click the "Review & Approve" button above to access the application.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>HRMS - Human Resource Management System</strong></p>
            <div class="company-info">
                <p>This is an automated notification. Please do not reply to this email.</p>
                <p>Generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
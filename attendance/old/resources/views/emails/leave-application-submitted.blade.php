<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Leave Application</title>
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);
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
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            border: 1px solid #c7d2fe;
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
            background: linear-gradient(to bottom, #4f46e5, #7c3aed);
            border-radius: 2px;
        }
        
        .employee-info h3 {
            margin: 0 0 15px 0;
            color: #1e1b4b;
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
        
        .info-grid {
            display: block;
        }
        
        .info-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 16px;
            margin-bottom: 16px;
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
        
        /* Table-based layout for email client compatibility */
        .leave-info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px 16px;
        }
        
        .leave-info-table td {
            width: 50%;
            vertical-align: top;
        }
        
        .info-item:hover {
            border-color: #c7d2fe;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        }
        
        .info-item .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6366f1;
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
        
        .leave-details {
            background-color: #fef9e7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .leave-details h4 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 14px;
            font-weight: 700;
        }
        
        .reason-box {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 2px solid #fcd34d;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.1);
        }
        
        .reason-box .label {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #92400e;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        
        .reason-box .label .icon {
            margin-right: 8px;
        }
        
        .reason-text {
            font-style: normal;
            font-weight: 600;
            font-size: 16px;
            color: #1e293b;
            line-height: 1.5;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #92400e;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }
        
        .action-buttons {
            text-align: center;
            margin: 20px 0;
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
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%) !important;
            color: #ffffff !important;
            border: none !important;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6b21a8 50%, #db2777 100%) !important;
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 70, 229, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        /* Additional email client specific styles */
        a.btn-primary {
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        a.btn-primary:visited {
            color: #ffffff !important;
        }
        
        a.btn-primary:active {
            color: #ffffff !important;
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
            
            .leave-summary div[style*="flex"] {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px;
            }
            
            .leave-summary h3 {
                margin-bottom: 10px !important;
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
            
            .employee-info, .leave-summary {
                padding: 16px;
            }
            
            .info-item {
                padding: 15px;
                margin-bottom: 12px;
            }
            
            .btn {
                display: block;
                margin: 15px 0;
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Leave Application Submitted</h1>
            <p>A new leave request requires your attention</p>
        </div>
        
        <div class="content">
            <div class="employee-info">
                <h3>👤 Employee Information</h3>
                <p><strong>{{ $employee->name }}</strong> has submitted a new leave application.</p>
                <p><strong>Email:</strong> {{ $employee->email }}</p>
                @if($employee->employee_id)
                    <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
                @endif
            </div>
            
            <div class="reason-box">
                <div class="label"><span class="icon">💭</span> Reason for Leave</div>
                <div class="reason-text">{{ $leave->reason }}</div>
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
                                   style="display: inline-block; padding: 8px 16px; margin: 0; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 12px; text-align: center; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%) !important; color: #ffffff !important; border: none; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); letter-spacing: 0.3px; vertical-align: middle;">
                                   📁 View Application
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Table-based layout for maximum email client compatibility -->
                <table class="leave-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <!-- Leave Type -->
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🏷️</span>
                                <div class="label">Leave Type</div>
                                <div class="value">{{ $leaveType->name }}</div>
                            </div>
                        </td>
                        <!-- Total Days -->
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">📅</span>
                                <div class="label">Total Days</div>
                                <div class="value">{{ $leave->total_days }} day{{ $leave->total_days > 1 ? 's' : '' }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <!-- Start Date -->
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🚀</span>
                                <div class="label">Start Date</div>
                                <div class="value">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <!-- End Date -->
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
            
            @if($leave->has_lop && $leave->lop_days > 0)
            <div class="leave-details">
                <h4>⚠️ Loss of Pay (LOP) Information</h4>
                <p><strong>Paid Days:</strong> {{ $leave->paid_days ?? 0 }}</p>
                <p><strong>LOP Days:</strong> {{ $leave->lop_days }}</p>
                <p style="color: #dc2626; font-weight: 600;">This leave application includes {{ $leave->lop_days }} day(s) of Loss of Pay.</p>
            </div>
            @endif
            

            
            @if($leave->emergency_contact_name)
            <div class="leave-summary">
                <h3 style="display: flex; align-items: center; justify-content: center;"><span style="margin-right: 8px; display: inline-block; vertical-align: middle;">🆘</span><span style="vertical-align: middle;">Emergency Contact</span></h3>
                <table class="leave-info-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <!-- Contact Name -->
                        <td>
                            <div class="info-item">
                                <span class="icon" style="display: block; text-align: center;">🔷</span>
                                <div class="label">Contact Name</div>
                                <div class="value">{{ $leave->emergency_contact_name }}</div>
                            </div>
                        </td>
                        <!-- Phone Number -->
                        <td>
                            <div class="info-item" @if(!$leave->emergency_contact_phone) style="opacity: 0.5;" @endif>
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
                    ⏳ {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                </span>
            </div>
            

            
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #1e40af; font-size: 13px;">
                    <strong>📌 Next Steps:</strong> Please review this leave application and take appropriate action. 
                    You can approve, reject, or forward this application through the HRMS system.
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
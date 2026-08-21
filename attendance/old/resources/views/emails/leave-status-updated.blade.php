<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application Status Updated</title>
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
        
        /* Dynamic header colors based on status */
        .header-approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        }
        
        .header-rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
        }
        
        .header-approved-by-manager {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
        }
        
        .header {
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
        
        .status-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            border: 1px solid #c7d2fe;
            padding: 20px;
            margin: 0 0 25px 0;
            border-radius: 12px;
            position: relative;
        }
        
        .status-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 2px;
        }
        
        .status-approved::before {
            background: linear-gradient(to bottom, #10b981, #059669);
        }
        
        .status-rejected::before {
            background: linear-gradient(to bottom, #ef4444, #dc2626);
        }
        
        .status-approved-by-manager::before {
            background: linear-gradient(to bottom, #3b82f6, #2563eb);
        }
        
        .status-info h3 {
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
            color: #4f46e5;
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
        
        .rejection-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #fca5a5;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.1);
        }
        
        .rejection-box .label {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #dc2626;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .status-approved-by-manager {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
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
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
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
        @if($status == 'approved')
            <div class="header header-approved">
                <h1>✅ Leave Application Approved</h1>
                <p>Your leave request has been approved</p>
            </div>
        @elseif($status == 'rejected')
            <div class="header header-rejected">
                <h1>❌ Leave Application Rejected</h1>
                <p>Your leave request has been rejected</p>
            </div>
        @elseif($status == 'approved_by_manager')
            <div class="header header-approved-by-manager">
                <h1>✅ Manager Approved</h1>
                <p>Your manager has approved your leave request</p>
            </div>
        @else
            <div class="header">
                <h1>🔄 Leave Status Updated</h1>
                <p>Your leave application status has been updated</p>
            </div>
        @endif
        
        <div class="content">
            <div class="status-info status-{{ str_replace('_', '-', $status) }}">
                <h3>📋 Status Update</h3>
                <p><strong>Dear {{ $employee->name }},</strong></p>
                
                @if($status == 'approved')
                    <p>Your leave application has been <strong>approved</strong> and is now confirmed.</p>
                @elseif($status == 'rejected')
                    <p>We regret to inform you that your leave application has been <strong>rejected</strong>.</p>
                @elseif($status == 'approved_by_manager')
                    <p>Your reporting manager has <strong>approved</strong> your leave application. It is now pending final HR approval.</p>
                @else
                    <p>Your leave application status has been updated to: <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}</strong></p>
                @endif
                
                @if($approvedBy)
                    <p><strong>
                        @if($status == 'approved_by_manager')
                            Manager
                        @elseif($status == 'rejected')
                            Rejected by
                        @else
                            Approved by
                        @endif
                    :</strong> {{ $approvedBy }}</p>
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
                                   style="display: inline-block; padding: 8px 16px; margin: 0; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 12px; text-align: center; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%) !important; color: #ffffff !important; border: none; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); letter-spacing: 0.3px; vertical-align: middle;">
                                   📁 View Application
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
            
            <div class="reason-box">
                <div class="label"><span class="icon">💭</span> Original Reason</div>
                <div class="reason-text">{{ $leave->reason }}</div>
            </div>
            
            @if($status == 'rejected' && $rejectionReason)
            <div class="rejection-box">
                <div class="label"><span class="icon">📝</span>Reason for Rejection</div>
                <div class="reason-text">{{ $rejectionReason }}</div>
            </div>
            @endif
            
            <div style="text-align: center; margin: 20px 0;">
                <span class="status-badge status-{{ str_replace('_', '-', $status) }}">
                    @if($status == 'approved')
                        ✅ Approved
                    @elseif($status == 'rejected')
                        ❌ Rejected
                    @elseif($status == 'approved_by_manager')
                        ✅ Manager Approved
                    @else
                        🔄 {{ ucfirst(str_replace('_', ' ', $status)) }}
                    @endif
                </span>
            </div>
            
            @if($status == 'approved')
            <div style="background-color: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #14532d; font-size: 13px;">
                    <strong>Notice:</strong> Your leave has been approved. Please ensure proper handover of responsibilities before your leave period.
                </p>
            </div>
            @elseif($status == 'rejected')
            <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #dc2626; font-size: 13px;">
                    <strong>📋 Next Steps:</strong> If you have any questions about this rejection, please contact HR or your reporting manager. 
                    You may submit a new leave application if needed.
                </p>
            </div>
            @elseif($status == 'approved_by_manager')
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #1e40af; font-size: 13px;">
                    <strong>📌 Status Update:</strong> Your manager has approved your leave request. It is now pending final approval from HR. 
                    You will be notified once the final decision is made.
                </p>
            </div>
            @endif
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
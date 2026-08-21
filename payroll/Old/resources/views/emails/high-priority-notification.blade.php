<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Priority Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .priority-badge {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        .content {
            padding: 30px;
        }
        .notification-title {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .notification-message {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }
        .notification-meta {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .meta-label {
            font-weight: 600;
            color: #495057;
        }
        .meta-value {
            color: #6c757d;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-right: 10px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }
        .urgent-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        .urgent-notice .icon {
            display: inline-block;
            margin-right: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 High Priority Notification</h1>
            <span class="priority-badge">URGENT - IMMEDIATE ATTENTION REQUIRED</span>
        </div>
        
        <div class="content">
            <div class="urgent-notice">
                <span class="icon">⚠️</span>
                <strong>This is a high priority notification requiring your immediate attention.</strong>
            </div>
            
            <div class="notification-title">
                {{ $notification->title }}
            </div>
            
            <div class="notification-message">
                {{ $notification->message }}
            </div>
            
            <div class="notification-meta">
                <div class="meta-item">
                    <span class="meta-label">Priority Level:</span>
                    <span class="meta-value" style="color: #dc3545; font-weight: bold;">{{ strtoupper($notification->priority) }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date & Time:</span>
                    <span class="meta-value">{{ $notification->start_date->format('F j, Y \a\t g:i A') }}</span>
                </div>
                @if($notification->end_date)
                <div class="meta-item">
                    <span class="meta-label">Valid Until:</span>
                    <span class="meta-value">{{ $notification->end_date->format('F j, Y \a\t g:i A') }}</span>
                </div>
                @endif
                <div class="meta-item">
                    <span class="meta-label">Recurrence:</span>
                    <span class="meta-value">
                        @if($notification->recurrence_type === 'once')
                            One-time notification
                        @else
                            Repeats {{ $notification->recurrence_type }}ly
                        @endif
                    </span>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/notifications/all') }}" class="action-button">
                    View All Notifications
                </a>
                <a href="{{ url('/') }}" class="action-button" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                    Go to Dashboard
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>
                <strong>{{ config('app.name', 'HRMS') }}</strong><br>
                This is an automated notification from your HRMS system.<br>
                Please do not reply to this email.
            </p>
            <p style="margin-top: 15px; font-size: 11px;">
                If you believe you received this email in error, please contact your system administrator.
            </p>
        </div>
    </div>
</body>
</html>
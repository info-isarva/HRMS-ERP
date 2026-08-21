<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .summary {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        
        .summary p {
            margin: 0;
            color: #555;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        table th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        table tr:hover {
            background-color: #e8f4fd;
        }
        
        .activity-type {
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            font-size: 9px;
        }
        
        .activity-create { background-color: #28a745; }
        .activity-update { background-color: #ffc107; color: #212529; }
        .activity-delete { background-color: #dc3545; }
        .activity-login { background-color: #17a2b8; }
        .activity-logout { background-color: #6c757d; }
        .activity-failed-login { background-color: #dc3545; }
        .activity-password-change { background-color: #ffc107; color: #212529; }
        .activity-calculate { background-color: #007bff; }
        .activity-default { background-color: #343a40; }
        
        .text-truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on: {{ $date }}</p>
    </div>
    
    <div class="summary">
        <p><strong>Total Records:</strong> {{ $records->count() }}</p>
        <p><strong>Export Format:</strong> PDF</p>
        <p><strong>System:</strong> HRMS Payroll Management</p>
    </div>
    
    @if($records->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Timestamp</th>
                    <th style="width: 8%;">User ID</th>
                    <th style="width: 12%;">User Name</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Role</th>
                    <th style="width: 10%;">Activity Type</th>
                    <th style="width: 8%;">Module</th>
                    <th style="width: 20%;">Description</th>
                    <th style="width: 8%;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->activity_timestamp ? $record->activity_timestamp->format('d-m-Y H:i:s') : 'N/A' }}</td>
                        <td>{{ $record->user_id ?? 'N/A' }}</td>
                        <td>{{ $record->user_name ?? 'N/A' }}</td>
                        <td>{{ $record->email ?? 'N/A' }}</td>
                        <td>{{ $record->role_name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $activityClass = 'activity-default';
                                $activityType = strtolower($record->activity_type ?? '');
                                if (str_contains($activityType, 'create') || str_contains($activityType, 'add')) {
                                    $activityClass = 'activity-create';
                                } elseif (str_contains($activityType, 'update') || str_contains($activityType, 'edit')) {
                                    $activityClass = 'activity-update';
                                } elseif (str_contains($activityType, 'delete') || str_contains($activityType, 'remove')) {
                                    $activityClass = 'activity-delete';
                                } elseif (str_contains($activityType, 'login')) {
                                    $activityClass = 'activity-login';
                                } elseif (str_contains($activityType, 'logout')) {
                                    $activityClass = 'activity-logout';
                                } elseif (str_contains($activityType, 'failed_login')) {
                                    $activityClass = 'activity-failed-login';
                                } elseif (str_contains($activityType, 'password_change')) {
                                    $activityClass = 'activity-password-change';
                                } elseif (str_contains($activityType, 'calculate')) {
                                    $activityClass = 'activity-calculate';
                                }
                            @endphp
                            <span class="activity-type {{ $activityClass }}">
                                {{ $record->activity_type ?? 'Unknown' }}
                            </span>
                        </td>
                        <td>{{ $record->module ?? 'N/A' }}</td>
                        <td class="text-truncate">{{ $record->description ?? 'N/A' }}</td>
                        <td>{{ $record->ip_address ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No activity log records found for the selected criteria.</p>
        </div>
    @endif
    
    <div class="footer">
        <p>© {{ date('Y') }} HRMS Payroll System - Activity Logs Report</p>
        <p>This document was automatically generated and contains {{ $records->count() }} record(s).</p>
    </div>
</body>
</html>

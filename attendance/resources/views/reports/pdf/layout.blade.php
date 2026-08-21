<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'HRMS Report' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4f46e5;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 500;
        }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-orange { background-color: #ffedd5; color: #9a3412; }
        .badge-gray { background-color: #f3f4f6; color: #374151; }
        
        .filters {
            margin-bottom: 20px;
            font-size: 11px;
            color: #666;
            background: #f8fafc;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    @if(isset($startDate) && isset($endDate) && $startDate && $endDate)
    <div class="filters">
        <strong>Period:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </div>
    @endif

    @yield('content')

    <div class="footer">
        &copy; {{ date('Y') }} HRMS Attendance System - All Rights Reserved. Page <script type="text/php">echo $PAGE_NUM . " of " . $PAGE_COUNT;</script>
    </div>
</body>
</html>

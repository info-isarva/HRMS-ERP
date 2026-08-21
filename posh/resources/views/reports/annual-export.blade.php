<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POSH Annual Report {{ $report->report_year }}</title>
    <link rel="stylesheet" href="{{ asset('css/posh-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 40px; max-width: 960px; margin: 0 auto; font-family: Inter, system-ui, sans-serif; }
        @media print { .no-print { display: none; } body { padding: 20px; } }
        .no-print button { padding: 0.5rem 1rem; font-size: 14px; cursor: pointer; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; }
        h1 { font-size: 1.35rem; margin: 0 0 0.25rem; color: #1e3a8a; }
        .export-subtitle { font-size: 0.875rem; color: #64748b; margin: 0 0 1.25rem; }
    </style>
</head>
<body>
    <p class="no-print"><button type="button" onclick="window.print()">Print / Save as PDF</button></p>
    <h1>POSH Annual Report — {{ $report->report_year }}</h1>
    <p class="export-subtitle">{{ \App\Models\Organization::sanitizeDisplayName($data['organization'] ?? '') }} · Section 22 particulars</p>
    @include('reports._annual-body', ['report' => $report, 'data' => $data])
</body>
</html>

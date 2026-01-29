<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 10px; color:#111827; }
        .meta { font-size: 11px; margin-bottom: 15px; color:#374151; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #667eea; color: #ffffff; padding: 6px 4px; font-size: 11px; text-align: left; }
        td { padding: 5px 4px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .small { font-size: 10px; color:#6b7280; }
        .nowrap { white-space: nowrap; }
        .badge { display:inline-block; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:600; color:#fff; }
        .badge-create { background:#10b981; }
        .badge-update { background:#3b82f6; }
        .badge-delete { background:#ef4444; }
        .badge-login { background:#0ea5e9; }
        .footer { margin-top: 8px; font-size:10px; color:#6b7280; text-align:right; }
        .truncate { max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
</head>
<body>
    <h1>Activity Logs Export</h1>
    <div class="meta">
        Generated at: {{ $generated_at }}<br>
        Total Records: {{ $total }}
    </div>
    <table>
        <thead>
            <tr>
                <th class="nowrap">Timestamp</th>
                <th class="nowrap">User</th>
                <th>Email</th>
                <th class="nowrap">Role</th>
                <th class="nowrap">Type</th>
                <th class="nowrap">Module</th>
                <th>Description</th>
                <th class="nowrap">IP</th>
                <th>User Agent (partial)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                @php
                    $type = strtolower($r['activity_type']);
                    $badgeClass = 'badge';
                    if(str_contains($type,'create')||str_contains($type,'add')) $badgeClass .= ' badge-create';
                    elseif(str_contains($type,'update')||str_contains($type,'edit')) $badgeClass .= ' badge-update';
                    elseif(str_contains($type,'delete')||str_contains($type,'remove')) $badgeClass .= ' badge-delete';
                    elseif(str_contains($type,'login')||str_contains($type,'auth')) $badgeClass .= ' badge-login';
                @endphp
                <tr>
                    <td class="nowrap">{{ $r['timestamp'] }}</td>
                    <td class="nowrap">{{ $r['user_name'] ?? 'N/A' }}</td>
                    <td class="truncate">{{ $r['email'] ?? 'N/A' }}</td>
                    <td class="nowrap">{{ $r['role_name'] ?? 'N/A' }}</td>
                    <td class="nowrap"><span class="{{ $badgeClass }}">{{ $r['activity_type'] }}</span></td>
                    <td class="nowrap">{{ $r['module'] }}</td>
                    <td class="truncate">{{ $r['description'] }}</td>
                    <td class="nowrap">{{ $r['ip_address'] ?? 'N/A' }}</td>
                    <td class="truncate">{{ $r['user_agent'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">HRMS Activity Logs PDF Export</div>
</body>
</html>

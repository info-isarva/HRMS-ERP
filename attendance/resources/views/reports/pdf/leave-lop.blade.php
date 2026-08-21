@extends('reports.pdf.layout')

@section('content')
@if(isset($search) && $search)
<div class="filters" style="margin-top: -10px; margin-bottom: 20px;">
    <strong>Search:</strong> {{ $search }}
</div>
@endif

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Period</th>
            <th class="text-center">Total</th>
            <th class="text-center">Paid</th>
            <th class="text-center">LOP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($leaves as $leave)
        <tr>
            <td>
                <div class="font-bold">{{ $leave->user->name ?? 'N/A' }}</div>
                <div style="font-size: 10px; color: #666;">{{ $leave->user->email ?? '' }}</div>
            </td>
            <td>
                <span class="badge badge-orange">{{ $leave->leaveType->name ?? 'Unknown' }}</span>
            </td>
            <td>
                {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
            </td>
            <td class="text-center">{{ floatval($leave->total_days) }}</td>
            <td class="text-center" style="color: #166534;">{{ floatval($leave->paid_days) }}</td>
            <td class="text-center font-bold" style="color: #991b1b;">{{ floatval($leave->lop_days) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

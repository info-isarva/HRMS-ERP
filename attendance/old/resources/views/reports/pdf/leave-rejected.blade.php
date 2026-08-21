@extends('reports.pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Period</th>
            <th class="text-center">Days</th>
            <th>Rejection Reason</th>
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
                <span class="badge badge-red">{{ $leave->leaveType->name ?? 'Unknown' }}</span>
            </td>
            <td>
                {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
            </td>
            <td class="text-center font-bold">
                {{ $leave->total_days }}
            </td>
            <td>
                <div style="font-size: 10px;">{{ $leave->rejection_reason }}</div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

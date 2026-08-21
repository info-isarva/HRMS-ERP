@extends('reports.pdf.layout')

@section('content')
<div class="filters">
    <strong>Employee:</strong> {{ $selectedUser ? $selectedUser->name . ' (' . $selectedUser->email . ')' : 'All Employees' }}<br>
    <strong>Year:</strong> {{ $year }}
</div>

<table>
    <thead>
        <tr>
            @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $month)
                <th class="text-center">{{ $month }}</th>
            @endforeach
            <th class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach($monthlyData as $days)
                <td class="text-center">
                    {{ $days }}
                </td>
            @endforeach
            <td class="text-center font-bold">
                {{ array_sum($monthlyData) }}
            </td>
        </tr>
    </tbody>
</table>

@if($selectedUser)
<div style="margin-top: 30px;">
    <h3>Employee Summary</h3>
    <table style="width: 50%;">
        <tr>
            <td class="font-bold">Name:</td>
            <td>{{ $selectedUser->name }}</td>
        </tr>
        <tr>
            <td class="font-bold">Email:</td>
            <td>{{ $selectedUser->email }}</td>
        </tr>
        <tr>
            <td class="font-bold">Total Leaves in {{ $year }}:</td>
            <td>{{ array_sum($monthlyData) }} days</td>
        </tr>
    </table>
</div>
@endif
@endsection

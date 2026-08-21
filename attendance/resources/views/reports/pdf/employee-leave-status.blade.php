@extends('reports.pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th class="text-center">Available Balance</th>
            <th class="text-center">Total Taken</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $data)
        <tr>
            <td>
                <div class="font-bold">{{ $data['user']->name }}</div>
                <div style="font-size: 10px; color: #666;">{{ $data['user']->email }}</div>
            </td>
            <td class="text-center">
                <span class="badge {{ $data['available_leave'] > 0 ? 'badge-green' : 'badge-red' }}">
                    {{ floatval($data['available_leave']) }}
                </span>
            </td>
            <td class="text-center">
                <span class="badge badge-gray">
                    {{ floatval($data['leave_taken']) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

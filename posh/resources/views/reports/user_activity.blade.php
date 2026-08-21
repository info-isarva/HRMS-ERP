@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="card mt-0">
        <div class="card-header">
            <h4>User Activity Report</h4>
        </div>
        <div class="card-body">
            @if($activities->isEmpty())
                <p>No activities found for this project.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td>{{ $activity['type'] }}</td>
                                <td>{{ $activity['title'] }}</td>
                                <td>{{ $activity['created_at']->format('d-m-Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
@extends('layouts.settings')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Salary Structure Configuration</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Salary Structure</li>
                        </ul>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">CTC Calculation Rules</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('salary.structure.save') }}" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Component</th>
                                                <th>Type</th>
                                                <th>Calculation</th>
                                                <th>Value</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($salaryComponents as $index => $component)
                                                @php
                                                    $config = $configs[$component->id] ?? null;
                                                    $calcType = $config->calculation_type ?? 'percentage';
                                                    $percentageOf = $config->percentage_of ?? 'ctc';
                                                    $value = $config->value ?? 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $component->name }} ({{ $component->short_name }})
                                                        <input type="hidden" name="configs[{{ $index }}][salary_component_id]" value="{{ $component->id }}">
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-inverse-{{ $component->type == 'earning' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($component->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <select class="form-select calc-type" name="configs[{{ $index }}][calculation_type]" data-index="{{ $index }}">
                                                                    <option value="percentage" {{ $calcType == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                                    <option value="fixed" {{ $calcType == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 base-select-wrapper" style="{{ $calcType == 'fixed' ? 'display:none;' : '' }}">
                                                                <select class="form-select" name="configs[{{ $index }}][percentage_of]">
                                                                    <option value="ctc" {{ $percentageOf == 'ctc' ? 'selected' : '' }}>of CTC</option>
                                                                    <option value="basic" {{ $percentageOf == 'basic' ? 'selected' : '' }}>of Basic</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" class="form-control" name="configs[{{ $index }}][value]" value="{{ $value }}" required>
                                                            <span class="input-group-text value-symbol">{{ $calcType == 'percentage' ? '%' : '₹' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="status-toggle">
                                                            <input type="checkbox" id="status_{{ $index }}" class="check" checked disabled>
                                                            <label for="status_{{ $index }}" class="checktoggle">checkbox</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn">Save Configuration</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('script')
    <script>
        $(document).ready(function() {
            $('.calc-type').change(function() {
                var index = $(this).data('index');
                var type = $(this).val();
                var row = $(this).closest('tr');
                
                if (type === 'percentage') {
                    row.find('.base-select-wrapper').show();
                    row.find('.value-symbol').text('%');
                } else {
                    row.find('.base-select-wrapper').hide();
                    row.find('.value-symbol').text('₹');
                }
            });
        });
    </script>
    @endsection
@endsection

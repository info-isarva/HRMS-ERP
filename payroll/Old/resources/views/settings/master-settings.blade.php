@extends('layouts.master')
@section('title', 'Master Settings')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">System Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @foreach($settingGroups as $group => $settings)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ ucfirst($group) }} Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($settings as $setting)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="setting-{{ $setting->key }}">
                                                            {{ $setting->display_name }}
                                                            @if($setting->description)
                                                                <i class="fa fa-info-circle" data-toggle="tooltip" title="{{ $setting->description }}"></i>
                                                            @endif
                                                        </label>

                                                        @if($setting->key === 'active_currency')
                                                            <select class="form-control select" name="settings[{{ $setting->key }}]" id="setting-{{ $setting->key }}">
                                                                @foreach(\App\Helper\CurrencyHelper::getCurrencies() as $code => $currency)
                                                                    <option value="{{ $code }}" {{ $setting->value === $code ? 'selected' : '' }}>
                                                                        {{ $code }} - {{ $currency['name'] }} ({{ $currency['symbol'] }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($setting->type === 'boolean')
                                                            <div class="form-check form-switch">
                                                                <!-- Hidden input to ensure false value is sent when checkbox is unchecked -->
                                                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                                <input type="checkbox" 
                                                                    class="form-check-input" 
                                                                    id="setting-{{ $setting->key }}" 
                                                                    name="settings[{{ $setting->key }}]" 
                                                                    value="1"
                                                                    {{ $setting->value === 'true' ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="setting-{{ $setting->key }}">
                                                                    {{ $setting->value === 'true' ? 'Enabled' : 'Disabled' }}
                                                                </label>
                                                                @if($setting->key === 'enable_self_portal')
                                                                    <div class="text-info mt-1">
                                                                        <small><i class="fa fa-info-circle"></i> This setting controls the default value for "Enable Self Portal" when creating new employees. Individual employees can still override this setting.</small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @elseif($setting->type === 'text')
                                                            <input type="text" 
                                                                class="form-control" 
                                                                id="setting-{{ $setting->key }}" 
                                                                name="settings[{{ $setting->key }}]" 
                                                                value="{{ $setting->value }}">
                                                        @elseif($setting->type === 'number')
                                                            <input type="number" 
                                                                class="form-control" 
                                                                id="setting-{{ $setting->key }}" 
                                                                name="settings[{{ $setting->key }}]" 
                                                                value="{{ $setting->value }}">
                                                        @elseif($setting->type === 'json')
                                                            <textarea 
                                                                class="form-control" 
                                                                id="setting-{{ $setting->key }}" 
                                                                name="settings[{{ $setting->key }}]" 
                                                                rows="5">{{ $setting->value }}</textarea>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        // Update switch label on change
        $('.form-check-input').change(function() {
            const label = $(this).siblings('.form-check-label');
            if ($(this).is(':checked')) {
                label.text('Enabled');
            } else {
                label.text('Disabled');
            }
        });
    });
</script>
@endpush

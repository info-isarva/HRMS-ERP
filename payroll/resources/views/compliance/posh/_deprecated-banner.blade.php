@if(config('posh.legacy_enabled'))
<div class="alert alert-warning border-0 shadow-sm mb-4" role="alert" style="border-left: 4px solid #d4622a !important;">
    <strong><i class="fas fa-triangle-exclamation"></i> Legacy POSH (deprecated)</strong>
    This basic module will be removed. Use <a href="{{ config('posh.workspace_url') }}{{ config('posh.coming_soon_path') }}" target="_blank" rel="noopener"><strong>{{ config('posh.product_name') }}</strong></a> from the HRMS workspace when Phase 1 is live. Do not file new complaints here unless exporting old data.
</div>
@endif

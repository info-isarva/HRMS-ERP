@extends('central.demo-tenants.layout')

@section('title', 'Create demo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="dtm-panel dtm-form">
            <div class="dtm-panel__head">
                <div>
                    <h1 class="dtm-panel__title">Provision new demo client</h1>
                    <p class="dtm-panel__sub mb-0">One form creates databases, migrations, and super admin login</p>
                </div>
            </div>
            <div class="dtm-panel__body">
                <form method="POST" action="{{ route('platform.demo-tenants.store') }}">
                    @csrf

                    <div class="section-label">Client details</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Company / client name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Acme Industries Pvt Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company code *</label>
                            <input type="text" name="company_code" class="form-control text-uppercase" value="{{ old('company_code') }}" required maxlength="32" pattern="[A-Za-z0-9]+" placeholder="ACME2026">
                            <div class="form-hint">Letters & numbers only — client enters this at login</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact person</label>
                            <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}" placeholder="Mr. Sharma">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Demo duration</label>
                            <select name="demo_days" class="form-select">
                                @foreach([10, 15, 30] as $days)
                                    <option value="{{ $days }}" @selected(old('demo_days', $defaultDays) == $days)>{{ $days }} days</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Or fixed expiry date</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}" min="{{ now()->addDay()->toDateString() }}">
                        </div>
                    </div>

                    <div class="section-label">Super admin login (always created)</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Super admin email *</label>
                            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required placeholder="admin@client.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display name</label>
                            <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" placeholder="Client Super Admin">
                        </div>
                    </div>
                    <p class="form-hint mb-4" style="margin-top:-.5rem">
                        <i class="fas fa-circle-info me-1"></i>
                        A secure password is auto-generated and saved — you can copy and share it from the demo detail page.
                    </p>

                    <div class="section-label">Initial data setup *</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="option-card">
                                <input type="radio" name="seed_profile" value="none" @checked(old('seed_profile', 'none') === 'none')>
                                <div class="option-card__inner">
                                    <div class="option-card__title"><i class="fas fa-user-shield"></i> Super admin only</div>
                                    <div class="option-card__desc">
                                        Creates super admin in Workspace, Payroll & Attendance. Client sets up company, departments, and employees themselves.
                                    </div>
                                    <span class="option-card__tag">Recommended for hands-on trials</span>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="option-card">
                                <input type="radio" name="seed_profile" value="standard" @checked(old('seed_profile') === 'standard')>
                                <div class="option-card__inner">
                                    <div class="option-card__title"><i class="fas fa-database"></i> Super admin + sample data</div>
                                    <div class="option-card__desc">
                                        Everything above, plus company profile, 3 departments, 3 sample employees, and one attendance record.
                                    </div>
                                    <span class="option-card__tag">Good for quick product tour</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="section-label">Internal notes</div>
                    <textarea name="internal_notes" class="form-control mb-4" rows="3" placeholder="Google Meet date, sales owner, follow-up notes…">{{ old('internal_notes') }}</textarea>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3 border-top">
                        <div class="form-hint mb-0">
                            <i class="fas fa-link me-1"></i>
                            Same URLs for all clients — only <strong>company code</strong> changes at login.
                        </div>
                        <button type="submit" class="dtm-btn dtm-btn--primary" style="padding:.65rem 1.25rem">
                            <i class="fas fa-bolt"></i> Create demo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Company Settings</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div id="company-success-alert" class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @php $canEdit = $canEdit ?? false; @endphp
                    @unless($canEdit)
                        <div class="alert alert-info">You have view-only access to company settings. Only Admin and Super Admin can edit.</div>
                    @endunless
                    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                        @csrf

                        <ul class="nav nav-tabs" id="companySettingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Company Details</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab" aria-controls="financial" aria-selected="false">Financial Year</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" type="button" role="tab" aria-controls="currency" aria-selected="false">Currency Settings</button>
                            </li>
                            <!-- <li class="nav-item" role="presentation">
                                <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false">Backup Data</button>
                            </li> -->
                        </ul>

                        <div class="tab-content p-3 border border-top-0" id="companySettingsTabContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name ?? '') }}" @if(!$canEdit) disabled @endif>
                                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Mobile Number</label>
                                                <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $company->mobile ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('mobile')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Email ID</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $company->email ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Website URL</label>
                                                <input type="url" name="website" class="form-control" value="{{ old('website', $company->website ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('website')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control" value="{{ old('address', $company->address ?? '') }}" @if(!$canEdit) disabled @endif>
                                            @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">City</label>
                                                <input type="text" name="city" class="form-control" value="{{ old('city', $company->city ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Pincode</label>
                                                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $company->pincode ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('pincode')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone ?? '') }}" @if(!$canEdit) disabled @endif>
                                                @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Logo</label>
                                            <div class="mt-3">
                                                @if(!empty($company->logo))
                                                    <img src="{{ asset('assets/company_image/' . $company->logo) }}" alt="Company Logo" style="height:60px;max-width:100%;object-fit:contain;" class="img-fluid mb-2">
                                                @endif
                                            </div>
                                            @if($canEdit)
                                                <input type="file" name="logo" class="form-control mb-2">
                                                @error('logo')<div class="text-danger small">{{ $message }}</div>@enderror
                                               
                                            @else
                                                <div class="small text-muted">Logo  upload disabled for your account.</div>
                                            @endif   
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label mt-2">Favicon</label>
                                            <div class="mb-2">
                                                @if(!empty($company->favicon))
                                                    <img src="{{ asset('assets/company_image/' . $company->favicon) }}" alt="Favicon" style="height:32px;width:32px;object-fit:contain;" class="img-fluid mb-2">
                                                @endif
                                            </div>

                                             
                                                @if($canEdit)
                                                    <input type="file" name="favicon" class="form-control">
                                                    @error('favicon')<div class="text-danger small">{{ $message }}</div>@enderror
                                                @else
                                                    <div class="small text-muted">Favicon upload disabled for your account.</div>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="financial" role="tabpanel" aria-labelledby="financial-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Financial Year Start</label>
                                        <div class="d-flex gap-2">
                                            <select name="fy_start_month" class="form-select" @if(!$canEdit) disabled @endif>
                                                <option value="">Month</option>
                                                @foreach([1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"] as $m => $label)
                                                    <option value="{{ $m }}" {{ old('fy_start_month', $company->fy_start_month ?? '') == $m ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select name="fy_start_day" class="form-select" @if(!$canEdit) disabled @endif>
                                                <option value="">Day</option>
                                                @for($d=1;$d<=31;$d++)
                                                    <option value="{{ $d }}" {{ old('fy_start_day', $company->fy_start_day ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="small text-muted mt-2">Example: April 1 means fiscal year runs from Apr 1 to next year Mar 31.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Financial Year End</label>
                                        <div class="d-flex gap-2">
                                            <select name="fy_end_month" class="form-select" @if(!$canEdit) disabled @endif>
                                                <option value="">Month</option>
                                                @foreach([1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"] as $m => $label)
                                                    <option value="{{ $m }}" {{ old('fy_end_month', $company->fy_end_month ?? '') == $m ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select name="fy_end_day" class="form-select" @if(!$canEdit) disabled @endif>
                                                <option value="">Day</option>
                                                @for($d=1;$d<=31;$d++)
                                                    <option value="{{ $d }}" {{ old('fy_end_day', $company->fy_end_day ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="small text-muted mt-2">Example: Mar 31 (if FY starts Apr 1).</div>
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="currency" role="tabpanel" aria-labelledby="currency-tab">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <select id="company-country" name="country" class="form-select" @if(!$canEdit) disabled @endif>
                                            <option value="">Select country</option>
                                            @php
                                                $countries = [
                                                    'United States' => ['code'=>'USD','symbol'=>'$'],
                                                    'India' => ['code'=>'INR','symbol'=>'₹'],
                                                    'United Kingdom' => ['code'=>'GBP','symbol'=>'£'],
                                                    'European Union' => ['code'=>'EUR','symbol'=>'€'],
                                                    'Japan' => ['code'=>'JPY','symbol'=>'¥'],
                                                ];
                                            @endphp
                                            @foreach($countries as $cName => $cData)
                                                <option value="{{ $cName }}" data-code="{{ $cData['code'] }}" data-symbol="{{ $cData['symbol'] }}" {{ old('country', $company->country ?? '') == $cName ? 'selected' : '' }}>{{ $cName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Currency Code</label>
                                        <input id="currency_code" type="text" name="currency_code" class="form-control" value="{{ old('currency_code', $company->currency_code ?? '') }}" @if(!$canEdit) disabled @endif>
                                        @error('currency_code')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">Currency Symbol</label>
                                        <input id="currency_symbol" type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $company->currency_symbol ?? '') }}" @if(!$canEdit) disabled @endif>
                                        @error('currency_symbol')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-1 mb-3">
                                        <label class="form-label">Pos</label>
                                        <select name="currency_position" class="form-select" @if(!$canEdit) disabled @endif>
                                            <option value="prefix" {{ old('currency_position', $company->currency_position ?? 'prefix') == 'prefix' ? 'selected' : '' }}>Pre</option>
                                            <option value="suffix" {{ old('currency_position', $company->currency_position ?? '') == 'suffix' ? 'selected' : '' }}>Suf</option>
                                        </select>
                                    </div>
                                </div>
                               
                            </div>
                            <!-- <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                                <div id="backup-content">
                                    <div class="mt-3">
                                        @if($canEdit)
                                            <p>Download a backup of the application database. This backup includes all data, routines, triggers, and events.</p>
                                            <a href="{{ route('backup') }}" class="btn btn-success">
                                                <i class="fa fa-database"></i> Download Database Backup
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div> -->
                        </div>
                       <div class="mt-3">
                            @if($canEdit)
                                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Save</button>
                            @endif
                        </div>
                        
                    </form>
<!-- @if($canEdit)
                                            <div class="mt-3">
                                                <form method="POST" action="{{ route('company.close_financial_year') }}" onsubmit="return confirm('This will take a full database backup before closing the financial year. Proceed?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning">Close Financial Year (backup first)</button>
                                                </form>
                                            </div>
                                        @endif -->
                   
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function setSaveButtonsVisible(visible) {
        document.querySelectorAll('.company-save-btn').forEach(function(btn){
            btn.style.display = visible ? '' : 'none';
        });
    }

    var tabContainer = document.getElementById('companySettingsTabs');
    if(tabContainer) {
        tabContainer.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tab){
            tab.addEventListener('shown.bs.tab', function(e){
                var target = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
                if(target === '#backup') {
                    setSaveButtonsVisible(false);
                } else {
                    setSaveButtonsVisible(true);
                }
            });
        });

        // initial state
        var active = tabContainer.querySelector('button.nav-link.active');
        if(active && (active.getAttribute('data-bs-target') === '#backup')) {
            setSaveButtonsVisible(false);
        }
    }
});
</script>
<script src="{{asset('js/our-company.js')}}"></script>
@endsection

<div class="p-3">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Employee ID</label>
                <input type="text" name="basic[employee_id]" class="form-control @error('basic.employee_id') is-invalid @enderror" value="{{ old('basic.employee_id') }}">
                @error('basic.employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Employee Name</label>
                <input type="text" name="basic[name]" class="form-control @error('basic.name') is-invalid @enderror" value="{{ old('basic.name') }}">
                @error('basic.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="basic[email]" class="form-control @error('basic.email') is-invalid @enderror" value="{{ old('basic.email') }}">
                @error('basic.email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="basic[contact_number]" class="form-control @error('basic.contact_number') is-invalid @enderror" value="{{ old('basic.contact_number') }}">
                @error('basic.contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Date Of Birth</label>
                <input type="date" name="basic[date_of_birth]" class="form-control @error('basic.date_of_birth') is-invalid @enderror" value="{{ old('basic.date_of_birth') }}">
                @error('basic.date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>        
        <div class="col-md-3">
            <div class="form-group">
                <label>Gender</label>
                <select name="basic[gender]" class="form-control @error('basic.gender') is-invalid @enderror">
                    @foreach($genders as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.gender') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Marital Status</label>
                <select name="basic[marital_status]" class="form-control @error('basic.marital_status') is-invalid @enderror">
                    @foreach($maritalStatuses as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.marital_status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Designation</label>
                <select name="basic[designation]" class="form-control @error('basic.designation') is-invalid @enderror">
                    @foreach($designations as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.designation') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Department</label>
                <select name="basic[department]" class="form-control @error('basic.department') is-invalid @enderror">
                    @foreach($departments as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.department') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.department')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Date Of Joining</label>
                <input type="date" name="basic[date_of_joining]" class="form-control @error('basic.date_of_joining') is-invalid @enderror" value="{{ old('basic.date_of_joining') }}">
                @error('basic.date_of_joining')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Role</label>
                <select name="basic[role]" class="form-control @error('basic.role') is-invalid @enderror">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.role' , 2 ) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>  
        <div class="col-md-3">
            <div class="form-group">
                <label>Status</label>
                <select name="basic[status]" class="form-control @error('basic.status') is-invalid @enderror">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ old('basic.status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('basic.status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>  
        
    </div>
</div>
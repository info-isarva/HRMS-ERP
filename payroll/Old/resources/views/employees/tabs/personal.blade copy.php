<div class="p-3">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Address</label>
                <textarea name="personal[address]" class="form-control @error('personal.address') is-invalid @enderror" value="{{ old('personal.address') }}"></textarea>
                @error('personal.address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Father Name</label>
                <input type="text" name="personal[father_name]" class="form-control @error('personal.father_name') is-invalid @enderror" value="{{ old('personal.father_name') }}">
                @error('personal.father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Mother Name</label>
                <input type="text" name="personal[mother_name]" class="form-control @error('personal.mother_name') is-invalid @enderror" value="{{ old('personal.mother_name') }}">
                @error('personal.mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Blood Group</label>
                <select name="personal[blood_group]" class="form-control @error('personal.blood_group') is-invalid @enderror">
                    @foreach($bloodGroups as $value => $label)
                        <option value="{{ $value }}" {{ old('personal.blood_group') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('personal.blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Aadhaar Number</label>
                <input type="text" name="personal[aadhaar_number]" class="form-control @error('personal.aadhaar_number') is-invalid @enderror" value="{{ old('personal.aadhaar_number') }}">
                @error('personal.aadhaar_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Pan Number</label>
                <input type="text" name="personal[pan_number]" class="form-control @error('personal.pan_number') is-invalid @enderror" value="{{ old('personal.pan_number') }}">
                @error('personal.pan_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>PF Account Number</label>
                <input type="text" name="personal[pf_account_number]" class="form-control @error('personal.pf_account_number') is-invalid @enderror" value="{{ old('personal.pf_account_number') }}">
                @error('personal.pf_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>ESIC Number</label>
                <input type="text" name="personal[esic_number]" class="form-control @error('personal.esic_number') is-invalid @enderror" value="{{ old('personal.esic_number') }}">
                @error('personal.esic_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Upload Documents</label>
                <input type="text" name="personal[uploaded_documents]" class="form-control @error('personal.uploaded_documents') is-invalid @enderror" value="{{ old('personal.uploaded_documents') }}">
                @error('personal.uploaded_documents')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
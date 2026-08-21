<div class="p-3">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Payment Mode</label>
                <select name="bank[type_of_payment]" class="form-control @error('bank.type_of_payment') is-invalid @enderror">
                    @foreach($paymentTypes as $value => $label)
                        <option value="{{ $value }}" {{ old('bank.type_of_payment') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('bank.type_of_payment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Bank Name</label>
                <input type="text" name="bank[bank_name]" class="form-control @error('bank.bank_name') is-invalid @enderror" value="{{ old('bank.bank_name') }}">
                @error('bank.bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Account Number</label>
                <input type="text" name="bank[account_number]" class="form-control @error('bank.account_number') is-invalid @enderror" value="{{ old('bank.account_number') }}">
                @error('bank.account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>IFSC Code</label>
                <input type="text" name="bank[ifsc_code]" class="form-control @error('bank.ifsc_code') is-invalid @enderror" value="{{ old('bank.ifsc_code') }}">
                @error('bank.ifsc_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Branch</label>
                <input type="text" name="bank[branch]" class="form-control @error('bank.branch') is-invalid @enderror" value="{{ old('bank.branch') }}">
                @error('bank.branch')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
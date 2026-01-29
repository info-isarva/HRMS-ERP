<div class="p-3">

    <div class="row">

        <div class="col-md-3">

            <div class="form-group">

                <label>Payment Mode</label>

                <select name="bank[type_of_payment]" id="payment_mode" class="form-control form-select @error('bank.type_of_payment') is-invalid @enderror" onchange="toggleBankFields()">

                    @foreach($paymentTypes as $value => $label)

                    <option 

                        value="{{ $value }}" {{ old('bank.type_of_payment', isset($employee) ? ($employee->bankDetail->type_of_payment ?? '') : '') == $value ? 'selected' : '' }}>

                        {{ $label }}

                    </option>



                    @endforeach

                </select>

                @error('bank.type_of_payment')<div class="invalid-feedback">{{ $message }}</div>@enderror

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label>Bank Name <span class="text-danger bank-required" style="display: none;">*</span></label>

                <input type="text" name="bank[bank_name]" id="bank_name" class="form-control @error('bank.bank_name') is-invalid @enderror" 

                    value="{{ old('bank.bank_name',  $employee->bankDetail->bank_name ?? '') }}">

                @error('bank.bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">
                <label>Account Number <span class="text-danger bank-required" style="display: none;">*</span></label>
                <input type="text" name="bank[account_number]" id="account_number" class="form-control @error('bank.account_number') is-invalid @enderror" 
                    value="{{ old('bank.account_number', $employee->bankDetail->account_number ?? '') }}"
                    pattern="[0-9]{9,18}" maxlength="18"
                    title="Enter a valid account number (9-18 digits)"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('bank.account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Confirm Account Number <span class="text-danger bank-required" style="display: none;">*</span></label>
                <input type="text" id="confirm_account_number" class="form-control" 
                    value="{{ old('bank.account_number', $employee->bankDetail->account_number ?? '') }}"
                    pattern="[0-9]{9,18}" maxlength="18"
                    title="Account numbers must match"
                    oninput="this.value = this.value.replace(/[^0-9]/g, ''); validateAccountNumberMatch();">
                <div class="invalid-feedback" id="confirm_account_error">
                    Account numbers do not match.
                </div>
            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">
                <label>IFSC Code <span class="text-danger bank-required" style="display: none;">*</span></label>
                <input type="text" name="bank[ifsc_code]" id="ifsc_code" class="form-control @error('bank.ifsc_code') is-invalid @enderror" 
                    value="{{ old('bank.ifsc_code', $employee->bankDetail->ifsc_code ?? '') }}"
                    pattern="^[A-Z]{4}0[A-Z0-9]{6}$" maxlength="11"
                    title="Enter a valid IFSC Code (e.g., SBIN0123456)"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                @error('bank.ifsc_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label>Branch <span class="text-danger bank-required" style="display: none;">*</span></label>

                <input type="text" name="bank[branch]" id="branch" class="form-control @error('bank.branch') is-invalid @enderror" 

                    value="{{ old('bank.branch', $employee->bankDetail->branch ?? '') }}">

                @error('bank.branch')<div class="invalid-feedback">{{ $message }}</div>@enderror

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label>Transaction Type <span class="text-danger bank-required" style="display: none;">*</span></label>

                <select name="bank[transaction_type]" id="transaction_type" class="form-control form-select @error('bank.transaction_type') is-invalid @enderror">

                    @foreach($transaction_types as $value => $label)

                        <option value="{{ $value }}" 

                            {{ old('bank.transaction_type' , $employee->bankDetail->transaction_type ?? '' ) == $value ? 'selected' : '' }}>

                            {{ $label }}

                        </option>

                    @endforeach

                </select>

                @error('bank.transaction_type')<div class="invalid-feedback">{{ $message }}</div>@enderror

            </div>

        </div>  

    </div>

</div>

<script>
    function toggleBankFields() {
        const paymentMode = document.getElementById('payment_mode');
        const requiredSpans = document.querySelectorAll('.bank-required');
        const bankName = document.getElementById('bank_name');
        const accountNumber = document.getElementById('account_number');
        const confirmAccountNumber = document.getElementById('confirm_account_number');
        const ifscCode = document.getElementById('ifsc_code');
        const branch = document.getElementById('branch');
        const transactionType = document.getElementById('transaction_type');
        
        // Check if payment mode is '1' (Bank transfer)
        const isBankPayment = paymentMode.value === '1';
        
        // Show/hide asterisks and set required attributes
        requiredSpans.forEach(span => {
            span.style.display = isBankPayment ? '' : 'none';
        });
        
        // Set or remove required attribute
        if (isBankPayment) {
            bankName.setAttribute('required', 'required');
            accountNumber.setAttribute('required', 'required');
            confirmAccountNumber.setAttribute('required', 'required');
            ifscCode.setAttribute('required', 'required');
            branch.setAttribute('required', 'required');
            transactionType.setAttribute('required', 'required');
        } else {
            bankName.removeAttribute('required');
            accountNumber.removeAttribute('required');
            confirmAccountNumber.removeAttribute('required');
            ifscCode.removeAttribute('required');
            branch.removeAttribute('required');
            transactionType.removeAttribute('required');
        }
    }
    
    function validateAccountNumberMatch() {
        const accountNumber = document.getElementById('account_number');
        const confirmAccountNumber = document.getElementById('confirm_account_number');
        const errorDiv = document.getElementById('confirm_account_error');
        
        if (confirmAccountNumber.value && accountNumber.value !== confirmAccountNumber.value) {
            confirmAccountNumber.classList.add('is-invalid');
            confirmAccountNumber.setCustomValidity('Account numbers do not match');
            if (errorDiv) errorDiv.style.display = 'block';
        } else {
            confirmAccountNumber.classList.remove('is-invalid');
            confirmAccountNumber.setCustomValidity('');
            if (errorDiv) errorDiv.style.display = 'none';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleBankFields();
        
        // Add listener to origin account number to re-validate on change
        document.getElementById('account_number').addEventListener('input', validateAccountNumberMatch);
    });
</script>
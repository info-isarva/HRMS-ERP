<div class="p-3">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Permanent Address </label>
                <textarea name="personal[address]" 
                    class="form-control @error('personal.address') is-invalid @enderror" required>{{ old('personal.address', isset($employee) ? $employee->personalDetail->address : '') }} </textarea>
                @error('personal.address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Temporary Address </label>
                <textarea name="personal[temporary_address]" 
                    class="form-control @error('personal.temporary_address') is-invalid @enderror" required>{{ old('personal.temporary_address', isset($employee) ? $employee->personalDetail->temporary_address : '') }} </textarea>
                @error('personal.temporary_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        
        
        <div class="col-md-3">
            <div class="form-group">
                <label>Father Name</label>
                <input type="text" name="personal[father_name]" 
                    value="{{ old('personal.father_name', $employee->personalDetail->father_name ?? '') }}" 
                    class="form-control @error('personal.father_name') is-invalid @enderror">
                @error('personal.father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        
        <div class="col-md-3">
            <div class="form-group">
                <label>Mother Name</label>
                <input type="text" name="personal[mother_name]" class="form-control @error('personal.mother_name') is-invalid @enderror" 
                    value="{{ old('personal.mother_name', $employee->personalDetail->mother_name ?? '') }}">
                @error('personal.mother_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Blood Group</label>
                <select name="personal[blood_group]" class="form-control @error('personal.blood_group') is-invalid @enderror">
                    <option value="">Select Blood Group</option>
                    @foreach($bloodGroups as $value => $label)
                        <option value="{{ $value }}" {{ old('personal.blood_group', $employee->personalDetail->blood_group ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('personal.blood_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Aadhaar Number</label>
                <input type="text" name="personal[aadhaar_number]" class="form-control @error('personal.aadhaar_number') is-invalid @enderror" 
                    value="{{ old('personal.aadhaar_number', $employee->personalDetail->aadhaar_number ?? '') }}"
                    pattern="[0-9]{12}" maxlength="12"
                    title="Aadhaar number must be exactly 12 digits"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('personal.aadhaar_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Pan Number</label>
                <input type="text" name="personal[pan_number]" class="form-control @error('personal.pan_number') is-invalid @enderror" 
                    value="{{ old('personal.pan_number', $employee->personalDetail->pan_number ?? '') }}"
                    pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10"
                    title="PAN Number must be valid (e.g. ABCDE1234F)"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                @error('personal.pan_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>PF Account Number</label>
                <input type="text" name="personal[pf_account_number]" class="form-control @error('personal.pf_account_number') is-invalid @enderror" 
                    value="{{ old('personal.pf_account_number', $employee->personalDetail->pf_account_number ?? '') }}"
                    pattern="[A-Z0-9]+" 
                    title="PF Account Number must be alphanumeric"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                @error('personal.pf_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>ESIC Number</label>
                <input type="text" name="personal[esic_number]" class="form-control @error('personal.esic_number') is-invalid @enderror" 
                    value="{{ old('personal.esic_number', $employee->personalDetail->esic_number ?? '') }}"
                    pattern="[0-9]{17}" maxlength="17"
                    title="ESIC Number must be exactly 17 digits"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                @error('personal.esic_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Emergency Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Emergency Contact Person Name</label>
                                <input type="text" name="personal[emergency_contact_name]" class="form-control @error('personal.emergency_contact_name') is-invalid @enderror" 
                                    value="{{ old('personal.emergency_contact_name', $employee->personalDetail->emergency_contact_name ?? '') }}">
                                @error('personal.emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Emergency Contact Person Contact Number</label>
                                <input type="text" name="personal[emergency_contact_number]" class="form-control @error('personal.emergency_contact_number') is-invalid @enderror" 
                                    value="{{ old('personal.emergency_contact_number', $employee->personalDetail->emergency_contact_number ?? '') }}"
                                    pattern="^\+?[0-9]{10,15}$"
                                    title="Enter a valid phone number: +911234567890 or 9876543210"
                                    oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                                <div class="invalid-feedback">
                                    @error('personal.emergency_contact_number') {{ $message }} @else Enter a valid phone number: +911234567890 or 9876543210 @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Document Upload Section -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Documents</h5>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Max file size: 5MB per document | Allowed formats: PDF, JPG, PNG, DOC, DOCX
                    </small>
                </div>
                <div class="card-body">
                    <!-- Display existing documents if editing -->
                   <?php // print_r($employee->employeeDocument); exit(); 
                   ?>
                    @if(isset($employee) && $employee->employeeDocument->count() > 0)
                        <div class="mb-3">
                            <h6>Current Documents</h6>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>File Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employee->employeeDocument as $document)
                                        <tr>
                                            <td>{{ getDocumentTypeLabel($document->document_id) }}</td>
                                            <td>{{ $document->name }}</td>
                                            <td>
                                                <a href="{{ asset($document->uploaded_document) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger delete-document-btn" 
                                                        data-document-id="{{ $document->id }}"
                                                        data-delete-url="{{ route('employees.document.delete', $document->id) }}"
                                                        onclick="deleteEmployeeDocument({{ $document->id }}, '{{ route('employees.document.delete', $document->id) }}', this)">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Add new documents section -->
                    <div id="documentUploads">
                        @if(old('personal.uploaded_document'))
                            @foreach(old('personal.uploaded_document') as $index => $document)
                                <div class="document-entry mb-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <input type="file" 
                                                name="personal[uploaded_document][{{ $index }}][file]"
                                                class="form-control @error("personal.uploaded_document.{$index}.file") is-invalid @enderror">
                                            @error("personal.uploaded_document.{$index}.file")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <select name="personal[uploaded_document][{{ $index }}][type]" 
                                                    class="form-control @error("personal.uploaded_document.{$index}.type") is-invalid @enderror">
                                                <option value="">Select Document Type</option>
                                                @foreach($documentTypes as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error("personal.uploaded_document.{$index}.type")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger" onclick="removeDocument(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach                        
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" onclick="addDocument()">
                        <i class="fa fa-plus"></i> Add Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<template id="documentTemplate">
    <div class="document-entry mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <select name="personal[uploaded_document][__INDEX__][type]" class="form-control form-select" required>
                    <option value="">Select Document Type</option>
                    @foreach($documentTypes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="file" 
                       name="personal[uploaded_document][__INDEX__][file]"
                       class="form-control" 
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       onchange="validateFileSize(this)"
                       required>
                <!-- <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle"></i> Max size: 5MB | Formats: PDF, JPG, PNG, DOC, DOCX
                </small> -->
            </div>
            
            <div class="col-md-2">
                <button type="button" class="btn btn-danger" onclick="removeDocument(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    // Inline function for document deletion with SweetAlert
    function deleteEmployeeDocument(documentId, deleteUrl, buttonElement) {
        // Check if SweetAlert is available, otherwise use native confirm
        if (typeof Swal === 'undefined') {
            if (!confirm('Are you sure you want to delete this document?')) {
                return false;
            }
            proceedWithDeletion(deleteUrl, buttonElement);
            return false;
        }
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                proceedWithDeletion(deleteUrl, buttonElement);
            }
        });
        
        return false;
    }
    
    // Separate function to handle the actual deletion
    function proceedWithDeletion(deleteUrl, buttonElement) {
        // Disable button to prevent double-click
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
        
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message || 'Document has been deleted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.success(data.message || 'Document deleted successfully');
                } else {
                    alert(data.message || 'Document deleted successfully');
                }
                
                // Remove the table row with animation
                const row = buttonElement.closest('tr');
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    
                    // Check if table is empty
                    const tbody = document.querySelector('.table tbody');
                    if (tbody && tbody.children.length === 0) {
                        const tableContainer = tbody.closest('.mb-3');
                        if (tableContainer) {
                            tableContainer.remove();
                        }
                    }
                }, 300);
            } else {
                // Re-enable button on error
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fa fa-trash"></i> Delete';
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to delete document',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Failed to delete document');
                } else {
                    alert(data.message || 'Failed to delete document');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Re-enable button on error
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="fa fa-trash"></i> Delete';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the document',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr.error('An error occurred while deleting the document');
            } else {
                alert('An error occurred while deleting the document');
            }
        });
    }

    function addDocument() {
        const template = document.getElementById('documentTemplate');
        const container = document.getElementById('documentUploads');
        const index = container.querySelectorAll('.document-entry').length;
        
        // Clone template with proper index handling
        const clone = document.importNode(template.content, true);
        
        // Update all elements in the clone using vanilla JS
        clone.querySelectorAll('[name]').forEach(element => {
            element.name = element.name.replace('__INDEX__', index);
        });
        
        container.appendChild(clone);
    }

    function removeDocument(button) {
        const entry = button.closest('.document-entry');
        if (entry) {
            entry.remove();
        }
    }

    // Immediate file size validation when file is selected
    function validateFileSize(input) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const file = input.files[0];
        
        // Remove any existing error messages
        const existingError = input.parentElement.querySelector('.file-size-error');
        if (existingError) {
            existingError.remove();
        }
        input.classList.remove('is-invalid');
        
        if (file && file.size > maxSize) {
            input.classList.add('is-invalid');
            
            // Create error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block file-size-error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> <strong>File too large!</strong> The uploaded file is ' + 
                                (file.size / (1024 * 1024)).toFixed(2) + ' MB. Maximum allowed size is 5MB.';
            input.after(errorDiv);
            
            // Clear the file input
            input.value = '';
            
            // Show alert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'File Too Large',
                    html: 'The selected file <strong>' + file.name + '</strong> is <strong>' + 
                          (file.size / (1024 * 1024)).toFixed(2) + ' MB</strong>.<br>' +
                          'Maximum allowed size is <strong>5MB</strong>.<br><br>' +
                          'Please choose a smaller file.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr.error('File size (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB) exceeds the 5MB limit. Please choose a smaller file.');
            } else {
                alert('File too large! The uploaded file is ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB. Maximum allowed size is 5MB.');
            }
        }
    }

    // Add validation for document uploads
    document.addEventListener('DOMContentLoaded', function() {
        // Validate document uploads before form submission
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                let hasValidationError = false;
                let errorMessages = [];
                
                // Clear previous custom validation messages
                document.querySelectorAll('.custom-validation-error').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                
                // Check each document entry
                const documentEntries = document.querySelectorAll('#documentUploads .document-entry');
                documentEntries.forEach((entry, index) => {
                    const fileInput = entry.querySelector('input[type="file"]');
                    const typeSelect = entry.querySelector('select');
                    const fileCount = fileInput.files.length;
                    const typeValue = typeSelect.value;
                    
                    // Only validate if there's some content
                    if (fileCount > 0 || typeValue !== '') {
                        // If file is selected but no document type is chosen
                        if (fileCount > 0 && typeValue === '') {
                            hasValidationError = true;
                            typeSelect.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback custom-validation-error';
                            errorDiv.textContent = 'Please select a document type for the uploaded file.';
                            typeSelect.after(errorDiv);
                            errorMessages.push(`Document ${index + 1}: Please select a document type`);
                        }
                        
                        // If document type is selected but no file is uploaded
                        if (typeValue !== '' && fileCount === 0) {
                            hasValidationError = true;
                            fileInput.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback custom-validation-error';
                            errorDiv.textContent = 'Please upload a file for the selected document type.';
                            fileInput.after(errorDiv);
                            errorMessages.push(`Document ${index + 1}: Please upload a file`);
                        }
                        
                        // Validate file size (max 5MB per file)
                        if (fileCount > 0) {
                            const maxSize = 5 * 1024 * 1024; // 5MB
                            for (let i = 0; i < fileInput.files.length; i++) {
                                if (fileInput.files[i].size > maxSize) {
                                    hasValidationError = true;
                                    fileInput.classList.add('is-invalid');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'invalid-feedback custom-validation-error';
                                    errorDiv.textContent = 'File size must not exceed 5MB.';
                                    fileInput.after(errorDiv);
                                    errorMessages.push(`Document ${index + 1}: File size exceeds 5MB limit`);
                                    break;
                                }
                            }
                        }
                    }
                });
                
                if (hasValidationError) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    
                    // Show detailed error message
                    if (typeof toastr !== 'undefined') {
                        const message = errorMessages.length > 0 
                            ? 'Document Upload Errors:\n' + errorMessages.join('\n')
                            : 'Please fix the document upload errors before submitting.';
                        toastr.error(message);
                    } else {
                        alert('Please fix the document upload errors:\n' + errorMessages.join('\n'));
                    }
                }
            });
        });
        
        // Clear validation errors when user makes changes
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[type="file"], select[name*="[type]"]')) {
                e.target.classList.remove('is-invalid');
                const errors = e.target.parentElement.querySelectorAll('.custom-validation-error');
                errors.forEach(err => err.remove());
            }
        });
        
        // Handle document deletion via AJAX to avoid form nesting issues
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-document-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const button = e.target.closest('.delete-document-btn');
                const documentId = button.getAttribute('data-document-id');
                const deleteUrl = button.getAttribute('data-delete-url');
                
                if (confirm('Are you sure you want to delete this document?')) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
                    
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the table row with fade effect
                            const row = button.closest('tr');
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                
                                // If no more documents, hide the table
                                if (document.querySelectorAll('.delete-document-btn').length === 0) {
                                    const tableContainer = button.closest('.mb-3');
                                    if (tableContainer) {
                                        tableContainer.style.opacity = '0';
                                        setTimeout(() => {
                                            tableContainer.remove();
                                        }, 300);
                                    }
                                }
                            }, 300);
                            
                            // Show success message
                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message || 'Document deleted successfully');
                            } else {
                                alert(data.message || 'Document deleted successfully');
                            }
                        } else {
                            button.disabled = false;
                            button.innerHTML = '<i class="fa fa-trash"></i> Delete';
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message || 'Failed to delete document');
                            } else {
                                alert(data.message || 'Failed to delete document');
                            }
                        }
                    })
                    .catch(error => {
                        button.disabled = false;
                        button.innerHTML = '<i class="fa fa-trash"></i> Delete';
                        
                        const errorMessage = error.message || 'An error occurred while deleting the document';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    });
                }
            }
        });
    });
</script>
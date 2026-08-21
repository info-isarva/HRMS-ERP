@extends('layouts.app')
@section('content')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="container">
                <!-- Account Logo -->
                <div class="account-logo">
                    <a href="#"><img src="{{ URL::to('assets/images/photo_defaults.png') }}" alt="HRMS" style="width:10rem"></a>
                </div>
                <!-- /Account Logo -->
                <div class="account-box">
                    <div class="account-wrapper">
                        <h3 class="account-title">Password Calculator</h3>
                        <p class="account-subtitle">Find your default password format</p>
                        
                        <div class="card">
                            <div class="card-body">
                                <form id="passwordCalcForm">
                                    <div class="form-group">
                                        <label>Your Employee ID</label>
                                        <input type="text" class="form-control" id="employeeId" placeholder="e.g., DRI-020" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Your Name</label>
                                        <input type="text" class="form-control" id="employeeName" placeholder="e.g., RAVI HOOGAR" required>
                                    </div>
                                    
                                    <div class="form-group text-center mt-4">
                                        <button type="button" id="calculateBtn" class="btn btn-primary">Calculate Password</button>
                                    </div>
                                </form>
                                
                                <div id="passwordResult" class="mt-4" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5>Your Default Password:</h5>
                                        <div class="password-display p-2 bg-light text-center">
                                            <code id="passwordDisplay" style="font-size: 1.2rem;"></code>
                                        </div>
                                        <small class="d-block mt-2">Format: [employee_id_without_special_chars][FIRST_4_CHARS_OF_NAME]</small>
                                    </div>
                                    <p class="text-center">
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Go to Login</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calculateBtn = document.getElementById('calculateBtn');
            const passwordResult = document.getElementById('passwordResult');
            const passwordDisplay = document.getElementById('passwordDisplay');
            
            calculateBtn.addEventListener('click', function() {
                const employeeId = document.getElementById('employeeId').value.trim();
                const employeeName = document.getElementById('employeeName').value.trim();
                
                if (employeeId && employeeName) {
                    // Clean employee ID (remove special characters)
                    const cleanEmployeeId = employeeId.replace(/[^a-zA-Z0-9]/g, '');
                    
                    // Get first 4 characters of name in uppercase
                    const cleanName = employeeName.replace(/[^a-zA-Z0-9]/g, '');
                    const firstFourChars = cleanName.substring(0, 4).toUpperCase();
                    
                    // Generate password
                    const password = cleanEmployeeId + firstFourChars;
                    
                    // Display result
                    passwordDisplay.textContent = password;
                    passwordResult.style.display = 'block';
                } else {
                    alert('Please enter both your Employee ID and Name');
                }
            });
        });
    </script>
@endsection

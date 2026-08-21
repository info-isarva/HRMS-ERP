@extends('layouts.master')
@section('title', 'Profile Details')

@section('css')
<style>
    .profile-img img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .personal-info li {
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .personal-info li:last-child {
        border-bottom: none;
    }
    
    .badge {
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .user-profile {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
    }
</style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Content -->
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Profile</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ul>
                    </div>
                </div>
            </div>
              
            <!-- /Page Header -->
            <div class="card mb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="profile-view">
                                <div class="profile-img-wrap">
                                    <div class="profile-img">
                                        <a href="#">
                                            @php
                                                $profileImage = Auth::user()->avatar;
                                                
                                                // For employee-converted users, try to get image from employee_basic_details
                                                if (Auth::user()->employee_id) {
                                                    $employeeData = DB::table('employee_basic_details')
                                                        ->where(function($query) {
                                                            $user = Auth::user();
                                                            if (is_numeric($user->employee_id)) {
                                                                $query->orWhere('id', $user->employee_id);
                                                            }
                                                            $query->orWhere('employee_id', $user->employee_id);
                                                            $query->orWhere('employee_id', $user->user_id);
                                                            $query->orWhere('name', 'like', '%' . $user->name . '%');
                                                        })
                                                        ->first();
                                                        
                                                    if ($employeeData && !empty($employeeData->profile_image)) {
                                                        $profileImage = $employeeData->profile_image;
                                                    }
                                                }
                                                
                                                // Determine the correct image URL
                                                if ($profileImage) {
                                                    if (str_contains($profileImage, 'assets/')) {
                                                        $imageUrl = url($profileImage);
                                                    } else {
                                                        $imageUrl = url('/assets/employee_profile_image/' . $profileImage);
                                                    }
                                                } else {
                                                    $imageUrl = url('/assets/img/user-icon.webp');
                                                }
                                            @endphp
                                            <img class="user-profile" alt="" src="{{ $imageUrl }}" alt="{{ Auth::user()->name }}">
                                        </a>
                                    </div>
                                </div>
                                <div class="profile-basic">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="profile-info-left">
                                                <h3 class="user-name m-t-0 mb-0">{{ $user->name }}</h3>
                                                @php
                                                    // Get department name from departments table
                                                    $departmentName = 'N/A';
                                                    if (!empty($user->department)) {
                                                        if (is_numeric($user->department)) {
                                                            $dept = DB::table('departments')->where('id', $user->department)->first();
                                                            $departmentName = $dept->department ?? $user->department;
                                                        } else {
                                                            $departmentName = $user->department;
                                                        }
                                                    }
                                                    
                                                    // Get position name from position_types table
                                                    $positionName = 'N/A';
                                                    if (!empty($user->position)) {
                                                        if (is_numeric($user->position)) {
                                                            $pos = DB::table('position_types')->where('id', $user->position)->first();
                                                            $positionName = $pos->position ?? $user->position;
                                                        } else {
                                                            $positionName = $user->position;
                                                        }
                                                    }
                                                    
                                                    $phoneNumber = $user->phone_number;
                                                    $dateOfJoining = $user->join_date;
                                                    $dateOfBirth = null;
                                                    
                                                    // For employee-converted users, get additional details from employee_basic_details
                                                    if (Auth::user()->employee_id) {
                                                        $employeeData = DB::table('employee_basic_details')
                                                            ->leftJoin('departments', 'employee_basic_details.department', '=', 'departments.id')
                                                            ->leftJoin('position_types', 'employee_basic_details.designation', '=', 'position_types.id')
                                                            ->where(function($query) {
                                                                $user = Auth::user();
                                                                if (is_numeric($user->employee_id)) {
                                                                    $query->orWhere('employee_basic_details.id', $user->employee_id);
                                                                }
                                                                $query->orWhere('employee_basic_details.employee_id', $user->employee_id);
                                                                $query->orWhere('employee_basic_details.employee_id', $user->user_id);
                                                                $query->orWhere('employee_basic_details.name', 'like', '%' . $user->name . '%');
                                                            })
                                                            ->select('employee_basic_details.*', 'departments.department', 'position_types.position')
                                                            ->first();
                                                            
                                                        if ($employeeData) {
                                                            $departmentName = $employeeData->department ?? $departmentName;
                                                            $positionName = $employeeData->position ?? $positionName;
                                                            $phoneNumber = $employeeData->contact_number ?? $phoneNumber;
                                                            
                                                            // Get actual joining date from employee record
                                                            if (!empty($employeeData->date_of_joining)) {
                                                                $dateOfJoining = \Carbon\Carbon::parse($employeeData->date_of_joining)->format('d M Y');
                                                            }
                                                            
                                                            // Get date of birth from employee record
                                                            if (!empty($employeeData->date_of_birth)) {
                                                                $dateOfBirth = \Carbon\Carbon::parse($employeeData->date_of_birth)->format('d M Y');
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <h6 class="text-muted">{{ $departmentName }}</h6>
                                                <small class="text-muted">{{ $positionName }}</small>
                                                <div class="staff-id">User ID : {{ $user->user_id }}</div>
                                                <div class="small doj text-muted">Date of Joining : {{ $dateOfJoining }}</div>
                                                @if($dateOfBirth)
                                                    <div class="small text-muted">Date of Birth : {{ $dateOfBirth }}</div>
                                                @endif
                                                @if(Auth::user()->employee_id && isset($employeeData))
                                                    <div class="small text-muted">Employee ID : {{ $employeeData->employee_id ?? 'N/A' }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <ul class="personal-info">
                                                <li>
                                                    <div class="title">Phone:</div>
                                                    <div class="text">{{ $phoneNumber }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Email:</div>
                                                    <div class="text">{{ $user->email }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Role:</div>
                                                    <div class="text">{{ $user->role_name }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Status:</div>
                                                    <div class="text">
                                                        <span class="badge {{ $user->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $user->status }}
                                                        </span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="pro-edit"><a data-target="#profile_info" data-toggle="modal" class="edit-icon" href="#"><i class="fa fa-pencil"></i></a></div> -->
                            </div>
                            
                            <!-- Password Change Section -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4 class="card-title">Change Password</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('change/password/db') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Current Password <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" placeholder="Enter current password" required>
                                                    @error('current_password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>New Password <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" placeholder="Enter new password" required>
                                                    @error('new_password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Confirm New Password <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control @error('new_confirm_password') is-invalid @enderror" name="new_confirm_password" placeholder="Confirm new password" required>
                                                    @error('new_confirm_password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-primary">Update Password</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Privacy & Data Rights Section -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h4 class="card-title">Privacy & Data Rights (DPDP Act)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h5>Consent Status</h5>
                                        @if(isset($dpdpConsent) && $dpdpConsent)
                                            <div class="alert alert-success">
                                                <i class="fa fa-check-circle"></i> You accepted the DPDP Privacy Policy on <strong>{{ $dpdpConsent->accepted_at->format('d M Y, h:i A') }}</strong>.
                                                <br><small>IP Address: {{ $dpdpConsent->ip_address }}</small>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i> Consent record not found.
                                            </div>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="mt-4">
                                        <h5>Request Data Change</h5>
                                        <p class="text-muted small">Under the DPDP Act, you have the right to request corrections or erasure of your personal data.</p>
                                        
                                        <form action="{{ url('api/compliance/data-change-request') }}" method="POST" id="dataChangeRequestForm">
                                            @csrf
                                            <input type="hidden" name="user_email" value="{{ Auth::user()->email }}">
                                            <input type="hidden" name="source_system" value="payroll">
                                            <!-- Include the sync token securely -->
                                            <input type="hidden" name="sync_token" value="{{ env('ATTENDANCE_SYNC_TOKEN', env('JWT_HMAC_SECRET')) }}">
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Request Type <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="request_type" required>
                                                            <option value="" disabled selected>Select a request type</option>
                                                            <option value="Correction">Data Correction</option>
                                                            <option value="Erasure">Data Erasure / Deletion</option>
                                                            <option value="Export">Data Export</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Details of Request <span class="text-danger">*</span></label>
                                                        <textarea class="form-control" name="details" rows="3" placeholder="Please describe what data you want changed, deleted, or exported..." required></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Submit Request</button>
                                        </form>
                                    </div>

                                    <hr>

                                    <div class="mt-4">
                                        <h5>Your Past Requests</h5>
                                        @if(isset($dataRequests) && $dataRequests->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped custom-table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Details</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dataRequests as $req)
                                                        <tr>
                                                            <td>{{ $req->created_at->format('d M Y') }}</td>
                                                            <td>{{ $req->request_type }}</td>
                                                            <td>{{ Str::limit($req->details, 50) }}</td>
                                                            <td>
                                                                @if($req->status == 'pending')
                                                                    <span class="badge bg-warning text-dark"><i class="fa fa-hand-paper-o"></i> Pending</span>
                                                                @elseif($req->status == 'resolved')
                                                                    <span class="badge bg-success"><i class="fa fa-check"></i> Resolved</span>
                                                                @else
                                                                    <span class="badge bg-danger"><i class="fa fa-times"></i> {{ ucfirst($req->status) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted">You have no past data requests.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Content -->
        {{-- @if(!empty($information)) --}}
        <!-- Profile Modal -->
        <div id="profile_info" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Profile Information
                            @if(Auth::user()->employee_id)
                                <small class="text-muted">(Employee-converted user)</small>
                            @endif
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('profile/information/save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="profile-img-wrap edit-img">
                                        @php
                                            $profileImage = Auth::user()->avatar;
                                            
                                            // For employee-converted users, try to get image from employee_basic_details
                                            if (Auth::user()->employee_id) {
                                                $employeeData = DB::table('employee_basic_details')
                                                    ->where(function($query) {
                                                        $user = Auth::user();
                                                        if (is_numeric($user->employee_id)) {
                                                            $query->orWhere('id', $user->employee_id);
                                                        }
                                                        $query->orWhere('employee_id', $user->employee_id);
                                                        $query->orWhere('employee_id', $user->user_id);
                                                        $query->orWhere('name', 'like', '%' . $user->name . '%');
                                                    })
                                                    ->first();
                                                    
                                                if ($employeeData && !empty($employeeData->profile_image)) {
                                                    $profileImage = $employeeData->profile_image;
                                                }
                                            }
                                            
                                            // Determine the correct image URL
                                            if ($profileImage) {
                                                if (str_contains($profileImage, 'assets/')) {
                                                    $imageUrl = url($profileImage);
                                                } else {
                                                    $imageUrl = url('/assets/employee_profile_image/' . $profileImage);
                                                }
                                            } else {
                                                $imageUrl = url('/assets/img/user-icon.webp');
                                            }
                                        @endphp
                                        <img class="inline-block" src="{{ $imageUrl }}" alt="{{ Auth::user()->name }}">
                                        <div class="fileupload btn">
                                            <span class="btn-text">edit</span>
                                            <input class="upload" type="file" id="image" name="images">
                                            <input type="hidden" name="hidden_image" id="e_image" value="{{ $profileImage }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror " id="name" name="name" value="{{ Auth::user()->name }}" >
                                                <input type="hidden" class="form-control" id="user_id" name="user_id" value="{{ Auth::user()->user_id }}">
                                                <input type="hidden" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}">
                                                @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gender</label>
                                        
                                                @php
                                                    $gender = $information->gender ?? '';
                                                    $validGenders = ['Male', 'Female'];
                                                @endphp
                                             
                                                @if(empty($gender) || !in_array($gender, $validGenders))
                                                    <!-- Only show dropdown -->
                                                    <select class="select form-control" id="gender" name="gender">
                                                        <option value="" disabled selected>Select Gender</option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    </select>
                                                @else                                                   
                                                    <select class="select form-control" id="gender" name="gender">
                                                        <option value="Male" {{ $gender == 'Male' ? 'selected' : '' }}>Male</option>
                                                        <option value="Female" {{ $gender == 'Female' ? 'selected' : '' }}>Female</option>
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                @php
                                                    $phone = $information->phone_number ?? '';
                                                @endphp
                                        
                                                @if(empty($phone))
                                                    <!-- Only show input -->
                                                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter phone number">
                                                @else                                                    
                                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $phone }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        @php
                                            $address = $information->address ?? '';
                                        @endphp
                                
                                        @if(empty($address))
                                            <!-- Only show input -->
                                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter address">
                                        @else
                                            
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $address }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Profile Modal -->
        {{-- @endif --}}


       

    <!-- /Page Content -->
    </div>
    @section('script')
        <!-- Password Change Validation -->
        <script>
            $(document).ready(function() {
                // Password change form validation
                $('form[action="{{ route('change/password/db') }}"]').validate({
                    rules: {
                        current_password: {
                            required: true,
                            minlength: 1
                        },
                        new_password: {
                            required: true,
                            minlength: 8
                        },
                        new_confirm_password: {
                            required: true,
                            equalTo: '[name="new_password"]'
                        }
                    },
                    messages: {
                        current_password: {
                            required: 'Please enter your current password',
                            minlength: 'Password must be at least 1 character'
                        },
                        new_password: {
                            required: 'Please enter a new password',
                            minlength: 'Password must be at least 8 characters'
                        },
                        new_confirm_password: {
                            required: 'Please confirm your new password',
                            equalTo: 'Passwords do not match'
                        }
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.form-group').append(error);
                    },
                    highlight: function(element, errorClass, validClass) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    }
                });
            });
        </script>

        <!-- Personal Info -->
        <script>
            $('#personalInfo').validate({  
                rules: {  
                    passport_no: 'required',  
                    passport_expiry_date: 'required',  
                    tel: 'required',  
                    nationality: 'required',  
                    religion: 'required',  
                    marital_status: 'required',  
                    employment_of_spouse: 'required',  
                    children: 'required',  
                },  
                messages: {
                    passport_no: 'Please Input Passport No',    
                    passport_expiry_date: 'Please Input Passport Expiry Date',    
                    tel: 'Please Input Phone Number',     
                    nationality: 'Please Input Nationality',    
                    religion: 'Please Input Religion',    
                    marital_status: 'Please Input Marital status',    
                    employment_of_spouse: 'Please Input Employment of spouse',    
                    children: 'Please Input No. of children',    
                },  
                submitHandler: function(form) {  
                    form.submit();
                }  
            });  
        </script>

        <!-- Emergency Contact -->
        <script>
            $('#validation').validate({  
                rules: {  
                    name_primary: 'required',  
                    relationship_primary: 'required',  
                    phone_primary: 'required',  
                    phone_2_primary: 'required',  
                    name_secondary: 'required',  
                    relationship_secondary: 'required',  
                    phone_secondary: 'required',  
                    phone_2_secondary: 'required',  
                },  
                messages: {
                    name_primary: 'Please input name primary',  
                    relationship_primary: 'Please input relationship primary',  
                    phone_primary: 'Please input phone primary',  
                    phone_2_primary: 'Please input phone 2 primary',  
                    name_secondary: 'Please input name secondary',  
                    relationship_secondary: 'Please input relationship secondary',  
                    phone_secondaryr: 'Please input phone secondary',  
                    phone_2_secondary: 'Please input phone 2 secondary',  
                },  
                submitHandler: function(form) {  
                    form.submit();
                }  
            });  
        </script>
        
    @endsection
@endsection
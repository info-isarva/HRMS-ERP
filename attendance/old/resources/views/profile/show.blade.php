@extends('layouts.app')

@section('title', 'My Profile - HRMS')
@section('page-title', 'My Profile')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white rounded-full"></div>
            <div class="absolute top-10 -right-8 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-white rounded-full"></div>
        </div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 flex items-center">
                        <i class="fas fa-user-circle mr-4"></i>
                        My Profile
                    </h1>
                    <p class="text-blue-100 text-lg">Manage your personal information and account settings</p>
                </div>
                <div class="hidden lg:block">
                    <div class="w-36 h-36 bg-white bg-opacity-15 rounded-full flex items-center justify-center">
                        <i class="fas fa-id-card text-5xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Personal Information Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                    Personal Information
                </h3>
                <p class="text-gray-600 text-sm mt-1">Your account and employee details (read-only)</p>
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <p class="text-blue-700 text-xs">
                            <strong>Note:</strong> Personal information is managed by HR and cannot be changed here. 
                            Contact your HR department for any updates to your profile details.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- User Account Information -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 border-b border-gray-200 pb-2">Account Information</h4>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $user->name }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $user->email }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                       ($user->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                       ($user->role === 'manager' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                    <i class="fas {{ $user->role === 'super_admin' ? 'fa-crown' : 
                                                   ($user->role === 'admin' ? 'fa-shield-alt' : 
                                                   ($user->role === 'manager' ? 'fa-users' : 'fa-user')) }} mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account Created</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $user->created_at->format('M d, Y g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Information (if available) -->
                @if($employee)
                <div class="space-y-4 pt-4 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 border-b border-gray-200 pb-2">Employee Information</h4>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID (Payroll)</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                <span class="font-mono">{{ $employee->payroll_id ?? 'N/A' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $employee->designation ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $employee->department->name ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $employee->phone ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Joining</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                {{ $employee->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $employee->status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas {{ $employee->status === 'Active' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    {{ $employee->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="space-y-4 pt-4 border-t border-gray-200">
                    <div class="text-center py-8">
                        <i class="fas fa-info-circle text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-600">No employee record found for your account.</p>
                        <p class="text-gray-500 text-sm mt-1">Employee information will appear here once your account is linked to an employee record.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-key text-orange-600 mr-3"></i>
                    Change Password
                </h3>
                <p class="text-gray-600 text-sm mt-1">Update your account password for security</p>
            </div>
            
            <div class="p-6">
                <form method="POST" action="{{ route('profile.update-password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('current_password') border-red-500 @enderror"
                                   placeholder="Enter your current password"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePasswordVisibility('current_password')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('password') border-red-500 @enderror"
                                   placeholder="Enter your new password"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePasswordVisibility('password')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-xs mt-1">Password must be at least 8 characters long</p>
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Confirm your new password"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePasswordVisibility('password_confirmation')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_confirmation_icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        <!-- Privacy & Data Rights Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-emerald-50">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-shield-alt text-teal-600 mr-3"></i>
                    Privacy & Data Rights (DPDP Act)
                </h3>
                <p class="text-gray-600 text-sm mt-1">Manage your data privacy preferences and submit requests</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Consent Status & Form -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Consent Status</h4>
                            @if(isset($dpdpConsent) && $dpdpConsent)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                                    <div>
                                        <p class="text-green-800 font-medium">DPDP Privacy Policy Accepted</p>
                                        <p class="text-green-600 text-sm mt-1">Accepted on {{ \Carbon\Carbon::parse($dpdpConsent->accepted_at)->timezone('Asia/Kolkata')->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                                    <div>
                                        <p class="text-yellow-800 font-medium">Consent record not found.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Request Data Change</h4>
                            <p class="text-gray-600 text-sm mb-4">Under the DPDP Act, you have the right to request corrections or erasure of your personal data.</p>
                            
                            <form action="{{ route('profile.data-change-request') }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Request Type <span class="text-red-500">*</span></label>
                                    <select name="request_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                                        <option value="" disabled selected>Select a request type</option>
                                        <option value="Correction">Data Correction</option>
                                        <option value="Erasure">Data Erasure / Deletion</option>
                                        <option value="Export">Data Export</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Details of Request <span class="text-red-500">*</span></label>
                                    <textarea name="details" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="Please describe what data you want changed, deleted, or exported..." required></textarea>
                                </div>
                                
                                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Past Requests -->
                    <div>
                        <h4 class="font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Your Past Requests</h4>
                        
                        @if(isset($dataRequests) && $dataRequests->count() > 0)
                            <div class="space-y-4">
                                @foreach($dataRequests as $req)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $req->request_type }}
                                                </span>
                                            </div>
                                            <div>
                                                @if($req->status == 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-hand-paper mr-1"></i> Pending
                                                    </span>
                                                @elseif($req->status == 'resolved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i> Resolved
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times mr-1"></i> {{ ucfirst($req->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2">{{ \Illuminate\Support\Str::limit($req->details, 80) }}</p>
                                        <p class="text-xs text-gray-500">Submitted on {{ \Carbon\Carbon::parse($req->created_at, 'UTC')->timezone('Asia/Kolkata')->format('M d, Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-100">
                                <i class="fas fa-history text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-500">You have no past data requests.</p>
                            </div>
                        @endif
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation feedback
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('profile.update-password') }}"]');
    const newPassword = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    if (form && newPassword && confirmPassword) {
        // Real-time password confirmation validation
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value && this.value.length > 0) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });
        
        // Password strength indicator
        newPassword.addEventListener('input', function() {
            const password = this.value;
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasMinLength = password.length >= 8;
            
            if (password.length > 0) {
                if (hasLetter && hasMinLength) {
                    this.classList.remove('border-red-500', 'border-yellow-500');
                    this.classList.add('border-green-500');
                } else if (hasMinLength) {
                    this.classList.remove('border-red-500', 'border-green-500');
                    this.classList.add('border-yellow-500');
                } else {
                    this.classList.remove('border-green-500', 'border-yellow-500');
                    this.classList.add('border-red-500');
                }
            } else {
                this.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
                this.classList.add('border-gray-300');
            }
        });
    }
});
</script>
@endsection
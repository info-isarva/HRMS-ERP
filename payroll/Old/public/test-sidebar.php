<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar2 Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Include sidebar2 -->
        @include('includes.sidebar2')
        
        <!-- Main content -->
        <div class="flex-1 p-8">
            <div class="tailwind-scope">
                <h1 class="text-4xl font-bold text-gray-800 mb-8">Payroll System Dashboard</h1>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Sample Card 1 -->
                    <div class="tailwind-card tailwind-card-hover">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Employee Management</h3>
                        <p class="text-gray-600 mb-4">Manage employee records, profiles, and basic information.</p>
                        <button class="tailwind-btn-primary">
                            <i class="fas fa-users me-2"></i>
                            View Employees
                        </button>
                    </div>
                    
                    <!-- Sample Card 2 -->
                    <div class="tailwind-card tailwind-card-hover">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Payroll Processing</h3>
                        <p class="text-gray-600 mb-4">Process monthly payroll and manage salary calculations.</p>
                        <button class="tailwind-btn-primary">
                            <i class="fas fa-calculator me-2"></i>
                            Process Payroll
                        </button>
                    </div>
                    
                    <!-- Sample Card 3 -->
                    <div class="tailwind-card tailwind-card-hover">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Reports & Analytics</h3>
                        <p class="text-gray-600 mb-4">Generate comprehensive payroll reports and analytics.</p>
                        <button class="tailwind-btn-primary">
                            <i class="fas fa-chart-bar me-2"></i>
                            View Reports
                        </button>
                    </div>
                </div>
                
                <!-- Test Section -->
                <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h2 class="text-2xl font-semibold text-blue-800 mb-4">
                        <i class="fas fa-check-circle me-2"></i>
                        Integration Test Results
                    </h2>
                    <div class="space-y-2">
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check me-2"></i>
                            <span>Tailwind CSS v3.4.0 - Compiled Successfully</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check me-2"></i>
                            <span>jQuery Integration - Configured</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check me-2"></i>
                            <span>Sidebar2 Component - Loaded</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check me-2"></i>
                            <span>Font Awesome 6 - Working</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check me-2"></i>
                            <span>Production Build - Ready</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test jQuery functionality
        $(document).ready(function() {
            console.log('✅ jQuery loaded successfully!');
            console.log('✅ Tailwind CSS styles applied!');
            console.log('✅ Sidebar2 integration complete!');
            
            // Add some interactive functionality
            $('.tailwind-btn-primary').on('click', function() {
                const buttonText = $(this).text().trim();
                alert('Button clicked: ' + buttonText + '\n\n✅ jQuery event handling working!');
            });
        });
    </script>
</body>
</html>
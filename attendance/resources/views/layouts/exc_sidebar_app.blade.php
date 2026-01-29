<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRMS - Human Resource Management System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-inter">
    <div class="min-h-screen flex">
       
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            @include('layouts.header')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
            
            <!-- Footer -->
            @include('layouts.footer')
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>

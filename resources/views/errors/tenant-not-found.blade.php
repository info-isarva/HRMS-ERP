<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 max-w-md w-full p-8 text-center">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">🏢</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Company not found</h1>
        <p class="text-gray-600 mt-2 text-sm">
            No registered company is mapped to this domain.
        </p>
        @if(!empty($host))
            <p class="text-xs text-gray-400 mt-3 font-mono">{{ $host }}</p>
        @endif
        <p class="text-gray-500 text-sm mt-4">Contact your HRMS administrator if you believe this is an error.</p>
    </div>
</body>
</html>

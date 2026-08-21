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
        <h1 class="text-xl font-bold text-gray-900">Company not found</h1>
        <p class="text-gray-600 mt-2 text-sm">No registered company is mapped to this domain.</p>
        @if(!empty($host))<p class="text-xs text-gray-400 mt-3 font-mono">{{ $host }}</p>@endif
    </div>
</body>
</html>

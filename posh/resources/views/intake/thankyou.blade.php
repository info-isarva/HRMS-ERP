<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint submitted</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center p-6 font-[Inter,sans-serif]">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 max-w-md w-full text-center">
        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-check text-2xl text-green-600"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Thank you</h2>
        <p class="text-gray-600 mt-2">Your complaint has been registered.</p>
        <p class="mt-4 text-lg font-semibold text-blue-600">{{ $complaint->case_number }}</p>
        <p class="text-sm text-gray-500 mt-2">Keep this reference. IC will acknowledge per statutory timelines.</p>
    </div>
</body>
</html>

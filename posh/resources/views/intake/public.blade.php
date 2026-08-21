<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POSH Complaint — {{ $org->display_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-[Inter,sans-serif]">
    <div class="max-w-lg mx-auto p-6">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white mb-6 text-center">
            <i class="fas fa-shield-halved text-3xl mb-2"></i>
            <h1 class="text-xl font-bold">Confidential POSH complaint</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $org->display_name }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-4">QR / public intake. Identity can stay confidential; only IC investigates per POSH Act.</p>
            <form method="POST" action="{{ route('intake.store', $org->intake_key) }}" class="space-y-4">
                @csrf
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_anonymous" value="1" checked class="rounded border-gray-300 text-blue-600">
                    File anonymously
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name (optional)</label>
                    <input name="complainant_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input name="employee_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondent name *</label>
                    <input name="respondent_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Respondent type</label>
                    <select name="respondent_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        @foreach(config('posh.respondent_types') as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Incident date *</label>
                    <input type="date" name="incident_date" required max="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="5" required minlength="20" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Submit complaint
                </button>
            </form>
        </div>
    </div>
</body>
</html>

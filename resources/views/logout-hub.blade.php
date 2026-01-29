<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
        function redirectHome() {
            window.location = "{{ url('/') }}"; // Redirect home after completion
        }
    </script>
</head>
<body onload="setTimeout(redirectHome, 3000)">
    <p>Logging out of all applications...</p>

    @foreach ($urls as $url)
        <iframe
            src="{{ $url }}"
            style="display:none;"
            title="Logout Frame"
        ></iframe>
    @endforeach
</body>
</html>
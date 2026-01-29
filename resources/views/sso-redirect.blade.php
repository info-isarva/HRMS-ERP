<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Payroll</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sso-form').submit();
        });
    </script>
</head>
<body>
    <p>Redirecting to Payroll system...</p>
    {{-- <form id="sso-form" action="{{ $payrollUrl }}" method="GET">
     <input type="hidden" name="token" value="{{ $token }}">
        @csrf
    </form> --}}
</body>
</html>
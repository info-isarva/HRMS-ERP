<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HRMS</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 1rem;
        }
        .icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .message {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .contact-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #007bff;
        }
        .logout-btn {
            background: #007bff;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }
        .logout-btn:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Access Denied</h1>
        <div class="message">
            {{ $message ?? 'You don\'t have permission to access this system.' }}
        </div>
        
        <div class="contact-info">
            <strong>Need Help?</strong><br>
            Please contact your HR department or system administrator to request access to the HRMS system.
        </div>
        
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">
                Logout & Return to Login Page
            </button>
        </form>
    </div>
</body>
</html>
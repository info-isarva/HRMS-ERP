<!DOCTYPE html>
<html>
<head>
    <title>Meeting Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-body {
            padding: 20px;
        }
        .email-footer {
            background: #f1f1f1;
            color: #555;
            text-align: center;
            padding: 10px;
            font-size: 12px;
        }
        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Meeting Reminder</h1>
        </div>
        <div class="email-body">
            <p>Dear {{ $meeting['host_name'] }},</p>
            <p>This is a reminder for your upcoming meeting. Here are the details:</p>
            <ul>
                <li><strong>Title:</strong> {{ $meeting['name'] }}</li>
                <li><strong>Description:</strong> {{ $meeting['description'] }}</li>
                <li><strong>Start Time:</strong> {{ $meeting['start_at'] }}</li>
                <li><strong>End Time:</strong> {{ $meeting['finish_at'] }}</li>
                <li><strong>Location:</strong> {{ $meeting['location'] }}</li>
            </ul>
            <p>Thank you.</p>
        </div>
        <div class="email-footer">
            <p>&copy; 2026 Your Company. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>Meeting Reminder</title>
</head>
<body>
    <h1>Meeting Reminder</h1>
    <p>Dear {{ $user->name }},</p>
    <p>This is a reminder for your upcoming meeting:</p>
    <ul>
        <li><strong>Title:</strong> {{ $meeting->name }}</li>
        <li><strong>Description:</strong> {{ $meeting->description }}</li>
        <li><strong>Start Time:</strong> {{ $meeting->start_at }}</li>
        <li><strong>Location:</strong> {{ $meeting->location }}</li>
    </ul>
    <p>Thank you.</p>
</body>
</html>
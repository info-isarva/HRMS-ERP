<?php

// Simple Email Test without Laravel Bootstrap
echo "<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>";

echo "<h1>📧 Email Configuration Test</h1>";

// Test 1: Check if mail functions are available
echo "<div class='section'>";
echo "<h2>1. PHP Mail Function Check</h2>";
if (function_exists('mail')) {
    echo "<p class='success'>✓ PHP mail() function is available</p>";
} else {
    echo "<p class='error'>❌ PHP mail() function is not available</p>";
}
echo "</div>";

// Test 2: Check environment variables
echo "<div class='section'>";
echo "<h2>2. Environment Variables Check</h2>";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "<p class='success'>✓ .env file exists</p>";
    $envContent = file_get_contents($envFile);
    
    // Check for Brevo configuration
    if (strpos($envContent, 'MAIL_HOST=smtp-relay.brevo.com') !== false) {
        echo "<p class='success'>✓ Brevo SMTP host configured</p>";
    } else {
        echo "<p class='error'>❌ Brevo SMTP host not found in .env</p>";
    }
    
    if (strpos($envContent, 'MAIL_PORT=587') !== false) {
        echo "<p class='success'>✓ SMTP port 587 configured</p>";
    } else {
        echo "<p class='error'>❌ SMTP port 587 not configured</p>";
    }
    
    if (strpos($envContent, 'MAIL_USERNAME=') !== false && strpos($envContent, 'MAIL_USERNAME=""') === false) {
        echo "<p class='success'>✓ SMTP username configured</p>";
    } else {
        echo "<p class='error'>❌ SMTP username not configured</p>";
    }
    
} else {
    echo "<p class='error'>❌ .env file not found</p>";
}
echo "</div>";

// Test 3: Simple SMTP connection test
echo "<div class='section'>";
echo "<h2>3. SMTP Connection Test</h2>";
echo "<p class='info'>Testing connection to smtp-relay.brevo.com:587...</p>";

$connection = @fsockopen('smtp-relay.brevo.com', 587, $errno, $errstr, 30);
if ($connection) {
    echo "<p class='success'>✓ Successfully connected to Brevo SMTP server</p>";
    fclose($connection);
} else {
    echo "<p class='error'>❌ Failed to connect to Brevo SMTP server: $errstr ($errno)</p>";
}
echo "</div>";

// Test 4: Email template file check
echo "<div class='section'>";
echo "<h2>4. Email Template Check</h2>";
$templateFile = __DIR__ . '/resources/views/emails/leave-application-submitted.blade.php';
if (file_exists($templateFile)) {
    echo "<p class='success'>✓ Email template file exists</p>";
    echo "<p class='info'>Template path: " . $templateFile . "</p>";
    echo "<p class='info'>Template size: " . number_format(filesize($templateFile)) . " bytes</p>";
} else {
    echo "<p class='error'>❌ Email template file not found</p>";
    echo "<p class='error'>Expected path: " . $templateFile . "</p>";
}
echo "</div>";

// Test 5: Notification class check
echo "<div class='section'>";
echo "<h2>5. Notification Class Check</h2>";
$notificationFile = __DIR__ . '/app/Notifications/LeaveApplicationSubmitted.php';
if (file_exists($notificationFile)) {
    echo "<p class='success'>✓ Notification class file exists</p>";
    echo "<p class='info'>Notification path: " . $notificationFile . "</p>";
} else {
    echo "<p class='error'>❌ Notification class file not found</p>";
    echo "<p class='error'>Expected path: " . $notificationFile . "</p>";
}
echo "</div>";

// Test 6: Simple mail sending test (if we have the configuration)
echo "<div class='section'>";
echo "<h2>6. Simple Mail Test</h2>";
echo "<p class='info'>This would send a test email if SMTP credentials are properly configured.</p>";
echo "<p style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px;'>";
echo "<strong>Note:</strong> To fully test email sending, you would need to configure Laravel's mail system and run a proper test through the framework.";
echo "</p>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📋 Summary</h2>";
echo "<p>If all the above tests show ✓ (green checkmarks), your email system should be ready to work.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Create a leave application through the HRMS system</li>";
echo "<li>Check if emails are sent to saikiran@idaksh.in and akash@idaksh.in</li>";
echo "<li>Verify the email template renders correctly</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";

?>
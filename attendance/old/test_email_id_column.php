<?php

// Simple test script to verify email_id is being stored correctly
// Run this from the attendance directory

require_once 'vendor/autoload.php';

// Load environment
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Testing email_id column in leave_applications table...\n\n";

// Test 1: Check if email_id column exists
try {
    $result = Capsule::select("SHOW COLUMNS FROM leave_applications LIKE 'email_id'");
    if (empty($result)) {
        echo "❌ FAIL: email_id column does not exist\n";
        exit(1);
    }
    echo "✅ PASS: email_id column exists\n";
    
    $column = $result[0];
    echo "   - Type: {$column->Type}\n";
    echo "   - Null: {$column->Null}\n";
    echo "   - Key: {$column->Key}\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR checking column: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if index exists
try {
    $result = Capsule::select("SHOW INDEX FROM leave_applications WHERE Key_name = 'idx_email_id'");
    if (empty($result)) {
        echo "❌ FAIL: email_id index does not exist\n";
    } else {
        echo "✅ PASS: email_id index exists\n\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR checking index: " . $e->getMessage() . "\n";
}

// Test 3: Check current leave applications count
try {
    $count = Capsule::table('leave_applications')->count();
    echo "📊 Current leave applications count: {$count}\n";
    
    if ($count > 0) {
        echo "⚠️  WARNING: Table is not empty. You may want to clear it first.\n";
    } else {
        echo "✅ Table is empty and ready for new applications\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR checking count: " . $e->getMessage() . "\n";
}

echo "\n🎉 Tests completed!\n";
echo "\nNext steps:\n";
echo "1. Try applying for leave through the web interface\n";
echo "2. Check that the email_id is automatically populated\n";
echo "3. Verify the email_id matches the user's email address\n";
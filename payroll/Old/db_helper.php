<?php

/**
 * Database Helper Script
 * 
 * This script provides database schema information without requiring Composer's platform check
 * Useful when the CLI PHP version differs from the server PHP version
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load required files manually without Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel application manually
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the kernel and handle the request
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput,
    new Symfony\Component\Console\Output\ConsoleOutput
);

// Function to examine database structure
function examineTable($tableName)
{
    echo "Examining table: $tableName\n";
    $columns = DB::select("SHOW COLUMNS FROM $tableName");
    
    echo "Table structure for '$tableName':\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-20s %-20s %-10s %-10s %-20s\n", 
        'Field', 'Type', 'Null', 'Key', 'Default');
    echo str_repeat('-', 80) . "\n";
    
    foreach ($columns as $column) {
        echo sprintf("%-20s %-20s %-10s %-10s %-20s\n", 
            $column->Field,
            $column->Type,
            $column->Null,
            $column->Key,
            $column->Default ?? 'NULL'
        );
    }
    echo str_repeat('-', 80) . "\n\n";
}

// Check which tables to examine
$tablesToExamine = [];

if (isset($argv[1])) {
    $tablesToExamine = [$argv[1]];
} else {
    // Default tables to check
    $tablesToExamine = ['users', 'employee_basic_details'];
}

// Use Laravel's DB facade to execute database queries
use Illuminate\Support\Facades\DB;

foreach ($tablesToExamine as $table) {
    try {
        examineTable($table);
    } catch (Exception $e) {
        echo "Error examining table $table: " . $e->getMessage() . "\n";
    }
}

// Custom query
if (isset($argv[2])) {
    try {
        $customQuery = $argv[2];
        echo "Running custom query: $customQuery\n";
        $results = DB::select($customQuery);
        
        if (!empty($results)) {
            echo "Results:\n";
            print_r($results);
        } else {
            echo "No results returned.\n";
        }
    } catch (Exception $e) {
        echo "Error running custom query: " . $e->getMessage() . "\n";
    }
}

echo "\nDone.\n";

// Exit
$kernel->terminate($input, $status);
exit($status);

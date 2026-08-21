<?php
/**
 * Test script for Biometric Import System
 * 
 * This script tests the biometric import parsers without using artisan
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\BiometricParsers\ZKTecoParser;
use App\Services\BiometricParsers\ESSLParser;
use App\Services\BiometricParsers\RealtimeParser;
use App\Services\BiometricParsers\GenericCSVParser;

echo "=== Biometric Import System Test ===\n\n";

$testFiles = [
    'ZKTeco' => __DIR__ . '/storage/app/test_biometric_samples/zkteco_sample.dat',
    'eSSL' => __DIR__ . '/storage/app/test_biometric_samples/essl_sample.csv',
    'Realtime' => __DIR__ . '/storage/app/test_biometric_samples/realtime_sample.txt',
    'Generic CSV' => __DIR__ . '/storage/app/test_biometric_samples/generic_sample.csv',
];

$parsers = [
    'ZKTeco' => new ZKTecoParser(),
    'eSSL' => new ESSLParser(),
    'Realtime' => new RealtimeParser(),
    'Generic CSV' => new GenericCSVParser(),
];

foreach ($testFiles as $name => $filePath) {
    echo "Testing $name Parser...\n";
    echo str_repeat('-', 50) . "\n";
    
    if (!file_exists($filePath)) {
        echo "❌ File not found: $filePath\n\n";
        continue;
    }
    
    $parser = $parsers[$name];
    
    // Test validation
    $isValid = $parser->validate($filePath);
    echo "File Validation: " . ($isValid ? "✓ PASS" : "✗ FAIL") . "\n";
    
    if (!$isValid) {
        echo "\n";
        continue;
    }
    
    // Test parsing
    try {
        $records = $parser->parse($filePath);
        echo "Records Parsed: " . count($records) . "\n";
        
        if (count($records) > 0) {
            echo "Sample Record:\n";
            print_r($records[0]);
        }
        
        echo "✓ Parsing successful\n";
    } catch (Exception $e) {
        echo "❌ Parsing failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";

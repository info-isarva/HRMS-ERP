<?php
/**
 * Simple test file to verify Company Settings API - Single Endpoint Only
 * 
 * Access this file via: http://your-domain.com/payroll/test_company_api.php
 */

// Include Laravel's bootstrap
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test the single comprehensive API endpoint
try {
    echo "<h2>Company Settings API Test - Single Endpoint</h2>";
    echo "<p>Testing: <strong>GET /api/company-settings</strong></p>";
    
    // Test 1: Get All Company Settings
    echo "<h3>1. GET /api/company-settings (All Data)</h3>";
    $request = Illuminate\Http\Request::create('/api/company-settings', 'GET');
    $response = $kernel->handle($request);
    echo "<pre>" . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "</pre>";
    
    // Test 2: Get Specific Fields
    echo "<h3>2. GET /api/company-settings?fields=company_name,logo_url,favicon_url</h3>";
    $request = Illuminate\Http\Request::create('/api/company-settings?fields=company_name,logo_url,favicon_url', 'GET');
    $response = $kernel->handle($request);
    echo "<pre>" . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "</pre>";
    
    // Test 3: Get Contact and Assets
    echo "<h3>3. GET /api/company-settings?fields=contact,assets</h3>";
    $request = Illuminate\Http\Request::create('/api/company-settings?fields=contact,assets', 'GET');
    $response = $kernel->handle($request);
    echo "<pre>" . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<br><h3>✅ Single API Endpoint Test Complete!</h3>";
    echo "<p><strong>Main Endpoint:</strong> <code>GET /api/company-settings</code></p>";
    echo "<p><strong>Field Filtering:</strong> <code>GET /api/company-settings?fields=field1,field2</code></p>";
    echo "<p>🎯 <strong>Clean and Simple:</strong> One endpoint for everything!</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error Testing API:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
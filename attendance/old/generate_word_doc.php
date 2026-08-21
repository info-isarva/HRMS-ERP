<?php
// Simple script to convert the feature documentation to Word format
// Usage: php generate_word_doc.php

echo "Generating Word Document...\n";

$htmlFile = __DIR__ . '/feature_document.html';
$outputFile = __DIR__ . '/HRMS_Attendance_Documentation.doc';

if (!file_exists($htmlFile)) {
    die("Error: feature_document.html not found!\n");
}

$html = file_get_contents($htmlFile);

// Strip the <style> tag but keep structure
$html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
$html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);

// Add basic Word-compatible inline styles
$wordHtml = <<<HTML
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<meta charset="UTF-8">
<title>HRMS Attendance System Documentation</title>
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>100</w:Zoom>
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<![endif]-->
<style>
body { 
    font-family: Calibri, Arial, sans-serif; 
    font-size: 11pt;
    line-height: 1.5;
}
h1 { 
    font-size: 24pt; 
    color: #2b6cb0;
    border-bottom: 2px solid #2b6cb0;
    padding-bottom: 8pt;
    margin-top: 0;
}
h2 { 
    font-size: 18pt; 
    color: #2b6cb0;
    border-left: 4px solid #2b6cb0;
    padding-left: 10pt;
    margin-top: 24pt;
}
h3 { 
    font-size: 14pt; 
    color: #2d3748;
    margin-top: 16pt;
}
h4 { 
    font-size: 12pt; 
    color: #4a5568;
    margin-top: 12pt;
}
code {
    background-color: #f0f0f0;
    padding: 2px 4px;
    font-family: 'Courier New', monospace;
    color: #c53030;
}
pre {
    background-color: #2d3748;
    color: #e2e8f0;
    padding: 12pt;
    font-family: 'Courier New', monospace;
    overflow-x: auto;
    border-radius: 4pt;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 12pt 0;
}
th, td {
    border: 1px solid #cbd5e0;
    padding: 8pt;
    text-align: left;
}
th {
    background-color: #edf2f7;
    font-weight: bold;
}
.info-box {
    background-color: #ebf8ff;
    border-left: 4px solid #3182ce;
    padding: 12pt;
    margin: 12pt 0;
}
.warning-box {
    background-color: #fffaf0;
    border-left: 4px solid #ed8936;
    padding: 12pt;
    margin: 12pt 0;
}
.danger-box {
    background-color: #fff5f5;
    border-left: 4px solid #f56565;
    padding: 12pt;
    margin: 12pt 0;
}
ul, ol {
    margin-left: 20pt;
}
</style>
</head>
$html
</html>
HTML;

file_put_contents($outputFile, $wordHtml);

echo "✓ Word document created: $outputFile\n";
echo "\nYou can now:\n";
echo "1. Open this file directly in Microsoft Word\n";
echo "2. Upload to Google Drive and open with Google Docs\n";
echo "3. The formatting will be preserved!\n";

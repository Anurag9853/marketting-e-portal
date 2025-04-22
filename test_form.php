<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Simulate JSON request body
$json_data = [
    'name' => 'John Doe',
    'phone' => '+919876543210',
    'message' => 'I am interested in learning more about your services. Please contact me at your earliest convenience.'
];

// Convert to JSON string
$json_string = json_encode($json_data);

// Override php://input stream
$stream = fopen('php://temp', 'r+');
fwrite($stream, $json_string);
rewind($stream);

// Save original stream
$original_stream = fopen('php://input', 'r');
$original_content = stream_get_contents($original_stream);
fclose($original_stream);

// Override php://input
$GLOBALS['__PHP_INPUT_OVERRIDE'] = $stream;

// Include the submit.php file
require_once 'submit.php';

// Restore original stream
if (isset($GLOBALS['__PHP_INPUT_OVERRIDE'])) {
    fclose($GLOBALS['__PHP_INPUT_OVERRIDE']);
    unset($GLOBALS['__PHP_INPUT_OVERRIDE']);
}
?> 
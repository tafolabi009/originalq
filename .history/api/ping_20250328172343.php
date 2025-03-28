<?php
// Set headers for JSON response and CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Allow GET and preflight OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Send JSON response
echo json_encode([
    'status' => 'success',
    'message' => 'API is running',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
]);

<?php
// Simple endpoint to test API connectivity
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

echo json_encode([
    'status' => 'success',
    'message' => 'API is running',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
]);


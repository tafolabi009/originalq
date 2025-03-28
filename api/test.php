<?php
/**
 * Test script to verify API functionality
 */
require_once 'config.php';
require_once 'database.php';
require_once 'helpers.php';

// Test database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if connection is successful
    if ($conn) {
        $dbStatus = [
            'status' => 'success',
            'message' => 'Database connection successful'
        ];
    } else {
        $dbStatus = [
            'status' => 'error',
            'message' => 'Database connection failed'
        ];
    }
} catch (Exception $e) {
    $dbStatus = [
        'status' => 'error',
        'message' => 'Database connection error: ' . $e->getMessage()
    ];
}

// Test JWT generation
$jwtStatus = [
    'status' => 'success',
    'message' => 'JWT generation successful',
    'token' => generateJWT(['user_id' => 1, 'role' => 'teacher'])
];

// Return test results
echo json_encode([
    'api_status' => 'running',
    'database' => $dbStatus,
    'jwt' => $jwtStatus,
    'environment' => [
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'api_url' => API_URL,
        'frontend_url' => FRONTEND_URL
    ]
]);


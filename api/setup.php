<?php
/**
 * Database setup script
 * 
 * This script creates the necessary database tables if they don't exist
 */
require_once 'config.php';
require_once 'database.php';

try {
    // Create database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read SQL from database.sql file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute SQL statements
    $conn->exec($sql);
    
    // Create uploads directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Create logs directory if it doesn't exist
    $logDir = __DIR__ . '/logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database setup completed successfully!'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database setup failed: ' . $e->getMessage()
    ]);
}


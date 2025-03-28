<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'iqrapath');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', ''); // Change this to your database password

// JWT Secret for authentication
define('JWT_SECRET', 'your_secure_jwt_secret_key_change_this_in_productio');

// API URL for CORS
define('API_URL', 'http://localhost/api');
define('FRONTEND_URL', 'http://localhost:3000');

// Session lifetime in seconds (24 hours)
define('SESSION_LIFETIME', 86400);

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);


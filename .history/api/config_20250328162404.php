<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'iqrapath');
define('DB_USER', 'iqrapath_user'); // Change this to your database username
define('DB_PASS', 'A'); // Change this to your database password

// JWT Secret for authentication
define('JWT_SECRET', '208605d4e88e2fb3c9396b65138634008f521ca1213525e77cde286d39214ec6108d8d6ca5903395c8f5e601261d9669c869a57f50c8b187eeee329508815c235113559bd8f5ed93c2e2b38d8b85728582397402f64772d60d211f9025fa34679cd529ac23655608292b4c14e9ccc8cc013dae7d9b9777e7eba14d98bfa145849e4d8b14b632121dbc8a6ac6be89d572feb8bedaa1225e970a9652530a5307670b798e3ef8937cd1e60b749074cb55ac402e2cda3ad1ac9c03c185c7a75f0f0462c2050b00a24d3a5267b0f13926ad7a173d82b4bf55d226facce8330378f9799099f0582d005b1debe583ad6495a4f2127d4ea57917cdb829bd61e180f37a46');

// API URL for CORS
define('API_URL', 'http://localhost/api');
define('FRONTEND_URL', 'http://localhost:3000');

// Session lifetime in seconds (24 hours)
define('SESSION_LIFETIME', 86400);

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);


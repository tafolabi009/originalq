<?php
/**
* Helper functions for the API
*/
require_once 'config.php';

/**
* Check if user is authenticated
* 
* @return array|bool User data if authenticated, false otherwise
*/
function isAuthenticated() {
   // Get headers in a cross-platform way
   $headers = getAuthHeaders();
   $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
   
   if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
       return false;
   }
   
   $jwt = $matches[1];
   
   // For development/testing, check if it's a mock token
   if (strpos($jwt, 'mock-token-') === 0) {
       return [
           'user_id' => 1,
           'role' => 'teacher'
       ];
   }
   
   try {
       // Verify JWT token
       $tokenParts = explode('.', $jwt);
       if (count($tokenParts) != 3) {
           return false;
       }
       
       $header = base64_decode($tokenParts[0]);
       $payload = base64_decode($tokenParts[1]);
       $signature = $tokenParts[2];
       
       // Check if base64 decode was successful
       if ($header === false || $payload === false) {
           return false;
       }
       
       $payload = json_decode($payload, true);
       if (!is_array($payload)) {
           return false;
       }
       
       // Check if token is expired
       if (isset($payload['exp']) && $payload['exp'] < time()) {
           return false;
       }
       
       return [
           'user_id' => $payload['user_id'],
           'role' => $payload['role']
       ];
   } catch (Exception $e) {
       error_log("JWT validation error: " . $e->getMessage());
       return false;
   }
}

/**
* Get authorization headers in a cross-platform way
* 
* @return array Headers
*/
function getAuthHeaders() {
   $headers = [];
   
   // Try getallheaders() first (Apache)
   if (function_exists('getallheaders')) {
       $headers = getallheaders();
       // Convert to uppercase keys for consistency
       $headers = array_change_key_case($headers, CASE_UPPER);
       // Normalize Authorization header
       if (isset($headers['AUTHORIZATION'])) {
           $headers['Authorization'] = $headers['AUTHORIZATION'];
       }
       return $headers;
   }
   
   // Fallback for servers without getallheaders()
   foreach ($_SERVER as $key => $value) {
       if (substr($key, 0, 5) === 'HTTP_') {
           $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
           $headers[$header] = $value;
       } else if ($key === 'CONTENT_TYPE' || $key === 'CONTENT_LENGTH') {
           $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($key))));
           $headers[$header] = $value;
       }
   }
   
   // Special handling for Authorization header
   if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
       $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
   } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
       $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
   }
   
   return $headers;
}

/**
* Generate a JWT token
* 
* @param array $payload Data to encode in the token
* @return string JWT token
*/
function generateJWT($payload) {
   // Add expiration time
   $payload['exp'] = time() + SESSION_LIFETIME;
   $payload['iat'] = time();
   
   // Create JWT parts
   $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
   $header = base64UrlEncode($header);
   
   $payloadEncoded = json_encode($payload);
   $payloadEncoded = base64UrlEncode($payloadEncoded);
   
   $signature = hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true);
   $signature = base64UrlEncode($signature);
   
   return "$header.$payloadEncoded.$signature";
}

/**
* Base64 URL encode
* 
* @param string $data Data to encode
* @return string Base64 URL encoded data
*/
function base64UrlEncode($data) {
   return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
* Validate required fields in a request
* 
* @param array $requiredFields List of required fields
* @param array $data Request data
* @return bool True if all required fields are present
*/
function validateRequiredFields($requiredFields, $data) {
   foreach ($requiredFields as $field) {
       if (!isset($data[$field]) || empty($data[$field])) {
           return false;
       }
   }
   return true;
}

/**
* Sanitize input data
* 
* @param mixed $data Data to sanitize
* @return mixed Sanitized data
*/
function sanitizeInput($data) {
   if (is_array($data)) {
       foreach ($data as $key => $value) {
           $data[$key] = sanitizeInput($value);
       }
   } else {
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
   }
   
   return $data;
}

/**
* Handle file upload
* 
* @param string $fileInputName Name of the file input field
* @param string $subDirectory Subdirectory to store the file in
* @return array|bool File info if successful, false otherwise
*/
function handleFileUpload($fileInputName, $subDirectory = '') {
   if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
       return false;
   }
   
   $file = $_FILES[$fileInputName];
   
   // Validate file size
   if ($file['size'] > MAX_FILE_SIZE) {
       return false;
   }
   
   // Validate file extension
   $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
   if (!in_array($extension, ALLOWED_EXTENSIONS)) {
       return false;
   }
   
   // Create upload directory if it doesn't exist
   $uploadDir = UPLOAD_DIR;
   if (!empty($subDirectory)) {
       $uploadDir .= $subDirectory . '/';
   }
   
   if (!is_dir($uploadDir)) {
       mkdir($uploadDir, 0755, true);
   }
   
   // Generate unique filename
   $filename = uniqid() . '.' . $extension;
   $destination = $uploadDir . $filename;
   
   // Move uploaded file
   if (move_uploaded_file($file['tmp_name'], $destination)) {
       return [
           'filename' => $filename,
           'path' => $destination,
           'url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $destination),
           'type' => $file['type'],
           'size' => $file['size']
       ];
   }
   
   return false;
}

/**
* Send a JSON response
* 
* @param array $data Data to send
* @param int $statusCode HTTP status code
*/
function sendResponse($data, $statusCode = 200) {
   http_response_code($statusCode);
   echo json_encode($data);
   exit;
}

/**
* Log API request
* 
* @param string $endpoint API endpoint
* @param array $request Request data
* @param array $response Response data
*/
function logApiRequest($endpoint, $request, $response) {
   $logDir = __DIR__ . '/logs/';
   
   if (!is_dir($logDir)) {
       mkdir($logDir, 0755, true);
   }
   
   $logFile = $logDir . date('Y-m-d') . '.log';
   
   $logData = [
       'timestamp' => date('Y-m-d H:i:s'),
       'endpoint' => $endpoint,
       'request' => $request,
       'response' => $response,
       'ip' => $_SERVER['REMOTE_ADDR']
   ];
   
   file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
}

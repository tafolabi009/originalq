<?php
// Set headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Start session
session_start();

// Clear session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie("remember_token", "", time() - 3600, "/");
}

echo json_encode(["success" => true, "message" => "Logged out successfully"]);
?>


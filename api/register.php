<?php
// Set headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
$host = "localhost";
$db_name = "iqrapath";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// If form data is sent instead of JSON
if (empty($data)) {
    $data = new stdClass();
    $data->username = $_POST['username'] ?? null;
    $data->email = $_POST['email'] ?? null;
    $data->password = $_POST['password'] ?? null;
}

// Validate data
if (
    empty($data->username) ||
    empty($data->email) ||
    empty($data->password)
) {
    echo json_encode(["success" => false, "message" => "Please fill all required fields"]);
    exit();
}

// Sanitize input
$username = htmlspecialchars(strip_tags($data->username));
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));

// Check if email already exists
$check_query = "SELECT id FROM users WHERE email = :email LIMIT 1";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bindParam(":email", $email);
$check_stmt->execute();

if ($check_stmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    exit();
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert the user
$query = "INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())";
$stmt = $conn->prepare($query);

$stmt->bindParam(":username", $username);
$stmt->bindParam(":email", $email);
$stmt->bindParam(":password", $password_hash);

try {
    if ($stmt->execute()) {
        // Create a session
        session_start();
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        echo json_encode(["success" => true, "message" => "User registered successfully", "user_id" => $conn->lastInsertId()]);
    } else {
        echo json_encode(["success" => false, "message" => "Unable to register user"]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>


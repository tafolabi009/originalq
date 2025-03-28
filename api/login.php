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
    $data->email = $_POST['email'] ?? null;
    $data->password = $_POST['password'] ?? null;
    $data->remember_me = $_POST['remember_me'] ?? false;
}

// Validate data
if (
    empty($data->email) ||
    empty($data->password)
) {
    echo json_encode(["success" => false, "message" => "Please provide email and password"]);
    exit();
}

// Sanitize input
$email = htmlspecialchars(strip_tags($data->email));
$password = htmlspecialchars(strip_tags($data->password));

// Check if user exists
$query = "SELECT id, username, email, password FROM users WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(":email", $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $username = $row['username'];
    $email = $row['email'];
    $password_hash = $row['password'];
    
    // Verify password
    if (password_verify($password, $password_hash)) {
        // Create a session
        session_start();
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        // Set cookie if remember me is checked
        if ($data->remember_me) {
            $token = bin2hex(random_bytes(32));
            setcookie("remember_token", $token, time() + (86400 * 30), "/"); // 30 days
            
            // Store token in database
            $token_query = "UPDATE users SET remember_token = :token WHERE id = :id";
            $token_stmt = $conn->prepare($token_query);
            $token_stmt->bindParam(":token", $token);
            $token_stmt->bindParam(":id", $id);
            $token_stmt->execute();
        }
        
        echo json_encode([
            "success" => true, 
            "message" => "Login successful",
            "user" => [
                "id" => $id,
                "username" => $username,
                "email" => $email
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
}
?>


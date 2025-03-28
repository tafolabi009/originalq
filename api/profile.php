<?php
// Set headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

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

// Handle GET request - Fetch user profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT u.id, u.username, u.email, u.created_at, 
              t.name, t.phone, t.country, t.city, t.profile_photo, 
              t.subjects, t.experience, t.qualification, t.introduction,
              t.timezone, t.teaching_mode, t.availability, t.currency, t.hourly_rate, t.payment_method
              FROM users u
              LEFT JOIN teacher_profiles t ON u.id = t.user_id
              WHERE u.id = :user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "profile" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
}

// Handle POST request - Update user profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"));
    
    // If form data is sent instead of JSON
    if (empty($data)) {
        $data = new stdClass();
        foreach ($_POST as $key => $value) {
            $data->$key = $value;
        }
    }
    
    // Check if teacher profile exists
    $check_query = "SELECT id FROM teacher_profiles WHERE user_id = :user_id LIMIT 1";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(":user_id", $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing profile
        $query = "UPDATE teacher_profiles SET 
                 name = :name,
                 phone = :phone,
                 country = :country,
                 city = :city,
                 subjects = :subjects,
                 experience = :experience,
                 qualification = :qualification,
                 introduction = :introduction,
                 timezone = :timezone,
                 teaching_mode = :teaching_mode,
                 availability = :availability,
                 currency = :currency,
                 hourly_rate = :hourly_rate,
                 payment_method = :payment_method,
                 updated_at = NOW()
                 WHERE user_id = :user_id";
    } else {
        // Create new profile
        $query = "INSERT INTO teacher_profiles (
                 user_id, name, phone, country, city, subjects, experience, qualification, 
                 introduction, timezone, teaching_mode, availability, currency, hourly_rate, 
                 payment_method, created_at, updated_at
                 ) VALUES (
                 :user_id, :name, :phone, :country, :city, :subjects, :experience, :qualification,
                 :introduction, :timezone, :teaching_mode, :availability, :currency, :hourly_rate,
                 :payment_method, NOW(), NOW()
                 )";
    }
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":name", $data->name ?? null);
    $stmt->bindParam(":phone", $data->phone ?? null);
    $stmt->bindParam(":country", $data->country ?? null);
    $stmt->bindParam(":city", $data->city ?? null);
    $stmt->bindParam(":subjects", $data->subjects ?? null);
    $stmt->bindParam(":experience", $data->experience ?? null);
    $stmt->bindParam(":qualification", $data->qualification ?? null);
    $stmt->bindParam(":introduction", $data->introduction ?? null);
    $stmt->bindParam(":timezone", $data->timezone ?? null);
    $stmt->bindParam(":teaching_mode", $data->teaching_mode ?? null);
    $stmt->bindParam(":availability", $data->availability ?? null);
    $stmt->bindParam(":currency", $data->currency ?? null);
    $stmt->bindParam(":hourly_rate", $data->hourly_rate ?? null);
    $stmt->bindParam(":payment_method", $data->payment_method ?? null);
    
    try {
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Unable to update profile"]);
        }
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
    }
}
?>


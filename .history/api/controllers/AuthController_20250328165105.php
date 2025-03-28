<?php
/**
* Authentication Controller
* 
* Handles user authentication, registration, and password management
*/
class AuthController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Register a new user
     */
    public function register() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Name, email, password, and role are required']);
            return;
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }
        
        // Validate role
        if (!in_array($data['role'], ['student', 'teacher'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role must be either student or teacher']);
            return;
        }
        
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                return;
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $data['role']);
            $stmt->execute();
            
            $userId = $this->db->lastInsertId();
            
            // Create profile based on role
            if ($data['role'] === 'teacher') {
                $stmt = $this->db->prepare("INSERT INTO teacher_profiles (user_id) VALUES (:user_id)");
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare("INSERT INTO student_profiles (user_id) VALUES (:user_id)");
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Generate JWT token
            $token = generateJWT(['user_id' => $userId, 'role' => $data['role']]);
            
            // Get user data
            $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Login user
     */
    public function login() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
            return;
        }
        
        try {
            // Get user by email
            $stmt = $this->db->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (!password_verify($data['password'], $user['password'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
                return;
            }
            
            // Remove password from user data
            unset($user['password']);
            
            // Generate JWT token
            $token = generateJWT(['user_id' => $user['id'], 'role' => $user['role']]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Login failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        // In a stateless API with JWT, there's no server-side session to destroy
        // The client should discard the token
        
        echo json_encode(['status' => 'success', 'message' => 'Logout successful']);
    }
    
    /**
     * Forgot password
     */
    public function forgotPassword() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email is required']);
            return;
        }
        
        try {
            // Check if email exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // Don't reveal that the email doesn't exist for security reasons
                echo json_encode(['status' => 'success', 'message' => 'If your email is registered, you will receive a password reset link']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token in database
            $stmt = $this->db->prepare("UPDATE users SET reset_token = :reset_token, reset_expires = :reset_expires WHERE id = :id");
            $stmt->bindParam(':reset_token', $resetToken);
            $stmt->bindParam(':reset_expires', $resetExpires);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
            
            // In a real application, send an email with the reset link
            // For this example, we'll just return the token
            
            echo json_encode([
                'status' => 'success',
                'message' => 'If your email is registered, you will receive a password reset link',
                'debug_token' => $resetToken // Remove this in production
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Password reset request failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['token']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token and password are required']);
            return;
        }
        
        try {
            // Check if token exists and is valid
            $stmt = $this->db->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()");
            $stmt->bindParam(':token', $data['token']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Hash new password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $this->db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Password reset successful']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Password reset failed: ' . $e->getMessage()]);
        }
    }
}

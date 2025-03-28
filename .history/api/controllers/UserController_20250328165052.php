<?php
/**
* User Controller
* 
* Handles user-related functionality
*/
class UserController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get authenticated user
     */
    public function getUser() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        try {
            // Get user data
            $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'user' => $user]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to get user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update user
     */
    public function updateUser() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['name']) || !isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
            return;
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }
        
        try {
            // Check if email already exists for another user
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                return;
            }
            
            // Update user
            $stmt = $this->db->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            // Get updated user data
            $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully', 'user' => $user]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }
        
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['current_password']) || !isset($data['new_password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Current password and new password are required']);
            return;
        }
        
        try {
            // Get user's current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                return;
            }
            
            // Hash new password
            $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to change password: ' . $e->getMessage()]);
        }
    }
}

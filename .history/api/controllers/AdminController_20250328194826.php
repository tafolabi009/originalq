<?php
/**
* Admin Controller
* 
* Handles admin-specific functionality for managing users, teachers, and students
*/
class AdminController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all users
     */
    public function getUsers() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $role = isset($_GET['role']) ? $_GET['role'] : '';
        
        // Build query
        $query = "SELECT id, name, email, role, created_at FROM users WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if ($role) {
            $query .= " AND role = :role";
            $params[':role'] = $role;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Get users
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        
        $countParams = [];
        
        if ($search) {
            $countQuery .= " AND (name LIKE :search OR email LIKE :search)";
            $countParams[':search'] = "%$search%";
        }
        
        if ($role) {
            $countQuery .= " AND role = :role";
            $countParams[':role'] = $role;
        }
        
        $stmt = $this->db->prepare($countQuery);
        
        foreach ($countParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'status' => 'success',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ]);
    }
    
    /**
     * Get all teachers
     */
    public function getTeachers() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        // Build query
        $query = "
            SELECT t.id, t.user_id, u.name, u.email, t.bio, t.hourly_rate, t.currency, 
                   t.verification_status, t.created_at
            FROM teacher_profiles t
            JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (u.name LIKE :search OR u.email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if ($status) {
            $query .= " AND t.verification_status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Get teachers
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "
            SELECT COUNT(*) as total
            FROM teacher_profiles t
            JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        
        $countParams = [];
        
        if ($search) {
            $countQuery .= " AND (u.name LIKE :search OR u.email LIKE :search)";
            $countParams[':search'] = "%$search%";
        }
        
        if ($status) {
            $countQuery .= " AND t.verification_status = :status";
            $countParams[':status'] = $status;
        }
        
        $stmt = $this->db->prepare($countQuery);
        
        foreach ($countParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'status' => 'success',
            'teachers' => $teachers,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ]);
    }
    
    /**
     * Get all students
     */
    public function getStudents() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Build query
        $query = "
            SELECT s.id, s.user_id, u.name, u.email, s.bio, s.created_at
            FROM student_profiles s
            JOIN users u ON s.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($search) {
            $query .= " AND (u.name LIKE :search OR u.email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $query .= " ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        // Get students
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "
            SELECT COUNT(*) as total
            FROM student_profiles s
            JOIN users u ON s.user_id = u.id
            WHERE 1=1
        ";
        
        $countParams = [];
        
        if ($search) {
            $countQuery .= " AND (u.name LIKE :search OR u.email LIKE :search)";
            $countParams[':search'] = "%$search%";
        }
        
        $stmt = $this->db->prepare($countQuery);
        
        foreach ($countParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo json_encode([
            'status' => 'success',
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ]);
    }
    
    /**
     * Get all pending verification requests
     */
    public function getVerificationRequests() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get pending verification requests
        $stmt = $this->db->prepare("
            SELECT t.id, t.user_id, u.name, u.email, t.verification_status, t.created_at
            FROM teacher_profiles t
            JOIN users u ON t.user_id = u.id
            WHERE t.verification_status = 'pending'
            ORDER BY t.created_at ASC
        ");
        
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'requests' => $requests]);
    }
    
    /**
     * Schedule verification meeting
     */
    public function scheduleVerification() {
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
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['teacher_id']) || !isset($data['scheduled_date']) || !isset($data['meeting_link'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        
        // Check if verification request exists
        $stmt = $this->db->prepare("
            SELECT * FROM teacher_verification_requests
            WHERE teacher_id = :teacher_id
        ");
        
        $stmt->bindParam(':teacher_id', $data['teacher_id']);
        $stmt->execute();
        
        $request = $stmt->fetch();
        
        if ($request) {
            // Update existing request
            $stmt = $this->db->prepare("
                UPDATE teacher_verification_requests
                SET status = 'scheduled',
                    scheduled_date = :scheduled_date,
                    meeting_link = :meeting_link,
                    notes = :notes,
                    updated_at = NOW()
                WHERE teacher_id = :teacher_id
            ");
        } else {
            // Create new request
            $stmt = $this->db->prepare("
                INSERT INTO teacher_verification_requests
                (teacher_id, status, scheduled_date, meeting_link, notes, created_at)
                VALUES (:teacher_id, 'scheduled', :scheduled_date, :meeting_link, :notes, NOW())
            ");
        }
        
        $notes = isset($data['notes']) ? $data['notes'] : null;
        
        $stmt->bindParam(':teacher_id', $data['teacher_id']);
        $stmt->bindParam(':scheduled_date', $data['scheduled_date']);
        $stmt->bindParam(':meeting_link', $data['meeting_link']);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            // Get teacher user ID for notification
            $stmt = $this->db->prepare("
                SELECT user_id FROM teacher_profiles WHERE id = :teacher_id
            ");
            
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                // Send notification to teacher
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, created_at)
                    VALUES (:user_id, :title, :message, :type, NOW())
                ");
                
                $title = 'Verification Meeting Scheduled';
                $message = 'Your verification meeting has been scheduled for ' . $data['scheduled_date'] . '. Please check your email for the meeting link.';
                $type = 'verification';
                
                $stmt->bindParam(':user_id', $teacher['user_id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Verification meeting scheduled successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to schedule verification meeting']);
        }
    }
    
    /**
     * Complete verification process
     */
    public function completeVerification() {
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
        
        // Check if user is an admin
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not an admin']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['teacher_id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        
        // Check if status is valid
        if (!in_array($data['status'], ['verified', 'rejected'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid status. Must be "verified" or "rejected"']);
            return;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update verification request
            $stmt = $this->db->prepare("
                UPDATE teacher_verification_requests
                SET status = 'completed',
                    updated_at = NOW()
                WHERE teacher_id = :teacher_id
            ");
            
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            // Update teacher profile
            $stmt = $this->db->prepare("
                UPDATE teacher_profiles
                SET verification_status = :status,
                    verification_date = NOW(),
                    verification_notes = :notes
                WHERE id = :teacher_id
            ");
            
            $notes = isset($data['notes']) ? $data['notes'] : null;
            
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            // Get teacher user ID for notification
            $stmt = $this->db->prepare("
                SELECT user_id FROM teacher_profiles WHERE id = :teacher_id
            ");
            
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                // Send notification to teacher
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, created_at)
                    VALUES (:user_id, :title, :message, :type, NOW())
                ");
                
                if ($data['status'] === 'verified') {
                    $title = 'Verification Approved';
                    $message = 'Congratulations! Your teacher profile has been verified. You can now start teaching on our platform.';
                } else {
                    $title = 'Verification Rejected';
                    $message = 'Your teacher verification has been rejected. Reason: ' . ($notes ?? 'Not specified');
                }
                
                $type = 'verification';
                
                $stmt->bindParam(':user_id', $teacher['user_id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
            
            // Commit transaction
            $this->db->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Verification process completed successfully']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to complete verification process: ' . $e->getMessage()]);
        }
    }
}

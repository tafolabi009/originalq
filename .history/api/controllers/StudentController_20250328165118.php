<?php
/**
* Student Controller
* 
* Handles student-specific functionality
*/
class StudentController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get student profile
     */
    public function getProfile() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a student
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a student']);
            return;
        }
        
        // Get student profile
        $stmt = $this->db->prepare("SELECT s.*, u.name, u.email, u.role, u.created_at
                                   FROM student_profiles s
                                   JOIN users u ON s.user_id = u.id
                                   WHERE s.user_id = :user_id");
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $student = $stmt->fetch();
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student profile not found']);
            return;
        }
        
        // Get student subjects
        $stmt = $this->db->prepare("SELECT subject_id FROM student_subjects WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student['id']);
        $stmt->execute();
        
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $student['subjects'] = $subjects;
        
        // Get student availability
        $stmt = $this->db->prepare("SELECT day, time_from, time_to FROM student_availability WHERE student_id = :student_id");
        $stmt->bindParam(':student_id', $student['id']);
        $stmt->execute();
        
        $availability = $stmt->fetchAll();
        $student['availability'] = $availability;
        
        echo json_encode(['status' => 'success', 'student' => $student]);
    }
    
    /**
     * Update student profile
     */
    public function updateProfile() {
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
        
        // Check if user is a student
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a student']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['bio']) || !isset($data['subjects'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Bio and subjects are required']);
            return;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update student profile
            $stmt = $this->db->prepare("UPDATE student_profiles 
                                       SET bio = :bio, 
                                           updated_at = NOW()
                                       WHERE user_id = :user_id");
            
            $stmt->bindParam(':bio', $data['bio']);
            $stmt->bindParam(':user_id', $user_id);
            
            $stmt->execute();
            
            // Get student profile ID
            $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $student = $stmt->fetch();
            $student_id = $student['id'];
            
            // Delete existing subjects
            $stmt = $this->db->prepare("DELETE FROM student_subjects WHERE student_id = :student_id");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();
            
            // Insert new subjects
            foreach ($data['subjects'] as $subject) {
                $stmt = $this->db->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (:student_id, :subject_id)");
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':subject_id', $subject);
                $stmt->execute();
            }
            
            // If availability is provided, update it
            if (isset($data['availability'])) {
                // Delete existing availability
                $stmt = $this->db->prepare("DELETE FROM student_availability WHERE student_id = :student_id");
                $stmt->bindParam(':student_id', $student_id);
                $stmt->execute();
                
                // Insert new availability
                foreach ($data['availability'] as $availability) {
                    $stmt = $this->db->prepare("INSERT INTO student_availability (student_id, day, time_from, time_to) 
                                              VALUES (:student_id, :day, :time_from, :time_to)");
                    
                    $stmt->bindParam(':student_id', $student_id);
                    $stmt->bindParam(':day', $availability['day']);
                    $stmt->bindParam(':time_from', $availability['time_from']);
                    $stmt->bindParam(':time_to', $availability['time_to']);
                    
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Student profile updated successfully']);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update student profile', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get dashboard stats
     */
    public function getDashboardStats() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a student
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a student']);
            return;
        }
        
        // Get student profile ID
        $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $student = $stmt->fetch();
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student profile not found']);
            return;
        }
        
        $student_id = $student['id'];
        
        // Get active teachers count
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT teacher_id) as active_teachers
                                   FROM sessions
                                   WHERE student_id = :student_id
                                   AND status = 'completed'
                                   AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        $active_teachers = $stmt->fetch()['active_teachers'];
        
        // Get upcoming sessions count
        $stmt = $this->db->prepare("SELECT COUNT(*) as upcoming_sessions
                                   FROM sessions
                                   WHERE student_id = :student_id
                                   AND status = 'scheduled'
                                   AND session_date >= CURDATE()");
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        $upcoming_sessions = $stmt->fetch()['upcoming_sessions'];
        
        // Get pending requests count
        $stmt = $this->db->prepare("SELECT COUNT(*) as pending_requests
                                   FROM student_requests
                                   WHERE student_id = :student_id
                                   AND status = 'pending'");
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        $pending_requests = $stmt->fetch()['pending_requests'];
        
        // Get total spent
        $stmt = $this->db->prepare("SELECT SUM(amount) as total_spent
                                   FROM payments
                                   WHERE student_id = :student_id
                                   AND status = 'completed'");
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        $total_spent = $stmt->fetch()['total_spent'] ?: 0;
        
        echo json_encode([
            'status' => 'success',
            'stats' => [
                'active_teachers' => (int)$active_teachers,
                'upcoming_sessions' => (int)$upcoming_sessions,
                'pending_requests' => (int)$pending_requests,
                'total_spent' => (float)$total_spent
            ]
        ]);
    }
    
    /**
     * Get teachers
     */
    public function getTeachers() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $subject = isset($_GET['subject']) ? (int)$_GET['subject'] : null;
        $search = isset($_GET['search']) ? $_GET['search'] : null;
        
        // Build query
        $query = "
            SELECT t.id, t.user_id, u.name, t.bio, t.hourly_rate, t.currency, t.verification_status,
                   (SELECT AVG(rating) FROM ratings WHERE teacher_id = t.id) as average_rating,
                   (SELECT COUNT(*) FROM ratings WHERE teacher_id = t.id) as rating_count
            FROM teacher_profiles t
            JOIN users u ON t.user_id = u.id
            WHERE t.verification_status = 'verified'
        ";
        
        $params = [];
        
        // Add subject filter if provided
        if ($subject) {
            $query .= " AND t.id IN (SELECT teacher_id FROM teacher_subjects WHERE subject_id = :subject)";
            $params[':subject'] = $subject;
        }
        
        // Add search filter if provided
        if ($search) {
            $query .= " AND (u.name LIKE :search OR t.bio LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $query .= " ORDER BY average_rating DESC, rating_count DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        try {
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
                WHERE t.verification_status = 'verified'
            ";
            
            $countParams = [];
            
            if ($subject) {
                $countQuery .= " AND t.id IN (SELECT teacher_id FROM teacher_subjects WHERE subject_id = :subject)";
                $countParams[':subject'] = $subject;
            }
            
            if ($search) {
                $countQuery .= " AND (u.name LIKE :search OR t.bio LIKE :search)";
                $countParams[':search'] = "%$search%";
            }
            
            $stmt = $this->db->prepare($countQuery);
            
            foreach ($countParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get subjects for each teacher
            foreach ($teachers as &$teacher) {
                $stmt = $this->db->prepare("
                    SELECT s.id, s.name
                    FROM subjects s
                    JOIN teacher_subjects ts ON s.id = ts.subject_id
                    WHERE ts.teacher_id = :teacher_id
                ");
                
                $stmt->bindParam(':teacher_id', $teacher['id']);
                $stmt->execute();
                $teacher['subjects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'status' => 'success',
                'teachers' => $teachers,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($total / $limit)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to get teachers', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get upcoming sessions
     */
    public function getUpcomingSessions() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a student
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a student']);
            return;
        }
        
        // Get student profile ID
        $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $student = $stmt->fetch();
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student profile not found']);
            return;
        }
        
        $student_id = $student['id'];
        
        // Get query parameters
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        // Get upcoming sessions
        $stmt = $this->db->prepare("
            SELECT s.id, s.teacher_id, s.session_date, s.start_time, s.end_time, s.status,
                   u.name as teacher_name, sub.name as subject_name
            FROM sessions s
            JOIN teacher_profiles tp ON s.teacher_id = tp.id
            JOIN users u ON tp.user_id = u.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.student_id = :student_id
            AND s.status = 'scheduled'
            AND MONTH(s.session_date) = :month
            AND YEAR(s.session_date) = :year
            ORDER BY s.session_date, s.start_time
        ");
        
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'sessions' => $sessions]);
    }
    
    /**
     * Send request to teacher
     */
    public function sendRequest() {
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
        
        // Check if user is a student
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a student']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['teacher_id']) || !isset($data['subject_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Teacher ID and subject ID are required']);
            return;
        }
        
        try {
            // Get student profile ID
            $stmt = $this->db->prepare("SELECT id FROM student_profiles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $student = $stmt->fetch();
            
            if (!$student) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Student profile not found']);
                return;
            }
            
            $student_id = $student['id'];
            
            // Check if teacher exists and is verified
            $stmt = $this->db->prepare("
                SELECT id FROM teacher_profiles 
                WHERE id = :teacher_id AND verification_status = 'verified'
            ");
            
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Teacher not found or not verified']);
                return;
            }
            
            // Check if subject exists
            $stmt = $this->db->prepare("SELECT id FROM subjects WHERE id = :subject_id");
            $stmt->bindParam(':subject_id', $data['subject_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Subject not found']);
                return;
            }
            
            // Insert request
            $stmt = $this->db->prepare("
                INSERT INTO student_requests (
                    student_id, teacher_id, subject_id, message, 
                    preferred_day, preferred_time_from, preferred_time_to
                ) VALUES (
                    :student_id, :teacher_id, :subject_id, :message, 
                    :preferred_day, :preferred_time_from, :preferred_time_to
                )
            ");
            
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->bindParam(':subject_id', $data['subject_id']);
            $stmt->bindParam(':message', $data['message'] ?? null);
            $stmt->bindParam(':preferred_day', $data['preferred_day'] ?? null);
            $stmt->bindParam(':preferred_time_from', $data['preferred_time_from'] ?? null);
            $stmt->bindParam(':preferred_time_to', $data['preferred_time_to'] ?? null);
            
            $stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Request sent successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send request', 'error' => $e->getMessage()]);
        }
    }
}

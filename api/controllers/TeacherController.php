<?php
/**
 * Teacher Controller
 * 
 * Handles teacher-specific functionality
 */
class TeacherController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get teacher profile
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher profile
        $stmt = $this->db->prepare("SELECT t.*, u.name, u.email, u.role, u.created_at
                                   FROM teacher_profiles t
                                   JOIN users u ON t.user_id = u.id
                                   WHERE t.user_id = :user_id");
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        // Get teacher subjects
        $stmt = $this->db->prepare("SELECT subject_id FROM teacher_subjects WHERE teacher_id = :teacher_id");
        $stmt->bindParam(':teacher_id', $teacher['id']);
        $stmt->execute();
        
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $teacher['subjects'] = $subjects;
        
        // Get teacher availability
        $stmt = $this->db->prepare("SELECT day, time_from, time_to FROM teacher_availability WHERE teacher_id = :teacher_id");
        $stmt->bindParam(':teacher_id', $teacher['id']);
        $stmt->execute();
        
        $availability = $stmt->fetchAll();
        $teacher['availability'] = $availability;
        
        echo json_encode(['status' => 'success', 'teacher' => $teacher]);
    }
    
    /**
     * Update teacher profile
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['bio']) || !isset($data['hourly_rate']) || !isset($data['subjects']) || !isset($data['availability'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Update teacher profile
            $stmt = $this->db->prepare("UPDATE teacher_profiles 
                                       SET bio = :bio, 
                                           hourly_rate = :hourly_rate, 
                                           currency = :currency,
                                           payment_method = :payment_method,
                                           updated_at = NOW()
                                       WHERE user_id = :user_id");
            
            $stmt->bindParam(':bio', $data['bio']);
            $stmt->bindParam(':hourly_rate', $data['hourly_rate']);
            $stmt->bindParam(':currency', $data['currency']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':user_id', $user_id);
            
            $stmt->execute();
            
            // Get teacher profile ID
            $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $teacher = $stmt->fetch();
            $teacher_id = $teacher['id'];
            
            // Delete existing subjects
            $stmt = $this->db->prepare("DELETE FROM teacher_subjects WHERE teacher_id = :teacher_id");
            $stmt->bindParam(':teacher_id', $teacher_id);
            $stmt->execute();
            
            // Insert new subjects
            foreach ($data['subjects'] as $subject) {
                $stmt = $this->db->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (:teacher_id, :subject_id)");
                $stmt->bindParam(':teacher_id', $teacher_id);
                $stmt->bindParam(':subject_id', $subject);
                $stmt->execute();
            }
            
            // Delete existing availability
            $stmt = $this->db->prepare("DELETE FROM teacher_availability WHERE teacher_id = :teacher_id");
            $stmt->bindParam(':teacher_id', $teacher_id);
            $stmt->execute();
            
            // Insert new availability
            foreach ($data['availability'] as $availability) {
                $stmt = $this->db->prepare("INSERT INTO teacher_availability (teacher_id, day, time_from, time_to) 
                                           VALUES (:teacher_id, :day, :time_from, :time_to)");
                
                $stmt->bindParam(':teacher_id', $teacher_id);
                $stmt->bindParam(':day', $availability['day']);
                $stmt->bindParam(':time_from', $availability['time_from']);
                $stmt->bindParam(':time_to', $availability['time_to']);
                
                $stmt->execute();
            }
            
            // Commit transaction
            $this->db->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Teacher profile updated successfully']);
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->db->rollBack();
            
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update teacher profile', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get teacher verification status
     */
    public function getVerificationStatus() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher verification status
        $stmt = $this->db->prepare("SELECT verification_status FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        echo json_encode(['status' => 'success', 'verification_status' => $teacher['verification_status']]);
    }
    
    /**
     * Update teacher verification status (admin only)
     */
    public function updateVerificationStatus() {
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
        if (!isset($data['teacher_id']) || !isset($data['verification_status'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        
        // Update teacher verification status
        $stmt = $this->db->prepare("UPDATE teacher_profiles 
                                   SET verification_status = :verification_status,
                                       updated_at = NOW()
                                   WHERE id = :teacher_id");
        
        $stmt->bindParam(':verification_status', $data['verification_status']);
        $stmt->bindParam(':teacher_id', $data['teacher_id']);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Teacher verification status updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update teacher verification status']);
        }
    }
    
    /**
     * Get teacher dashboard stats
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher profile ID
        $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $teacher['id'];
        
        // Get active students count
        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT student_id) as active_students
                                   FROM sessions
                                   WHERE teacher_id = :teacher_id
                                   AND status = 'completed'
                                   AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $active_students = $stmt->fetch()['active_students'];
        
        // Get upcoming sessions count
        $stmt = $this->db->prepare("SELECT COUNT(*) as upcoming_sessions
                                   FROM sessions
                                   WHERE teacher_id = :teacher_id
                                   AND status = 'scheduled'
                                   AND session_date >= CURDATE()");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $upcoming_sessions = $stmt->fetch()['upcoming_sessions'];
        
        // Get pending requests count
        $stmt = $this->db->prepare("SELECT COUNT(*) as pending_requests
                                   FROM student_requests
                                   WHERE teacher_id = :teacher_id
                                   AND status = 'pending'");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $pending_requests = $stmt->fetch()['pending_requests'];
        
        // Get total earnings
        $stmt = $this->db->prepare("SELECT SUM(amount) as total_earnings
                                   FROM payments
                                   WHERE teacher_id = :teacher_id
                                   AND status = 'completed'");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $total_earnings = $stmt->fetch()['total_earnings'] ?: 0;
        
        echo json_encode([
            'status' => 'success',
            'stats' => [
                'active_students' => (int)$active_students,
                'upcoming_sessions' => (int)$upcoming_sessions,
                'pending_requests' => (int)$pending_requests,
                'total_earnings' => (float)$total_earnings
            ]
        ]);
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher profile ID
        $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $teacher['id'];
        
        // Get query parameters
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        // Get upcoming sessions
        $stmt = $this->db->prepare("SELECT s.id, s.student_id, s.session_date, s.start_time, s.end_time, s.status,
                                   u.name as student_name
                                   FROM sessions s
                                   JOIN student_profiles sp ON s.student_id = sp.id
                                   JOIN users u ON sp.user_id = u.id
                                   WHERE s.teacher_id = :teacher_id
                                   AND s.status = 'scheduled'
                                   AND MONTH(s.session_date) = :month
                                   AND YEAR(s.session_date) = :year
                                   ORDER BY s.session_date, s.start_time");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $sessions = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'sessions' => $sessions]);
    }
    
    /**
     * Get recommended students
     */
    public function getRecommendedStudents() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher profile ID and subjects
        $stmt = $this->db->prepare("SELECT tp.id, ts.subject_id
                                   FROM teacher_profiles tp
                                   JOIN teacher_subjects ts ON tp.id = ts.teacher_id
                                   WHERE tp.user_id = :user_id");
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        
        if (empty($results)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $results[0]['id'];
        $subjects = array_column($results, 'subject_id');
        
        // Get students looking for these subjects
        $placeholders = str_repeat('?,', count($subjects) - 1) . '?';
        
        $query = "SELECT DISTINCT sp.id, u.name, sp.bio, sp.hourly_rate, sp.currency,
                 ss.subject_id, s.name as subject_name,
                 sa.day, sa.time_from, sa.time_to
                 FROM student_profiles sp
                 JOIN users u ON sp.user_id = u.id
                 JOIN student_subjects ss ON sp.id = ss.student_id
                 JOIN subjects s ON ss.subject_id = s.id
                 JOIN student_availability sa ON sp.id = sa.student_id
                 WHERE ss.subject_id IN ($placeholders)
                 AND sp.id NOT IN (
                     SELECT DISTINCT student_id FROM sessions WHERE teacher_id = ?
                 )
                 LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        
        // Bind subject IDs
        foreach ($subjects as $i => $subject_id) {
            $stmt->bindValue($i + 1, $subject_id);
        }
        
        // Bind teacher ID
        $stmt->bindValue(count($subjects) + 1, $teacher_id);
        
        $stmt->execute();
        
        $students = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'students' => $students]);
    }
    
    /**
     * Get student requests
     */
    public function getStudentRequests() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get teacher profile ID
        $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $teacher['id'];
        
        // Get student requests
        $stmt = $this->db->prepare("SELECT sr.id, sr.student_id, sr.subject_id, sr.message, sr.preferred_day, 
                                   sr.preferred_time_from, sr.preferred_time_to, sr.status, sr.created_at,
                                   u.name as student_name, s.name as subject_name
                                   FROM student_requests sr
                                   JOIN student_profiles sp ON sr.student_id = sp.id
                                   JOIN users u ON sp.user_id = u.id
                                   JOIN subjects s ON sr.subject_id = s.id
                                   WHERE sr.teacher_id = :teacher_id
                                   ORDER BY sr.created_at DESC");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $requests = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'requests' => $requests]);
    }
    
    /**
     * Accept student request
     */
    public function acceptStudentRequest() {
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['request_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Request ID is required']);
            return;
        }
        
        $request_id = $data['request_id'];
        
        // Get teacher profile ID
        $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $teacher['id'];
        
        // Check if request exists and belongs to this teacher
        $stmt = $this->db->prepare("SELECT * FROM student_requests 
                                   WHERE id = :request_id AND teacher_id = :teacher_id");
        
        $stmt->bindParam(':request_id', $request_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $request = $stmt->fetch();
        
        if (!$request) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Request not found or does not belong to this teacher']);
            return;
        }
        
        // Update request status
        $stmt = $this->db->prepare("UPDATE student_requests 
                                   SET status = 'accepted', 
                                       updated_at = NOW()
                                   WHERE id = :request_id");
        
        $stmt->bindParam(':request_id', $request_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Request accepted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to accept request']);
        }
    }
    
    /**
     * Decline student request
     */
    public function declineStudentRequest() {
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
        
        // Check if user is a teacher
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user['role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not a teacher']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['request_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Request ID is required']);
            return;
        }
        
        $request_id = $data['request_id'];
        
        // Get teacher profile ID
        $stmt = $this->db->prepare("SELECT id FROM teacher_profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $teacher = $stmt->fetch();
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Teacher profile not found']);
            return;
        }
        
        $teacher_id = $teacher['id'];
        
        // Check if request exists and belongs to this teacher
        $stmt = $this->db->prepare("SELECT * FROM student_requests 
                                   WHERE id = :request_id AND teacher_id = :teacher_id");
        
        $stmt->bindParam(':request_id', $request_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $request = $stmt->fetch();
        
        if (!$request) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Request not found or does not belong to this teacher']);
            return;
        }
        
        // Update request status
        $stmt = $this->db->prepare("UPDATE student_requests 
                                   SET status = 'declined', 
                                       updated_at = NOW()
                                   WHERE id = :request_id");
        
        $stmt->bindParam(':request_id', $request_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Request declined successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to decline request']);
        }
    }
}


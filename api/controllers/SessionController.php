<?php
/**
 * Session Controller
 * 
 * Handles session-related functionality like scheduling, updating, and canceling sessions
 */
class SessionController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get sessions for a user (teacher or student)
     */
    public function getSessions() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        $role = $auth['role'];
        
        // Get query parameters
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
        $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : null;
        
        // Build query based on user role
        if ($role === 'teacher') {
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
            
            // Build query
            $query = "SELECT s.id, s.student_id, s.subject_id, s.session_date, s.start_time, s.end_time, 
                             s.status, s.meeting_link, s.notes, s.created_at,
                             u.name as student_name, sub.name as subject_name
                      FROM sessions s
                      JOIN student_profiles sp ON s.student_id = sp.id
                      JOIN users u ON sp.user_id = u.id
                      JOIN subjects sub ON s.subject_id = sub.id
                      WHERE s.teacher_id = :teacher_id";
            
            $params = [':teacher_id' => $teacher_id];
            
        } else if ($role === 'student') {
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
            
            // Build query
            $query = "SELECT s.id, s.teacher_id, s.subject_id, s.session_date, s.start_time, s.end_time, 
                             s.status, s.meeting_link, s.notes, s.created_at,
                             u.name as teacher_name, sub.name as subject_name
                      FROM sessions s
                      JOIN teacher_profiles tp ON s.teacher_id = tp.id
                      JOIN users u ON tp.user_id = u.id
                      JOIN subjects sub ON s.subject_id = sub.id
                      WHERE s.student_id = :student_id";
            
            $params = [':student_id' => $student_id];
            
        } else {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }
        
        // Add filters
        if ($status) {
            $query .= " AND s.status = :status";
            $params[':status'] = $status;
        }
        
        if ($from_date) {
            $query .= " AND s.session_date >= :from_date";
            $params[':from_date'] = $from_date;
        }
        
        if ($to_date) {
            $query .= " AND s.session_date <= :to_date";
            $params[':to_date'] = $to_date;
        }
        
        // Order by date and time
        $query .= " ORDER BY s.session_date, s.start_time";
        
        // Execute query
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'sessions' => $sessions]);
    }
    
    /**
     * Create a new session
     */
    public function createSession() {
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
        $role = $auth['role'];
        
        // Only teachers can create sessions
        if ($role !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Only teachers can create sessions']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['student_id']) || !isset($data['subject_id']) || !isset($data['session_date']) || 
            !isset($data['start_time']) || !isset($data['end_time'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
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
        
        // Check if teacher teaches this subject
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM teacher_subjects 
                                   WHERE teacher_id = :teacher_id AND subject_id = :subject_id");
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':subject_id', $data['subject_id']);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'You do not teach this subject']);
            return;
        }
        
        // Check if teacher is available at this time
        $day_of_week = date('l', strtotime($data['session_date']));
        $day_of_week = strtolower($day_of_week);
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM teacher_availability 
                                   WHERE teacher_id = :teacher_id 
                                   AND day = :day 
                                   AND time_from <= :start_time 
                                   AND time_to >= :end_time");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':day', $day_of_week);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'You are not available at this time']);
            return;
        }
        
        // Check if teacher has another session at this time
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM sessions 
                                   WHERE teacher_id = :teacher_id 
                                   AND session_date = :session_date 
                                   AND ((start_time <= :start_time AND end_time > :start_time) 
                                        OR (start_time < :end_time AND end_time >= :end_time)
                                        OR (start_time >= :start_time AND end_time <= :end_time))
                                   AND status != 'cancelled'");
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':session_date', $data['session_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'You already have a session scheduled at this time']);
            return;
        }
        
        // Create session
        $stmt = $this->db->prepare("INSERT INTO sessions (teacher_id, student_id, subject_id, session_date, 
                                   start_time, end_time, status, meeting_link, notes, created_at)
                                   VALUES (:teacher_id, :student_id, :subject_id, :session_date, 
                                   :start_time, :end_time, :status, :meeting_link, :notes, NOW())");
        
        $status = 'scheduled';
        $meeting_link = isset($data['meeting_link']) ? $data['meeting_link'] : null;
        $notes = isset($data['notes']) ? $data['notes'] : null;
        
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject_id', $data['subject_id']);
        $stmt->bindParam(':session_date', $data['session_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':meeting_link', $meeting_link);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            $session_id = $this->db->lastInsertId();
            
            // Get student user ID for notification
            $stmt = $this->db->prepare("SELECT user_id FROM student_profiles WHERE id = :student_id");
            $stmt->bindParam(':student_id', $data['student_id']);
            $stmt->execute();
            
            $student = $stmt->fetch();
            
            if ($student) {
                // Send notification to student
                $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at)
                                           VALUES (:user_id, :title, :message, :type, NOW())");
                
                $title = 'New Session Scheduled';
                $message = 'A new session has been scheduled for you on ' . $data['session_date'] . ' at ' . $data['start_time'];
                $type = 'booking';
                
                $stmt->bindParam(':user_id', $student['user_id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Session created successfully',
                'session_id' => $session_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create session']);
        }
    }
    
    /**
     * Update a session
     */
    public function updateSession() {
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
        $role = $auth['role'];
        
        // Only teachers can update sessions
        if ($role !== 'teacher') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Only teachers can update sessions']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['session_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Session ID is required']);
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
        
        // Check if session exists and belongs to this teacher
        $stmt = $this->db->prepare("SELECT * FROM sessions 
                                   WHERE id = :session_id AND teacher_id = :teacher_id");
        
        $stmt->bindParam(':session_id', $data['session_id']);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        
        $session = $stmt->fetch();
        
        if (!$session) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Session not found or does not belong to this teacher']);
            return;
        }
        
        // Check if session is already completed or cancelled
        if ($session['status'] === 'completed' || $session['status'] === 'cancelled') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot update a completed or cancelled session']);
            return;
        }
        
        // Build update query
        $updateFields = [];
        $params = [
            ':session_id' => $data['session_id']
        ];
        
        if (isset($data['session_date'])) {
            $updateFields[] = "session_date = :session_date";
            $params[':session_date'] = $data['session_date'];
        }
        
        if (isset($data['start_time'])) {
            $updateFields[] = "start_time = :start_time";
            $params[':start_time'] = $data['start_time'];
        }
        
        if (isset($data['end_time'])) {
            $updateFields[] = "end_time = :end_time";
            $params[':end_time'] = $data['end_time'];
        }
        
        if (isset($data['status'])) {
            $updateFields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        
        if (isset($data['meeting_link'])) {
            $updateFields[] = "meeting_link = :meeting_link";
            $params[':meeting_link'] = $data['meeting_link'];
        }
        
        if (isset($data['notes'])) {
            $updateFields[] = "notes = :notes";
            $params[':notes'] = $data['notes'];
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
            return;
        }
        
        // If date or time is being updated, check teacher availability
        if (isset($data['session_date']) || isset($data['start_time']) || isset($data['end_time'])) {
            $session_date = isset($data['session_date']) ? $data['session_date'] : $session['session_date'];
            $start_time = isset($data['start_time']) ? $data['start_time'] : $session['start_time'];
            $end_time = isset($data['end_time']) ? $data['end_time'] : $session['end_time'];
            
            $day_of_week = date('l', strtotime($session_date));
            $day_of_week = strtolower($day_of_week);
            
            // Check if teacher is available at this time
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM teacher_availability 
                                       WHERE teacher_id = :teacher_id 
                                       AND day = :day 
                                       AND time_from <= :start_time 
                                       AND time_to >= :end_time");
            
            $stmt->bindParam(':teacher_id', $teacher_id);
            $stmt->bindParam(':day', $day_of_week);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->execute();
            
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'You are not available at this time']);
                return;
            }
            
            // Check if teacher has another session at this time (excluding this session)
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM sessions 
                                       WHERE teacher_id = :teacher_id 
                                       AND session_date = :session_date 
                                       AND ((start_time <= :start_time AND end_time > :start_time) 
                                            OR (start_time < :end_time AND end_time >= :end_time)
                                            OR (start_time >= :start_time AND end_time <= :end_time))
                                       AND status != 'cancelled'
                                       AND id != :session_id");
            
            $stmt->bindParam(':teacher_id', $teacher_id);
            $stmt->bindParam(':session_date', $session_date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':session_id', $data['session_id']);
            $stmt->execute();
            
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'You already have a session scheduled at this time']);
                return;
            }
        }
        
        // Update session
        $updateFields[] = "updated_at = NOW()";
        $query = "UPDATE sessions SET " . implode(", ", $updateFields) . " WHERE id = :session_id";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            // Get student user ID for notification
            $stmt = $this->db->prepare("SELECT sp.user_id FROM sessions s
                                       JOIN student_profiles sp ON s.student_id = sp.id
                                       WHERE s.id = :session_id");
            $stmt->bindParam(':session_id', $data['session_id']);
            $stmt->execute();
            
            $student = $stmt->fetch();
            
            if ($student) {
                // Send notification to student
                $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at)
                                           VALUES (:user_id, :title, :message, :type, NOW())");
                
                $title = 'Session Updated';
                $message = 'Your session has been updated. Please check your schedule for details.';
                $type = 'booking';
                
                $stmt->bindParam(':user_id', $student['user_id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Session updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update session']);
        }
    }
    
    /**
     * Cancel a session
     */
    public function cancelSession() {
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
        $role = $auth['role'];
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['session_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Session ID is required']);
            return;
        }
        
        // Check if session exists
        $stmt = $this->db->prepare("SELECT s.*, tp.user_id as teacher_user_id, sp.user_id as student_user_id 
                                   FROM sessions s
                                   JOIN teacher_profiles tp ON s.teacher_id = tp.id
                                   JOIN student_profiles sp ON s.student_id = sp.id
                                   WHERE s.id = :session_id");
        
        $stmt->bindParam(':session_id', $data['session_id']);
        $stmt->execute();
        
        $session = $stmt->fetch();
        
        if (!$session) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Session not found']);
            return;
        }
        
        // Check if user is the teacher or student of this session
        if ($role === 'teacher' && $session['teacher_user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. You are not the teacher of this session']);
            return;
        }
        
        if ($role === 'student' && $session['student_user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. You are not the student of this session']);
            return;
        }
        
        // Check if session is already completed or cancelled
        if ($session['status'] === 'completed' || $session['status'] === 'cancelled') {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot cancel a completed or already cancelled session']);
            return;
        }
        
        // Cancel session
        $stmt = $this->db->prepare("UPDATE sessions SET status = 'cancelled', updated_at = NOW() WHERE id = :session_id");
        $stmt->bindParam(':session_id', $data['session_id']);
        
        if ($stmt->execute()) {
            // Send notification to the other party
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at)
                                       VALUES (:user_id, :title, :message, :type, NOW())");
            
            $title = 'Session Cancelled';
            $message = 'A session scheduled for ' . $session['session_date'] . ' at ' . $session['start_time'] . ' has been cancelled.';
            $type = 'booking';
            
            if ($role === 'teacher') {
                // Send notification to student
                $stmt->bindParam(':user_id', $session['student_user_id']);
            } else {
                // Send notification to teacher
                $stmt->bindParam(':user_id', $session['teacher_user_id']);
            }
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Session cancelled successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to cancel session']);
        }
    }
}

<?php
/**
* Admin Controller
* 
* Handles admin-specific functionality for teacher verification
*/
class AdminController {
   private $db;
   
   public function __construct($db) {
       $this->db = $db;
   }
   
   /**
    * Get all pending verification requests
    */
   public function getPendingVerifications() {
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
    * Approve teacher verification
    */
   public function approveVerification() {
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
       if (!isset($data['teacher_id'])) {
           http_response_code(400);
           echo json_encode(['status' => 'error', 'message' => 'Teacher ID is required']);
           return;
       }
       
       // Update teacher verification status
       $stmt = $this->db->prepare("
           UPDATE teacher_profiles
           SET verification_status = 'verified',
               verification_date = NOW()
           WHERE id = :teacher_id
       ");
       
       $stmt->bindParam(':teacher_id', $data['teacher_id']);
       
       if ($stmt->execute()) {
           // Get teacher's user ID
           $stmt = $this->db->prepare("
               SELECT user_id FROM teacher_profiles WHERE id = :teacher_id
           ");
           
           $stmt->bindParam(':teacher_id', $data['teacher_id']);
           $stmt->execute();
           
           $teacher = $stmt->fetch();
           
           // Send notification to teacher
           if ($teacher) {
               $this->sendVerificationNotification($teacher['user_id'], 'approved');
           }
           
           echo json_encode(['status' => 'success', 'message' => 'Teacher verification approved successfully']);
       } else {
           http_response_code(500);
           echo json_encode(['status' => 'error', 'message' => 'Failed to approve teacher verification']);
       }
   }
   
   /**
    * Reject teacher verification
    */
   public function rejectVerification() {
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
       if (!isset($data['teacher_id']) || !isset($data['reason'])) {
           http_response_code(400);
           echo json_encode(['status' => 'error', 'message' => 'Teacher ID and reason are required']);
           return;
       }
       
       // Update teacher verification status
       $stmt = $this->db->prepare("
           UPDATE teacher_profiles
           SET verification_status = 'rejected',
               verification_notes = :reason,
               verification_date = NOW()
           WHERE id = :teacher_id
       ");
       
       $stmt->bindParam(':teacher_id', $data['teacher_id']);
       $stmt->bindParam(':reason', $data['reason']);
       
       if ($stmt->execute()) {
           // Get teacher's user ID
           $stmt = $this->db->prepare("
               SELECT user_id FROM teacher_profiles WHERE id = :teacher_id
           ");
           
           $stmt->bindParam(':teacher_id', $data['teacher_id']);
           $stmt->execute();
           
           $teacher = $stmt->fetch();
           
           // Send notification to teacher
           if ($teacher) {
               $this->sendVerificationNotification($teacher['user_id'], 'rejected', $data['reason']);
           }
           
           echo json_encode(['status' => 'success', 'message' => 'Teacher verification rejected successfully']);
       } else {
           http_response_code(500);
           echo json_encode(['status' => 'error', 'message' => 'Failed to reject teacher verification']);
       }
   }
   
   /**
    * Send verification notification to teacher
    */
   private function sendVerificationNotification($user_id, $status, $reason = '') {
       $title = $status === 'approved' ? 'Verification Approved' : 'Verification Rejected';
       $message = $status === 'approved' 
           ? 'Congratulations! Your teacher profile has been verified. You can now start teaching on our platform.'
           : 'Your teacher verification has been rejected. Reason: ' . $reason;
       
       $stmt = $this->db->prepare("
           INSERT INTO notifications (user_id, title, message, created_at)
           VALUES (:user_id, :title, :message, NOW())
       ");
       
       $stmt->bindParam(':user_id', $user_id);
       $stmt->bindParam(':title', $title);
       $stmt->bindParam(':message', $message);
       $stmt->execute();
       
       // In a real application, you might also send an email or push notification here
   }
   
   /**
    * Get all teachers
    */
   public function getAllTeachers() {
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
}


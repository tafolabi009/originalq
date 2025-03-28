<?php
/**
 * Payment Controller
 * 
 * Handles payment-related functionality
 */
class PaymentController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get payments for a user (teacher or student)
     */
    public function getPayments() {
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
            $query = "SELECT p.id, p.session_id, p.student_id, p.amount, p.currency, p.status, 
                             p.payment_method, p.transaction_id, p.created_at,
                             u.name as student_name, s.session_date, s.start_time, s.end_time
                      FROM payments p
                      LEFT JOIN sessions s ON p.session_id = s.id
                      JOIN student_profiles sp ON p.student_id = sp.id
                      JOIN users u ON sp.user_id = u.id
                      WHERE p.teacher_id = :teacher_id";
            
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
            $query = "SELECT p.id, p.session_id, p.teacher_id, p.amount, p.currency, p.status, 
                             p.payment_method, p.transaction_id, p.created_at,
                             u.name as teacher_name, s.session_date, s.start_time, s.end_time
                      FROM payments p
                      LEFT JOIN sessions s ON p.session_id = s.id
                      JOIN teacher_profiles tp ON p.teacher_id = tp.id
                      JOIN users u ON tp.user_id = u.id
                      WHERE p.student_id = :student_id";
            
            $params = [':student_id' => $student_id];
            
        } else {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }
        
        // Add filters
        if ($status) {
            $query .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        
        if ($from_date) {
            $query .= " AND p.created_at >= :from_date";
            $params[':from_date'] = $from_date . ' 00:00:00';
        }
        
        if ($to_date) {
            $query .= " AND p.created_at <= :to_date";
            $params[':to_date'] = $to_date . ' 23:59:59';
        }
        
        // Order by date
        $query .= " ORDER BY p.created_at DESC";
        
        // Execute query
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'payments' => $payments]);
    }
    
    /**
     * Create a new payment
     */
    public function createPayment() {
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
        
        // Only students can create payments
        if ($role !== 'student') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access denied. Only students can create payments']);
            return;
        }
        
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['teacher_id']) || !isset($data['amount']) || !isset($data['currency'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
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
        
        // Create payment
        $stmt = $this->db->prepare("INSERT INTO payments (session_id, teacher_id, student_id, amount, currency, 
                                   status, payment_method, transaction_id, created_at)
                                   VALUES (:session_id, :teacher_id, :student_id, :amount, :currency, 
                                   :status, :payment_method, :transaction_id, NOW())");
        
        $session_id = isset($data['session_id']) ? $data['session_id'] : null;
        $status = 'pending';
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : null;
        $transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : null;
        
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':teacher_id', $data['teacher_id']);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':transaction_id', $transaction_id);
        
        if ($stmt->execute()) {
            $payment_id = $this->db->lastInsertId();
            
            // Get teacher user ID for notification
            $stmt = $this->db->prepare("SELECT user_id FROM teacher_profiles WHERE id = :teacher_id");
            $stmt->bindParam(':teacher_id', $data['teacher_id']);
            $stmt->execute();
            
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                // Send notification to teacher
                $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at)
                                           VALUES (:user_id, :title, :message, :type, NOW())");
                
                $title = 'New Payment Received';
                $message = 'You have received a new payment of ' . $data['amount'] . ' ' . $data['currency'];
                $type = 'payment';
                
                $stmt->bindParam(':user_id', $teacher['user_id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
            }
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Payment created successfully',
                'payment_id' => $payment_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create payment']);
        }
    }
}


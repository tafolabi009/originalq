<?php
/**
* Message Controller
* 
* Handles messaging functionality
*/
class MessageController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get messages
     */
    public function getMessages() {
        // Check if user is authenticated
        $auth = isAuthenticated();
        
        if (!$auth) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $user_id = $auth['user_id'];
        
        // Get query parameters
        $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;
        
        try {
            // If conversation_id is provided, get messages for that conversation
            if ($conversation_id) {
                // Check if user is part of the conversation
                $stmt = $this->db->prepare("
                    SELECT c.id
                    FROM conversations c
                    WHERE c.id = :conversation_id
                    AND (c.user1_id = :user_id OR c.user2_id = :user_id)
                ");
                
                $stmt->bindParam(':conversation_id', $conversation_id);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not part of this conversation']);
                    return;
                }
                
                // Get messages
                $stmt = $this->db->prepare("
                    SELECT m.id, m.conversation_id, m.sender_id, m.message, m.is_read, m.created_at,
                           u.name as sender_name
                    FROM messages m
                    JOIN users u ON m.sender_id = u.id
                    WHERE m.conversation_id = :conversation_id
                    ORDER BY m.created_at DESC
                    LIMIT :limit OFFSET :offset
                ");
                
                $stmt->bindParam(':conversation_id', $conversation_id);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Mark messages as read if user is not the sender
                $stmt = $this->db->prepare("
                    UPDATE messages
                    SET is_read = 1
                    WHERE conversation_id = :conversation_id
                    AND sender_id != :user_id
                    AND is_read = 0
                ");
                
                $stmt->bindParam(':conversation_id', $conversation_id);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Get total count
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM messages
                    WHERE conversation_id = :conversation_id
                ");
                
                $stmt->bindParam(':conversation_id', $conversation_id);
                $stmt->execute();
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                echo json_encode([
                    'status' => 'success',
                    'messages' => $messages,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => ceil($total / $limit)
                ]);
                
            } else {
                // Get all conversations for the user
                $stmt = $this->db->prepare("
                    SELECT c.id, c.user1_id, c.user2_id, c.created_at,
                           CASE WHEN c.user1_id = :user_id THEN c.user2_id ELSE c.user1_id END as other_user_id,
                           CASE WHEN c.user1_id = :user_id THEN u2.name ELSE u1.name END as other_user_name,
                           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != :user_id AND is_read = 0) as unread_count,
                           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
                    FROM conversations c
                    JOIN users u1 ON c.user1_id = u1.id
                    JOIN users u2 ON c.user2_id = u2.id
                    WHERE c.user1_id = :user_id OR c.user2_id = :user_id
                    ORDER BY last_message_time DESC
                    LIMIT :limit OFFSET :offset
                ");
                
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get total count
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total
                    FROM conversations
                    WHERE user1_id = :user_id OR user2_id = :user_id
                ");
                
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                echo json_encode([
                    'status' => 'success',
                    'conversations' => $conversations,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'totalPages' => ceil($total / $limit)
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to get messages', 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Send message
     */
    public function sendMessage() {
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
        if ((!isset($data['conversation_id']) && !isset($data['recipient_id'])) || !isset($data['message'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Conversation ID or recipient ID, and message are required']);
            return;
        }
        
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            $conversation_id = null;
            
            // If conversation_id is provided, check if user is part of the conversation
            if (isset($data['conversation_id'])) {
                $stmt = $this->db->prepare("
                    SELECT id
                    FROM conversations
                    WHERE id = :conversation_id
                    AND (user1_id = :user_id OR user2_id = :user_id)
                ");
                
                $stmt->bindParam(':conversation_id', $data['conversation_id']);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Access denied. User is not part of this conversation']);
                    return;
                }
                
                $conversation_id = $data['conversation_id'];
            }
            // If recipient_id is provided, check if conversation exists or create a new one
            else if (isset($data['recipient_id'])) {
                // Check if recipient exists
                $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :recipient_id");
                $stmt->bindParam(':recipient_id', $data['recipient_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Recipient not found']);
                    return;
                }
                
                // Check if conversation already exists
                $stmt = $this->db->prepare("
                    SELECT id
                    FROM conversations
                    WHERE (user1_id = :user_id AND user2_id = :recipient_id)
                    OR (user1_id = :recipient_id AND user2_id = :user_id)
                ");
                
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':recipient_id', $data['recipient_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $conversation_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
                } else {
                    // Create new conversation
                    $stmt = $this->db->prepare("
                        INSERT INTO conversations (user1_id, user2_id)
                        VALUES (:user_id, :recipient_id)
                    ");
                    
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':recipient_id', $data['recipient_id']);
                    $stmt->execute();
                    
                    $conversation_id = $this->db->lastInsertId();
                }
            }
            
            // Insert message
            $stmt = $this->db->prepare("
                INSERT INTO messages (conversation_id, sender_id, message)
                VALUES (:conversation_id, :sender_id, :message)
            ");
            
            $stmt->bindParam(':conversation_id', $conversation_id);
            $stmt->bindParam(':sender_id', $user_id);
            $stmt->bindParam(':message', $data['message']);
            $stmt->execute();
            
            $message_id = $this->db->lastInsertId();
            
            // Get message data
            $stmt = $this->db->prepare("
                SELECT m.id, m.conversation_id, m.sender_id, m.message, m.is_read, m.created_at,
                       u.name as sender_name
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.id = :message_id
            ");
            
            $stmt->bindParam(':message_id', $message_id);
            $stmt->execute();
            
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Commit transaction
            $this->db->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Message sent successfully', 'data' => $message]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send message', 'error' => $e->getMessage()]);
        }
    }
}

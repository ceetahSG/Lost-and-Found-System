<?php
class Message {
    private $conn;
    private $table = 'messages';

    public $id;
    public $sender_id;
    public $receiver_id;
    public $item_id;
    public $subject;
    public $body;
    public $is_read;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Send message
    public function sendMessage($sender_id, $receiver_id, $subject, $body, $item_id = null) {
        $query = "INSERT INTO {$this->table} (sender_id, receiver_id, item_id, subject, body, is_read)
                  VALUES (?, ?, ?, ?, ?, 0)";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }

        $stmt->bind_param("iisss", $sender_id, $receiver_id, $item_id, $subject, $body);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Message sent', 'message_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Failed to send message'];
        }
    }

    // Get inbox
    public function getInbox($user_id) {
        $query = "SELECT m.*, u.username, u.profile_picture FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.id
                  WHERE m.receiver_id = ? ORDER BY m.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get conversation between two users
    public function getConversation($user_id, $other_user_id) {
        $query = "SELECT m.*, u.username FROM {$this->table} m
                  JOIN users u ON m.sender_id = u.id
                  WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR 
                         (m.sender_id = ? AND m.receiver_id = ?))
                  ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Mark as read
    public function markAsRead($message_id) {
        $query = "UPDATE {$this->table} SET is_read = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $message_id);
        return $stmt->execute();
    }

    // Get unread count
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE receiver_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
}
?>
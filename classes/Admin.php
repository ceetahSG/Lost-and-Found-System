<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users with pagination
    public function getAllUsers($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT id, username, email, full_name, role, is_banned, created_at FROM users 
                  ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get total users count
    public function getTotalUsers() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM users");
        return $result->fetch_assoc()['total'];
    }

    // Get all items with pagination
    public function getAllItems($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT i.*, u.username FROM items i
                  JOIN users u ON i.user_id = u.id
                  ORDER BY i.date_posted DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get total items count
    public function getTotalItems() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM items");
        return $result->fetch_assoc()['total'];
    }

    // Ban user
    public function banUser($user_id) {
        $query = "UPDATE users SET is_banned = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $this->logAction($_SESSION['user_id'], 'ban_user', 'user', $user_id, 'User banned');
            return ['success' => true, 'message' => 'User banned'];
        }
        return ['success' => false, 'message' => 'Ban failed'];
    }

    // Unban user
    public function unbanUser($user_id) {
        $query = "UPDATE users SET is_banned = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $this->logAction($_SESSION['user_id'], 'unban_user', 'user', $user_id, 'User unbanned');
            return ['success' => true, 'message' => 'User unbanned'];
        }
        return ['success' => false, 'message' => 'Unban failed'];
    }

    // Delete item
    public function deleteItem($item_id) {
        $query = "DELETE FROM items WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $item_id);
        
        if ($stmt->execute()) {
            $this->logAction($_SESSION['user_id'], 'delete_item', 'item', $item_id, 'Item deleted');
            return ['success' => true, 'message' => 'Item deleted'];
        }
        return ['success' => false, 'message' => 'Delete failed'];
    }

    // Get dashboard stats
    public function getStats() {
        $stats = [];
        
        $total_users = $this->conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $total_items = $this->conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc()['count'];
        $active_items = $this->conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'active'")->fetch_assoc()['count'];
        $lost_items = $this->conn->query("SELECT COUNT(*) as count FROM items WHERE item_type = 'lost'")->fetch_assoc()['count'];
        $found_items = $this->conn->query("SELECT COUNT(*) as count FROM items WHERE item_type = 'found'")->fetch_assoc()['count'];

        return [
            'total_users' => $total_users,
            'total_items' => $total_items,
            'active_items' => $active_items,
            'lost_items' => $lost_items,
            'found_items' => $found_items
        ];
    }

    // Log admin actions
    public function logAction($admin_id, $action, $target_type, $target_id, $details) {
        $query = "INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issss", $admin_id, $action, $target_type, $target_id, $details);
        return $stmt->execute();
    }
}
?>
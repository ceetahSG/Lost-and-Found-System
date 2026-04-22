<?php
class Item {
    private $conn;
    private $table = 'items';

    public $id;
    public $user_id;
    public $category;
    public $item_type;
    public $title;
    public $description;
    public $image_url;
    public $location;
    public $status;
    public $date_lost_found;
    public $color;
    public $distinguishing_features;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Post new item - FIXED
    public function postItem($user_id, $category, $item_type, $title, $description, $location, $date_lost_found, $color = '', $features = '') {
        $query = "INSERT INTO {$this->table} 
                  (user_id, category, item_type, title, description, location, date_lost_found, color, distinguishing_features, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }

        // FIXED: issssssss = 1 integer + 8 strings = 9 total (matches 9 variables)
        $stmt->bind_param("issssssss", $user_id, $category, $item_type, $title, $description, $location, $date_lost_found, $color, $features);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Item posted successfully', 'item_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Failed to post item'];
        }
    }

    // Upload item image
    public function uploadImage($item_id, $file) {
        $target_dir = UPLOAD_PATH . "items/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($file['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_type, $allowed)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File too large'];
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $pic_path = "items/" . $file_name;
            $query = "UPDATE {$this->table} SET image_url = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $pic_path, $item_id);
            $stmt->execute();

            return ['success' => true, 'message' => 'Image uploaded', 'path' => $pic_path];
        } else {
            return ['success' => false, 'message' => 'Upload failed'];
        }
    }

    // Get all active items with search filters - FIXED
    public function searchItems($category = '', $item_type = '', $location = '', $status = 'active') {
        $query = "SELECT i.*, u.username, u.profile_picture FROM {$this->table} i 
                  JOIN users u ON i.user_id = u.id WHERE 1=1";

        $params = [];
        $types = '';

        if (!empty($category)) {
            $query .= " AND i.category = ?";
            $params[] = $category;
            $types .= 's';
        }
        if (!empty($item_type)) {
            $query .= " AND i.item_type = ?";
            $params[] = $item_type;
            $types .= 's';
        }
        if (!empty($location)) {
            $query .= " AND i.location LIKE ?";
            $params[] = "%{$location}%";
            $types .= 's';
        }
        if (!empty($status)) {
            $query .= " AND i.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        $query .= " ORDER BY i.date_posted DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        if ($types && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $items;
    }

    // Get item by ID
    public function getItemById($item_id) {
        $query = "SELECT i.*, u.username, u.email, u.phone, u.profile_picture FROM {$this->table} i 
                  JOIN users u ON i.user_id = u.id WHERE i.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get user's items
    public function getUserItems($user_id) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY date_posted DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Update item
    public function updateItem($item_id, $title, $description, $location, $status) {
        $query = "UPDATE {$this->table} SET title = ?, description = ?, location = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssi", $title, $description, $location, $status, $item_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Item updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    // Delete item
   public function deleteItem($item_id, $user_id) {
    // Check ownership
    $query = "SELECT user_id FROM {$this->table} WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || $result['user_id'] != $user_id) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }

    // Delete related messages first
    $stmt = $this->conn->prepare("DELETE FROM messages WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();

    // Delete item
    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Item deleted'];
    } else {
        return ['success' => false, 'message' => $this->conn->error];
    }
}

    // Find matching items
    public function findMatches($item_id) {
        $item = $this->getItemById($item_id);
        if (!$item) {
            return [];
        }
        
        $opposite_type = ($item['item_type'] == 'lost') ? 'found' : 'lost';

        $query = "SELECT * FROM {$this->table} WHERE item_type = ? AND category = ? 
                  AND id != ? AND status = 'active' ORDER BY date_posted DESC LIMIT 5";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssi", $opposite_type, $item['category'], $item_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $phone;
    public $address;
    public $profile_picture;
    public $is_banned;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function register($username, $email, $password, $full_name) {
        // Check if user already exists
        $checkQuery = "SELECT id FROM {$this->table} WHERE email = ? OR username = ?";
        $stmt = $this->conn->prepare($checkQuery);
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }

        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email or username already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $query = "INSERT INTO {$this->table} (username, email, password, full_name, role) 
                  VALUES (?, ?, ?, ?, 'user')";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Registration failed'];
        }

        $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT id, username, email, password, role, is_banned FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Email not found'];
        }

        $user = $result->fetch_assoc();

        if ($user['is_banned']) {
            return ['success' => false, 'message' => 'Your account has been banned'];
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            return ['success' => true, 'message' => 'Login successful', 'user_id' => $user['id'], 'role' => $user['role']];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }

    // Get user profile
    public function getProfile($user_id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update profile
    public function updateProfile($user_id, $full_name, $phone, $address) {
        $query = "UPDATE {$this->table} SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Update failed'];
        }
    }

    // Upload profile picture
    public function uploadProfilePicture($user_id, $file) {
        $target_dir = UPLOAD_PATH . "profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($file['name']);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_type, $allowed)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File too large'];
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $pic_path = "profiles/" . $file_name;
            $query = "UPDATE {$this->table} SET profile_picture = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $pic_path, $user_id);
            $stmt->execute();

            return ['success' => true, 'message' => 'Picture uploaded', 'path' => $pic_path];
        } else {
            return ['success' => false, 'message' => 'Upload failed'];
        }
    }

    // Check if user is admin
    public function isAdmin($user_id) {
        $query = "SELECT role FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['role'] === 'admin';
    }
}
?>
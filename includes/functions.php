<?php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once __DIR__ . '/../config/database.php';

// Include classes
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Message.php';
require_once __DIR__ . '/../classes/Admin.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'pages/index.php');
        exit;
    }
}

// Escape output for XSS protection
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Get user by ID
function getUserData($user_id, $db) {
    $user = new User($db);
    return $user->getProfile($user_id);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Get relative time (e.g., "2 hours ago")
function getRelativeTime($date) {
    $time = strtotime($date);
    $diff = time() - $time;

    if ($diff < 60) return $diff . ' seconds ago';
    elseif ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    elseif ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    elseif ($diff < 604800) return floor($diff / 86400) . ' days ago';
    else return date('M d, Y', $time);
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate password strength
function validatePassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

// CSRF token generation
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Output CSRF token field
function csrfField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . escape($token) . '">';
}

?>
<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine environment and load appropriate config
// database-railway.php handles both Railway and local development
require_once __DIR__ . '/../config/database-railway.php';

// Include classes
$user_class = dirname(__DIR__) . '/classes/User.php';
$item_class = dirname(__DIR__) . '/classes/Item.php';
$message_class = dirname(__DIR__) . '/classes/Message.php';
$admin_class = dirname(__DIR__) . '/classes/Admin.php';

if (file_exists($user_class)) require_once $user_class;
if (file_exists($item_class)) require_once $item_class;
if (file_exists($message_class)) require_once $message_class;
if (file_exists($admin_class)) require_once $admin_class;

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
    if (!$user_id || !isset($db)) return null;
    $user = new User($db);
    return $user->getProfile($user_id);
}

// Format date
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    return date('M d, Y', $timestamp);
}

// Get relative time (e.g., "2 hours ago") - FIXED
function getRelativeTime($date) {
    if (empty($date)) {
        return 'Recently';
    }

    $time = strtotime($date);
    
    if ($time === false) {
        return 'Recently';
    }

    $now = time();
    $diff = $now - $time;

    // If time is in the future
    if ($diff < 0) {
        return 'just now';
    }

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 120) {
        return '1 minute ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 7200) {
        return '1 hour ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 172800) {
        return '1 day ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $time);
    }
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

// Validate username
function validateUsername($username) {
    return strlen($username) >= 3 && preg_match('/^[a-zA-Z0-9_-]+$/', $username);
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
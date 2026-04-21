<?php
// Railway Production Database Configuration

// Get database URL from Railway environment
$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Parse database URL
    $db_config = parse_url($db_url);
    
    define('DB_HOST', $db_config['host']);
    define('DB_USER', $db_config['user']);
    define('DB_PASS', $db_config['pass']);
    define('DB_NAME', ltrim($db_config['path'], '/'));
} else {
    // Fallback to individual environment variables
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASSWORD') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'lost_and_found');
}

// Get public domain for BASE_URL
$public_domain = getenv('RAILWAY_PUBLIC_DOMAIN');
define('BASE_URL', $public_domain ? 'https://' . $public_domain . '/' : 'http://localhost:8000/');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Upload configuration
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Enable error logging in production
if (!getenv('RAILWAY_ENVIRONMENT')) {
    // Local development
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    // Production
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}

?>
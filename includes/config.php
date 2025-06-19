<?php
session_start();

// Database configuration
define('DB_HOST', '127.0.0.1:3306');
define('DB_NAME', 'u819159169_wemia');
define('DB_USER', 'u819159169_wemia');
define('DB_PASS', '@20SX0k3w@27');

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip', 'rar']);

// Security settings
define('HASH_COST', 12);
define('SESSION_LIFETIME', 7200); // 2 hours

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Get database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        if (isJsonRequest()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
        } else {
            header('Location: /');
        }
        exit;
    }
}

// Require admin authentication
function requireAdmin() {
    if (!isAdmin()) {
        if (isJsonRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'Admin access required']);
        } else {
            header('Location: /');
        }
        exit;
    }
}

// Check if request expects JSON response
function isJsonRequest() {
    return isset($_SERVER['HTTP_ACCEPT']) && 
           strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
           isset($_SERVER['CONTENT_TYPE']) && 
           strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        if (isJsonRequest()) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token validation failed']);
        } else {
            die('CSRF token validation failed');
        }
        exit;
    }
}

// Clean uploaded filename
function cleanFileName($fileName) {
    // Remove any directory components
    $fileName = basename($fileName);
    
    // Replace any non-alphanumeric characters (except dots and dashes)
    $fileName = preg_replace('/[^a-zA-Z0-9\-\.]/', '_', $fileName);
    
    // Ensure safe extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    // Add random prefix to prevent overwriting
    return uniqid() . '_' . $fileName;
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
} 
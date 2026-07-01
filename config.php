<?php
/**
 * Database Configuration for Helpdesk System
 * PHP 7.4+ Compatible
 */

// Session configuration - HARUS SEBELUM session_start()
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', true);
ini_set('session.use_strict_mode', true);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');

// Start session hanya sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration - Hostinger
define('DB_HOST', 'localhost');
define('DB_NAME', 'helpdesk_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
// Update SITE_URL to your production URL when deploying
define('SITE_URL', 'http://localhost/helpdesk_system');
define('ADMIN_EMAILS', ['admin@helpdesk.local']);

// Timezone
date_default_timezone_set('Asia/Makassar');
// --- TAMBAHKAN DI SINI ---
try {
    $db = Database::getInstance()->getConnection();
    $db->exec("SET time_zone = '+08:00'");
} catch (Exception $e) {
    // Boleh diabaikan
}
// --- END TAMBAH ---

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection class
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function untuk mendapatkan koneksi database
function getDB() {
    return Database::getInstance()->getConnection();
}

// Helper function untuk sanitasi input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// FIXED: Add missing sanitize function
function sanitize($data, $connection = null) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function untuk JSON response
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// FIXED: Add missing response function
function response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// FIXED: Add missing sendResponse function
function sendResponse($success, $message, $data = null) {
    return response($success, $message, $data);
}
?>
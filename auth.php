<?php
require_once 'config.php';

class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Login user - FIXED to use PDO and proper password verification
    public function login($email, $password) {
        $email = clean_input($email);
        
        $stmt = $this->conn->prepare("SELECT id, name, email, department, password, role FROM " . $this->table_name . " WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_department'] = $user['department'];
            $_SESSION['user_role'] = $user['role'];
            
            return json_response(['success' => true, 'message' => 'Login successful', 'user' => $user]);
        }
        
        return json_response(['success' => false, 'message' => 'Invalid email or password']);
    }

    // Register new user - FIXED to use PDO and proper password hashing
    public function register($name, $email, $department, $password, $role = 'user') {
        $name = clean_input($name);
        $email = clean_input($email);
        $department = clean_input($department);
        
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return json_response(['success' => false, 'message' => 'Email already registered']);
        }
        
        // Hash password properly
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Set role: if email is in ADMIN_EMAILS, then admin, else user
        $role = in_array($email, ADMIN_EMAILS) ? 'admin' : 'user';
        
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " (name, email, department, password, role) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $department, $hashedPassword, $role])) {
            return json_response(['success' => true, 'message' => 'Registration successful']);
        }
        
        return json_response(['success' => false, 'message' => 'Registration failed']);
    }

    // Change password - FIXED to use PDO and proper password verification
    public function changePassword($user_id, $current_password, $new_password) {
        $stmt = $this->conn->prepare("SELECT password FROM " . $this->table_name . " WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($current_password, $user['password'])) {
            $hashedNewPassword = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashedNewPassword, $user_id])) {
                return json_response(['success' => true, 'message' => 'Password changed successfully']);
            }
        }
        
        return json_response(['success' => false, 'message' => 'Current password is incorrect']);
    }

    // Logout
    public function logout() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return json_response(['success' => true, 'message' => 'Logout successful']);
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Get current user
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'department' => $_SESSION['user_department'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
}

// Handle AJAX requests - FIXED to use proper database connection
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $auth = new Auth($db);
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $auth->login($_POST['email'], $_POST['password']);
            break;
            
        case 'register':
            $auth->register($_POST['name'], $_POST['email'], $_POST['department'], $_POST['password']);
            break;
            
        case 'change_password':
            if ($auth->isLoggedIn()) {
                $user = $auth->getCurrentUser();
                $auth->changePassword($user['id'], $_POST['current_password'], $_POST['new_password']);
            }
            break;
            
        case 'logout':
            $auth->logout();
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Invalid action']);
    }
}
?>
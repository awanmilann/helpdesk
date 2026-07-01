<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

class UserManager {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users - FIXED to use PDO
    public function getAllUsers() {
        $stmt = $this->conn->query("SELECT id, name, email, department, role, created_at FROM " . $this->table_name . " ORDER BY name");
        $users = $stmt->fetchAll();
        
        return sendResponse(true, "", $users);
    }

    // Create new user - FIXED to use PDO and proper password hashing
    public function createUser($name, $email, $department, $password, $role = 'user') {
        $name = clean_input($name);
        $email = clean_input($email);
        $department = clean_input($department);
        
        // Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM " . $this->table_name . " WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return sendResponse(false, "Email already exists");
        }
        
        // Hash password properly
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Override role if email is admin
        if (in_array($email, ADMIN_EMAILS)) {
            $role = 'admin';
        }
        
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " (name, email, department, password, role) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $department, $hashedPassword, $role])) {
            return sendResponse(true, "User created successfully");
        }
        
        return sendResponse(false, "Failed to create user");
    }

    // Delete user - FIXED to use PDO
    public function deleteUser($user_id) {
        // Prevent deleting yourself
        if ($user_id == $_SESSION['user_id']) {
            return sendResponse(false, "Cannot delete your own account");
        }
        
        // Check if user is admin
        $stmt = $this->conn->prepare("SELECT email FROM " . $this->table_name . " WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return sendResponse(false, "User not found");
        }
        
        // Prevent deleting main admin accounts
        if (in_array($user['email'], ADMIN_EMAILS)) {
            return sendResponse(false, "Main admin accounts cannot be deleted");
        }
        
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        
        if ($stmt->execute([$user_id])) {
            return sendResponse(true, "User deleted successfully");
        }
        
        return sendResponse(false, "Failed to delete user");
    }
}

// Handle AJAX requests - FIXED to use proper database connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $userManager = new UserManager($db);
    $auth = new Auth($db);
    
    if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'admin') {
        sendResponse(false, "Access denied");
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_all_users':
            $userManager->getAllUsers();
            break;
            
        case 'create_user':
            $userManager->createUser(
                $_POST['name'],
                $_POST['email'],
                $_POST['department'],
                $_POST['password'],
                $_POST['role'] ?? 'user'
            );
            break;
            
        case 'delete_user':
            $userManager->deleteUser($_POST['user_id']);
            break;
            
        default:
            sendResponse(false, "Invalid action");
    }
}
?>
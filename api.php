<?php
/**
 * Helpdesk System API
 * Handles all server-side logic for IT Helpdesk
 */

require_once 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
// Get action from GET parameter, trim and clean it
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$action = clean_input($action);

// Debug logging (remove in production if needed)
error_log("API Request - Method: $method, Action: $action, GET params: " . print_r($_GET, true));

try {
    $db = getDB();
    
    switch ($action) {
        // Authentication
        case 'login':
            handle_login($db);
            break;
        case 'signup':
            handle_signup($db);
            break;
        case 'check_session':
            handle_check_session();
            break;
        case 'get_user_info':
            handle_check_session(); // Reuse check_session to get user info
            break;
        case 'logout':
            handle_logout();
            break;
        case 'change_password':
            handle_change_password($db);
            break;
        case 'request_password_reset':
            handle_request_password_reset($db);
            break;
        case 'verify_reset_token':
            handle_verify_reset_token($db);
            break;
        case 'reset_password':
            handle_reset_password($db);
            break;
            
        // Tickets
        case 'create_ticket':
            create_ticket($db);
            break;
    case 'list_tickets':
        list_tickets($db);
        break;
    case 'get_attachments':
        get_attachments($db);
        break;
    case 'get_ticket_details':
        get_ticket_details($db);
        break;
    case 'update_user_ticket':
        update_user_ticket($db);
        break;
    case 'delete_user_ticket':
        delete_user_ticket($db);
        break;
    case 'update_ticket':
        update_ticket($db);
        break;
        case 'admin_delete_ticket':
            admin_delete_ticket($db);
            break;
        case 'ticket_stats':
            get_ticket_stats($db);
            break;
        // Users (Admin only)
        case 'list_users':
            list_users($db);
            break;
        case 'create_user':
            create_user($db);
            break;
        case 'update_user':
            update_user($db);
            break;
        case 'delete_user':
            delete_user($db);
            break;
            
        // Notifications
        case 'get_notifications':
            get_notifications($db);
            break;
        case 'mark_notification_read':
            mark_notification_read($db);
            break;
        case 'mark_all_notifications_read':
            mark_all_notifications_read($db);
            break;
        case 'delete_notification':
            delete_notification($db);
            break;
        case 'clear_read_notifications':
            clear_read_notifications($db);
            break;
            
        // SDLC Form Functions
        case 'save_sdlc_data':
            save_sdlc_data($db);
            break;
        case 'get_sdlc_data':
            get_sdlc_data($db);
            break;
        case 'view_sdlc':
            view_sdlc_html($db);
            break;
        case 'download_sdlc':
            download_sdlc($db);
            break;
        case 'download_sdlc_pdf':
            download_sdlc_pdf($db);
            break;
            
        // Report Functions
        case 'get_report':
            get_report($db);
            break;
        case 'get_admin_list':
            get_admin_list($db);
            break;
        case 'update_profile':
            update_profile($db);
            break;
            
        default:
            // Log invalid action for debugging
            error_log("Invalid action received: '$action' (method: $method)");
            error_log("Available actions: login, signup, check_session, logout, change_password, reset_password, create_ticket, list_tickets, get_attachments, get_ticket_details, update_user_ticket, delete_user_ticket, update_ticket, admin_delete_ticket, ticket_stats, list_users, create_user, update_user, delete_user, get_notifications, mark_notification_read, mark_all_notifications_read, delete_notification, clear_read_notifications, get_report, get_admin_list, update_profile");
            json_response(['success' => false, 'message' => 'Invalid action: ' . ($action ?: 'empty'), 'received_action' => $action, 'method' => $method], 400);
    }
    
} catch (Exception $e) {
    error_log("API Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    json_response(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

// --- Authentication Functions ---

function handle_login($db) {
    // Enable CORS for debugging
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");
    
    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log received data for debugging
    error_log("Login attempt: " . print_r($data, true));
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        json_response(['success' => false, 'message' => 'Invalid JSON data'], 400);
    }
    
    $login_input = strtolower(trim($data['email'] ?? '')); // Can be email or username
    $password = $data['password'] ?? '';
    
    if (empty($login_input) || empty($password)) {
        json_response(['success' => false, 'message' => 'Username/Email and password are required'], 400);
    }
    
    try {
        // Try to login with either username or email
        $stmt = $db->prepare("SELECT id, username, name, email, department, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();
        
        error_log("User query result: " . print_r($user, true));
        
        if ($user && password_verify($password, $user['password'])) {
            // Session should already be started in config.php
            // Just set the session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_department'] = $user['department'];
            
            // Force session write to ensure it's saved
            session_write_close();
            session_start();
            
            error_log("Login successful for: " . $login_input);
            
            json_response([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'department' => $user['department'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            error_log("Login failed for: " . $login_input);
            json_response(['success' => false, 'message' => 'Invalid username/email or password'], 401);
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        json_response(['success' => false, 'message' => 'Server error during login'], 500);
    }
}

function handle_signup($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = strtolower(trim($data['username'] ?? ''));
    $name = clean_input($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $dept = clean_input($data['department'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($name) || empty($email) || empty($dept) || empty($password)) {
        json_response(['success' => false, 'message' => 'All fields are required'], 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    if (strlen($password) < 6) {
        json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Validate username format (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        json_response(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores'], 400);
    }
    
    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Username already exists'], 409);
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Email already exists'], 409);
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Set role: if email is in ADMIN_EMAILS, then admin, else user
    $role = in_array($email, ADMIN_EMAILS) ? 'admin' : 'user';
    
    $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $name, $email, $dept, $hashedPassword, $role]);
    
    json_response([
        'success' => true,
        'message' => 'Signup successful. You can now login with your username or email.'
    ]);
}

function handle_check_session() {
    // Session should already be started in config.php
    // Check if session is valid and user is logged in
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        global $db;
        
        // Get profile picture from database
        $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();
        $profilePicture = $userData ? $userData['profile_picture'] : null;
        
        json_response([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['user_username'] ?? '',
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'department' => $_SESSION['user_department'],
                'role' => $_SESSION['user_role'],
                'profile_picture' => $profilePicture
            ]
        ]);
    } else {
        // Clear any invalid session data
        $_SESSION = array();
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
}

function handle_logout() {
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
    
    json_response(['success' => true, 'message' => 'Logout successful']);
}

function handle_change_password($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        json_response(['success' => false, 'message' => 'All fields are required'], 400);
    }
    
    if ($newPassword !== $confirmPassword) {
        json_response(['success' => false, 'message' => 'New password and confirmation do not match'], 400);
    }
    
    if (strlen($newPassword) < 6) {
        json_response(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
    }
    
    // Verify current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        json_response(['success' => false, 'message' => 'Current password is incorrect'], 400);
    }
    
    // Update password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedNewPassword, $_SESSION['user_id']]);
    
    json_response(['success' => true, 'message' => 'Password changed successfully']);
}

// Request password reset - send email with reset token
function handle_request_password_reset($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = strtolower(trim($data['email'] ?? ''));
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Valid email is required'], 400);
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Always return success message (security: don't reveal if email exists)
    if (!$user) {
        json_response([
            'success' => true,
            'message' => 'If the email exists, a password reset link has been sent to your inbox.'
        ]);
        return;
    }
    
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour
    
    // Invalidate any existing tokens for this user
    $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE user_id = ? AND used = 0");
    $stmt->execute([$user['id']]);
    
    // Insert new token
    $stmt = $db->prepare("INSERT INTO password_reset_tokens (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user['id'], $email, $token, $expires_at]);
    
    // Get site URL from config or use default
    $site_url = defined('SITE_URL') ? SITE_URL : 'http://localhost/helpdesk_system';
    $reset_link = $site_url . '/index.php?reset_token=' . $token;
    
    // Prepare email
        $logo_url = 'http://localhost/helpdesk_system/assets/images/logo.png';
    $email_body = <<<HTML
<h2>Password Reset Request</h2>
<p>Dear {$user['name']},</p>
<p>You have requested to reset your password for the IT Helpdesk System.</p>
<p>Click the button below to reset your password:</p>
<p style="text-align: center; margin: 30px 0;">
    <a href="$reset_link" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">Reset Password</a>
</p>
<p>Or copy and paste this link into your browser:</p>
<p style="word-break: break-all; color: #4f46e5;">$reset_link</p>
<p><strong>Important:</strong></p>
<ul>
    <li>This link will expire in 1 hour</li>
    <li>If you did not request this password reset, please ignore this email</li>
    <li>For security reasons, please do not share this link with anyone</li>
</ul>
HTML;
    
    // Send email using existing email function
    $admin_email = 'admin@helpdesk.local';
    $subject = '[Helpdesk] Password Reset Request';
    
    $email_sent = send_ticket_email($email, $user['name'], $subject, $email_body, $admin_email);
    
    if ($email_sent) {
        json_response([
            'success' => true,
            'message' => 'Password reset link has been sent to your email. Please check your inbox.'
        ]);
    } else {
        json_response([
            'success' => false,
            'message' => 'Failed to send reset email. Please try again later.'
        ]);
    }
}

// Verify reset token
function handle_verify_reset_token($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $token = trim($data['token'] ?? '');
    
    if (empty($token)) {
        json_response(['success' => false, 'message' => 'Token is required'], 400);
    }
    
    // Check token validity
    $stmt = $db->prepare("
        SELECT prt.id, prt.user_id, prt.email, prt.expires_at, prt.used, u.name 
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.id
        WHERE prt.token = ? AND prt.used = 0
    ");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();
    
    if (!$token_data) {
        json_response(['success' => false, 'message' => 'Invalid or expired reset token'], 400);
    }
    
    // Check if token has expired
    $expires_at = strtotime($token_data['expires_at']);
    $now = time();
    
    if ($now > $expires_at) {
        // Mark token as used
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
        $stmt->execute([$token_data['id']]);
        
        json_response(['success' => false, 'message' => 'Reset token has expired. Please request a new one.'], 400);
    }
    
    json_response([
        'success' => true,
        'message' => 'Token is valid',
        'email' => $token_data['email']
    ]);
}

// Reset password with token
function handle_reset_password($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $token = trim($data['token'] ?? '');
    $newPassword = $data['new_password'] ?? '';
    
    if (empty($token) || empty($newPassword)) {
        json_response(['success' => false, 'message' => 'Token and new password are required'], 400);
    }
    
    if (strlen($newPassword) < 6) {
        json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Verify token
    $stmt = $db->prepare("
        SELECT prt.id, prt.user_id, prt.email, prt.expires_at, prt.used, u.name 
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.id
        WHERE prt.token = ? AND prt.used = 0
    ");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();
    
    if (!$token_data) {
        json_response(['success' => false, 'message' => 'Invalid or expired reset token'], 400);
    }
    
    // Check if token has expired
    $expires_at = strtotime($token_data['expires_at']);
    $now = time();
    
    if ($now > $expires_at) {
        // Mark token as used
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
        $stmt->execute([$token_data['id']]);
        
        json_response(['success' => false, 'message' => 'Reset token has expired. Please request a new one.'], 400);
    }
    
    // Update password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedNewPassword, $token_data['user_id']]);
    
    // Mark token as used
    $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
    $stmt->execute([$token_data['id']]);
    
    json_response([
        'success' => true,
        'message' => 'Password has been reset successfully. You can now login with your new password.'
    ]);
}

// Admin reset password function (keep existing)
function handle_admin_reset_password($db) {
    // Only admin can reset passwords
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = intval($data['user_id'] ?? 0);
    $newPassword = $data['new_password'] ?? '';
    
    if ($userId <= 0 || empty($newPassword)) {
        json_response(['success' => false, 'message' => 'User ID and new password are required'], 400);
    }
    
    if (strlen($newPassword) < 6) {
        json_response(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT username, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Update password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedNewPassword, $userId]);
    
    json_response(['success' => true, 'message' => "Password reset successfully for {$user['name']} ({$user['username']})"]);
}

// --- Ticket Functions ---

// === Helper: Insert in-app notification ===
function insert_notification($db, $user_id, $message, $url = null) {
    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, url, is_read) VALUES (?, ?, ?, 0)");
    $stmt->execute([$user_id, $message, $url]);
}

// === Helper: Send HTML Email with Logo, Dynamic Greetings, and Footer ===
function send_ticket_email($to, $to_name, $subject, $email_body, $assign_email, $sender_email = null) {
    try {
        // Validasi input
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid recipient email: $to");
            return false;
        }
        
        if (empty($assign_email) || !filter_var($assign_email, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid assign email: $assign_email");
            $assign_email = 'admin@helpdesk.local'; // Fallback
        }
        
        global $db;
        
        // Ambil informasi admin yang assign untuk From address (selalu gunakan admin sebagai From)
        $admin_stmt = $db->prepare("SELECT name, email FROM users WHERE email = ?");
        $admin_stmt->execute([$assign_email]);
        $admin_info = $admin_stmt->fetch();
        
        // Ambil informasi sender (user) jika sender_email diberikan (untuk Reply-To)
        $sender_info = null;
        if (!empty($sender_email) && filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
            $sender_stmt = $db->prepare("SELECT name, email FROM users WHERE email = ?");
            $sender_stmt->execute([$sender_email]);
            $sender_info = $sender_stmt->fetch();
        }
        
        // Selalu gunakan email admin sebagai From address (untuk menghindari SPF/DKIM issues)
        if ($admin_info && !empty($admin_info['email']) && filter_var($admin_info['email'], FILTER_VALIDATE_EMAIL)) {
            $from_name = $admin_info['name'] . ' - IT Helpdesk System';
            $from = $admin_info['email'];
        } else {
            // Fallback jika admin tidak ditemukan
            $from_name = 'IT Helpdesk System';
            $from = filter_var($assign_email, FILTER_VALIDATE_EMAIL) ? $assign_email : 'admin@helpdesk.local';
        }
        
        // Reply-To: 
        // - Jika sender_email diberikan (email ke admin), gunakan sender_email (email user) sebagai Reply-To
        // - Jika tidak (email ke user), gunakan assign_email (admin) sebagai Reply-To
        if (!empty($sender_email) && filter_var($sender_email, FILTER_VALIDATE_EMAIL) && $sender_info) {
            // Email ke admin: Reply-To adalah email user
            $reply_to = $sender_email;
            $reply_to_name = $sender_info['name'] ?? 'User';
        } else {
            // Email ke user: Reply-To adalah email admin
            $reply_to = filter_var($assign_email, FILTER_VALIDATE_EMAIL) ? $assign_email : $from;
            $reply_to_name = ($admin_info && !empty($admin_info['name'])) ? $admin_info['name'] : 'IT Helpdesk Team';
        }
    $logo_url = 'http://localhost/helpdesk_system/assets/images/logo.png';
        
        // Footer dengan nama:
        // - Jika sender_email diberikan (email ke admin), tampilkan nama user di footer
        // - Jika tidak (email ke user), tampilkan nama admin di footer
        if (!empty($sender_email) && $sender_info && !empty($sender_info['name'])) {
            $footer_name_display = $sender_info['name'];
        } else {
            $footer_name_display = ($admin_info && !empty($admin_info['name'])) ? $admin_info['name'] : 'IT Helpdesk Team';
        }
        
        // Sanitasi nama untuk HTML
        $footer_name_display_safe = htmlspecialchars($footer_name_display, ENT_QUOTES, 'UTF-8');
        
        $footer = <<<HTML
<p style="margin-bottom:0;"><strong>Warm Regards,</strong></p>
<p style="margin:0;">
<strong>{$footer_name_display_safe}</strong><br>
IT Helpdesk System<br>
Information Systems &amp; Digital Infrastructure<br>
Helpdesk System<br>
<a href="http://localhost/helpdesk_system" target="_blank">localhost/helpdesk_system</a>
</p>
HTML;
        $body = <<<EMAIL
<html><body style="font-family:Arial,sans-serif;background-color:#f5f5f5;padding:20px;">
<div style="text-align:center;margin-bottom:20px;">
<img src="$logo_url" alt="Bamboo Village Trust Logo" style="width:120px;height:auto;margin-bottom:10px;"><br>
<small style="color:#666;">IT Helpdesk System</small>
</div>
<div style="max-width:600px;margin:0 auto;background-color:#ffffff;border:1px solid #e0e0e0;padding:30px;border-radius:12px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
$email_body
$footer
</div>
<div style="text-align:center;margin-top:20px;color:#999;font-size:12px;">
<p>Email ini dikirim secara otomatis dari IT Helpdesk System atas nama {$footer_name_display_safe}.</p>
</div>
</body></html>
EMAIL;
        
        // Encoding nama untuk header email (mendukung karakter non-ASCII)
        // Jika nama mengandung karakter non-ASCII, gunakan encoded format
        $encoded_from_name = mb_encode_mimeheader($from_name, 'UTF-8', 'Q');
        $encoded_reply_name = mb_encode_mimeheader($reply_to_name, 'UTF-8', 'Q');
        
        // Email headers yang lebih lengkap untuk meningkatkan keamanan dan deliverability
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: $encoded_from_name <$from>" . "\r\n";
        $headers .= "Reply-To: $encoded_reply_name <$reply_to>" . "\r\n";
        $headers .= "Return-Path: <$from>" . "\r\n"; // Return path untuk bounce handling
        $headers .= "X-Mailer: IT Helpdesk System" . "\r\n";
        $headers .= "X-Priority: 3" . "\r\n";
        $headers .= "Organization: Helpdesk System" . "\r\n";
        $headers .= "Message-ID: <" . time() . "." . md5($to . $from) . "@helpdesk.local>" . "\r\n";
        
        // Encoding subject dengan UTF-8
        $encoded_subject = mb_encode_mimeheader($subject, 'UTF-8', 'Q');
        
        // Log informasi email sebelum dikirim (untuk debugging)
        error_log("Attempting to send email:");
        error_log("  To: $to");
        error_log("  From: $from ($from_name)");
        error_log("  Reply-To: $reply_to ($reply_to_name)");
        error_log("  Subject: $subject");
        error_log("  Sender email param: " . ($sender_email ?? 'null'));
        
        // Kirim email
        $result = @mail($to, $encoded_subject, $body, $headers);
        
        // Log hasil pengiriman
        if ($result) {
            error_log("Email sent successfully to: $to, Subject: $subject");
        } else {
            $error = error_get_last();
            error_log("Failed to send email to: $to");
            error_log("Error details: " . ($error ? $error['message'] : 'Unknown error'));
            error_log("Headers: " . str_replace("\r\n", " | ", $headers));
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Exception in send_ticket_email: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

// === Update create_ticket to trigger notification and email ===
function create_ticket($db) {
    // Log function entry
    error_log("create_ticket function called");
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
    error_log("Request method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    if (!isset($_SESSION['user_id'])) {
        error_log("create_ticket: User not logged in");
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    // Check if it's FormData (file upload) or JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isFormData = strpos($contentType, 'multipart/form-data') !== false;
    
    error_log("create_ticket: isFormData = " . ($isFormData ? 'true' : 'false'));
    
    if ($isFormData) {
        // Handle FormData (file upload)
        // Category tidak perlu di-clean_input karena ENUM, langsung trim saja untuk menghindari htmlspecialchars yang mengubah & menjadi &amp;
        $category = trim($_POST['category'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $priority = clean_input($_POST['priority'] ?? 'Medium');
        $assign_to = clean_input($_POST['assign_to'] ?? '');
        
        // Get files and URLs
        // Handle multiple files - PHP will parse 'files[]' as $_FILES['files']
        $files = $_FILES['files'] ?? [];
        // Handle URLs array - PHP will parse 'urls[]' as array in $_POST
        $urls = isset($_POST['urls']) ? (is_array($_POST['urls']) ? $_POST['urls'] : [$_POST['urls']]) : [];
    } else {
        // Handle JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        // Category tidak perlu di-clean_input karena ENUM, langsung trim saja
        $category = trim($data['category'] ?? '');
        $description = clean_input($data['description'] ?? '');
        $priority = clean_input($data['priority'] ?? 'Medium');
        $assign_to = clean_input($data['assign_to'] ?? '');
        $files = [];
        $urls = [];
    }
    
    // Log received data
    error_log("create_ticket: category = " . ($category ?? 'empty'));
    error_log("create_ticket: description = " . (substr($description ?? '', 0, 50) . '...'));
    error_log("create_ticket: priority = " . ($priority ?? 'empty'));
    error_log("create_ticket: assign_to = " . ($assign_to ?? 'empty'));
    
    if (empty($category) || empty($description) || empty($assign_to)) {
        error_log("create_ticket: Missing required fields - category: " . ($category ? 'set' : 'empty') . ", description: " . ($description ? 'set' : 'empty') . ", assign_to: " . ($assign_to ? 'set' : 'empty'));
        json_response(['success' => false, 'message' => 'Category, description, and assign to are required'], 400);
    }
    
    // Validasi category sesuai ENUM di database
    // Mapping kategori baru ke format database (untuk backward compatibility)
    $categoryMapping = [
        'Jaringan & Keamanan' => 'Network',
        'Aplikasi & Sistem Internal' => 'System & Aplikasi',
        'Perangkat Keras' => 'Hardware',
        'Perangkat Lunak Umum & Akun' => 'Software',
        'Pengajuan pembuatan atau perubahan aplikasi sistem' => 'Pengajuan pembuatan atau perubahan aplikasi sistem'
    ];
    
    // Convert kategori baru ke format database jika perlu
    $dbCategory = $categoryMapping[$category] ?? $category;
    
    $validCategories = ['Network', 'Software', 'Hardware', 'System & Aplikasi', 'Pengajuan pembuatan atau perubahan aplikasi sistem', 'Jaringan & Keamanan', 'Aplikasi & Sistem Internal', 'Perangkat Keras', 'Perangkat Lunak Umum & Akun'];
    if (!in_array($category, $validCategories)) {
        json_response(['success' => false, 'message' => 'Invalid category. Must be one of: ' . implode(', ', $validCategories)], 400);
    }
    
    // Use database category for storage
    $category = $dbCategory;
    
    // Get user info from session
    $reporter_id = $_SESSION['user_id'];
    $reporter_name = $_SESSION['user_name'];
    $reporter_dept = $_SESSION['user_department'];
    
    // Generate ticket number
    $stmt = $db->query("SELECT counter FROM ticket_counter WHERE id = 1");
    $counter = $stmt->fetch()['counter'] + 1;
    
    // Update counter
    $db->prepare("UPDATE ticket_counter SET counter = ? WHERE id = 1")->execute([$counter]);
    
    $ticket_number = 'T' . str_pad($counter, 5, '0', STR_PAD_LEFT);
    
    // Debug: Log category value sebelum insert
    error_log("Creating ticket with category: " . $category);
    error_log("Category type: " . gettype($category));
    error_log("Category length: " . strlen($category));
    
    $stmt = $db->prepare("INSERT INTO tickets (ticket_number, reporter_id, reporter_name, reporter_dept, category, description, priority, assign_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$ticket_number, $reporter_id, $reporter_name, $reporter_dept, $category, $description, $priority, $assign_to]);
        error_log("Ticket created successfully with category: " . $category);
    } catch (PDOException $e) {
        error_log("Error creating ticket: " . $e->getMessage());
        error_log("Category value that failed: " . $category);
        json_response(['success' => false, 'message' => 'Failed to create ticket: ' . $e->getMessage()], 500);
    }
    
    $ticket_id = $db->lastInsertId();
    
    // Handle file uploads
    if (!empty($files) && isset($files['name'])) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle multiple files
        // Check if it's single file or multiple files
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;
        
        for ($i = 0; $i < $fileCount; $i++) {
            // Handle both single and multiple file formats
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $fileTmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $fileType = is_array($files['type']) ? $files['type'][$i] : $files['type'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            
            if ($fileError === UPLOAD_ERR_OK) {
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $uniqueFileName;
                
                // Move uploaded file
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    // Save to database
                    $stmt = $db->prepare("INSERT INTO attachments (ticket_id, file_name, file_path, file_type, file_size, attachment_type) VALUES (?, ?, ?, ?, ?, 'file')");
                    $stmt->execute([$ticket_id, $fileName, $filePath, $fileType, $fileSize]);
                }
            }
            
            // If single file, break after first iteration
            if (!is_array($files['name'])) {
                break;
            }
        }
    }
    
    // Handle URL attachments
    if (!empty($urls)) {
        foreach ($urls as $url) {
            if (!empty($url)) {
                $stmt = $db->prepare("INSERT INTO attachments (ticket_id, attachment_type, url) VALUES (?, 'url', ?)");
                $stmt->execute([$ticket_id, $url]);
            }
        }
    }
    
    // Get the created ticket
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    // ambil admin user detail
    $admin_stmt = $db->prepare("SELECT id, email, name FROM users WHERE email = ?");
    $admin_stmt->execute([$assign_to]);
    $admin = $admin_stmt->fetch();
    if ($admin) {
        // create in-app notification (admin)
        $notif_msg = "You have been assigned a new ticket from {$_SESSION['user_name']} (#{ $ticket_number })";
        insert_notification($db, $admin['id'], $notif_msg);
        // kirim email ke admin assigned - Reply-To adalah user yang membuat tiket
        $user_email = $_SESSION['user_email'] ?? '';
        if (empty($user_email)) {
            // Fallback: ambil email dari database jika tidak ada di session
            $user_stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $user_stmt->execute([$_SESSION['user_id']]);
            $user_data = $user_stmt->fetch();
            $user_email = $user_data ? $user_data['email'] : '';
        }
        error_log("Creating ticket - User email for Reply-To: " . ($user_email ?: 'NOT FOUND'));
        $html_body = "<h2>New Ticket Assigned</h2><p>Dear {$admin['name']},<br><br>You have a new ticket assigned by {$_SESSION['user_name']}:</p>"
            . "<table style='margin-bottom:20px'><tr><td><b>Ticket ID</b></td><td>#$ticket_number</td></tr>"
            . "<tr><td><b>Category</b></td><td>$category</td></tr><tr><td><b>Description</b></td><td>$description</td></tr><tr><td><b>Priority</b></td><td>$priority</td></tr></table>"
            . "<p>Click <a href='http://localhost/helpdesk_system'>here</a> to see details in Helpdesk System.</p>";
        send_ticket_email($admin['email'], $admin['name'], "[Helpdesk] New Ticket Assigned #$ticket_number", $html_body, $admin['email'], $user_email);
    }
    // notifikasi ke user sendiri (jika perlu, misal "Ticket anda berhasil dibuat"), insert_notification($db, $_SESSION['user_id'], ...)
    
    error_log("create_ticket: Ticket created successfully - ID: " . $ticket_id . ", Number: " . $ticket_number);
    
    json_response([
        'success' => true,
        'message' => 'Ticket created successfully',
        'ticket' => $ticket
    ]);
}

function list_tickets($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    // For user, list their own tickets. For admin, list all.
    if ($_SESSION['user_role'] === 'admin') {
        $stmt = $db->query("SELECT * FROM tickets ORDER BY created_at DESC");
    } else {
        $stmt = $db->prepare("SELECT * FROM tickets WHERE reporter_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
    }
    $tickets = $stmt->fetchAll();
    
    json_response(['success' => true, 'tickets' => $tickets]);
}

function get_attachments($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $ticket_id = $_GET['ticket_id'] ?? null;
    if (!$ticket_id) {
        json_response(['success' => false, 'message' => 'Ticket ID required'], 400);
    }
    
    // Check if user has access to this ticket
    $stmt = $db->prepare("SELECT reporter_id FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    
    // Check if user is admin or ticket owner
    if ($_SESSION['user_role'] !== 'admin' && $ticket['reporter_id'] != $_SESSION['user_id']) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Get attachments
    $stmt = $db->prepare("SELECT * FROM attachments WHERE ticket_id = ? ORDER BY created_at ASC");
    $stmt->execute([$ticket_id]);
    $attachments = $stmt->fetchAll();
    
    json_response(['success' => true, 'attachments' => $attachments]);
}

function get_ticket_details($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    $ticket_id = $_GET['ticket_id'] ?? null;
    if (!$ticket_id) {
        json_response(['success' => false, 'message' => 'Ticket ID required'], 400);
    }
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND reporter_id = ?");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    $ticket = $stmt->fetch();
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found or access denied'], 404);
    }
    json_response(['success' => true, 'ticket' => $ticket]);
}

function update_user_ticket($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $ticket_id = intval($data['ticket_id'] ?? 0);
    // Category tidak perlu di-clean_input karena ENUM, langsung trim saja
    $category = trim($data['category'] ?? '');
    $description = clean_input($data['description'] ?? '');
    $user_priority = clean_input($data['user_priority'] ?? '');
    $assign_to = clean_input($data['assign_to'] ?? '');
    
    // Mapping kategori baru ke format database
    $categoryMapping = [
        'Jaringan & Keamanan' => 'Network',
        'Aplikasi & Sistem Internal' => 'System & Aplikasi',
        'Perangkat Keras' => 'Hardware',
        'Perangkat Lunak Umum & Akun' => 'Software',
        'Pengajuan pembuatan atau perubahan aplikasi sistem' => 'Pengajuan pembuatan atau perubahan aplikasi sistem'
    ];
    $dbCategory = $categoryMapping[$category] ?? $category;
    
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid ticket ID'], 400);
    }
    if (empty($category) || empty($description) || empty($user_priority) || empty($assign_to)) {
        json_response(['success' => false, 'message' => 'All fields are required'], 400);
    }
    
    $validCategories = ['Network', 'Software', 'Hardware', 'System & Aplikasi', 'Pengajuan pembuatan atau perubahan aplikasi sistem', 'Jaringan & Keamanan', 'Aplikasi & Sistem Internal', 'Perangkat Keras', 'Perangkat Lunak Umum & Akun'];
    if (!in_array($category, $validCategories)) {
        json_response(['success' => false, 'message' => 'Invalid category'], 400);
    }
    
    // Use database category for storage
    $category = $dbCategory;
    $stmt = $db->prepare("SELECT id FROM tickets WHERE id = ? AND reporter_id = ?");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Ticket not found or access denied'], 404);
    }
    $stmt = $db->prepare("UPDATE tickets SET category = ?, description = ?, priority = ?, assign_to = ? WHERE id = ?");
    $stmt->execute([$category, $description, $user_priority, $assign_to, $ticket_id]);
    json_response(['success' => true, 'message' => 'Ticket updated successfully']);
}

function delete_user_ticket($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $ticket_id = intval($data['ticket_id'] ?? 0);
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid ticket ID'], 400);
    }
    $stmt = $db->prepare("SELECT id FROM tickets WHERE id = ? AND reporter_id = ?");
    $stmt->execute([$ticket_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Ticket not found or access denied'], 404);
    }
    $db->prepare("DELETE FROM attachments WHERE ticket_id = ?")->execute([$ticket_id]);
    $stmt = $db->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    json_response(['success' => true, 'message' => 'Ticket deleted successfully']);
}

// === Update update_ticket for notif/email on status change to user (reporter) ===
function update_ticket($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $ticket_id = intval($data['ticket_id'] ?? 0);
    $status = clean_input($data['status'] ?? '');
    $start_date = clean_input($data['start_date'] ?? '');
    $end_date = clean_input($data['end_date'] ?? '');
    $sla_priority = clean_input($data['sla_priority'] ?? '');
    // Category tidak perlu di-clean_input karena ENUM, langsung trim saja
    $category = trim($data['category'] ?? '');
    $description = clean_input($data['description'] ?? '');
    $priority = clean_input($data['priority'] ?? '');
    $assign_to = clean_input($data['assign_to'] ?? '');
    $admin_comment = trim($data['admin_comment'] ?? ''); // Admin comment untuk memberikan reason kepada user
    
    // Mapping kategori baru ke format database
    $categoryMapping = [
        'Jaringan & Keamanan' => 'Network',
        'Aplikasi & Sistem Internal' => 'System & Aplikasi',
        'Perangkat Keras' => 'Hardware',
        'Perangkat Lunak Umum & Akun' => 'Software',
        'Pengajuan pembuatan atau perubahan aplikasi sistem' => 'Pengajuan pembuatan atau perubahan aplikasi sistem'
    ];
    $dbCategory = $categoryMapping[$category] ?? $category;
    
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid ticket ID'], 400);
    }
    
    // Validasi category jika ada
    if (!empty($category)) {
        $validCategories = ['Network', 'Software', 'Hardware', 'System & Aplikasi', 'Pengajuan pembuatan atau perubahan aplikasi sistem', 'Jaringan & Keamanan', 'Aplikasi & Sistem Internal', 'Perangkat Keras', 'Perangkat Lunak Umum & Akun'];
        if (!in_array($category, $validCategories)) {
            json_response(['success' => false, 'message' => 'Invalid category'], 400);
        }
        // Use database category for storage
        $category = $dbCategory;
    }
    
    // Get current ticket for calculations and permissions
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $current = $stmt->fetch();
    if (!$current) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    
    if (!empty($status)) {
        $updateFields[] = 'status = ?';
        $params[] = $status;
        
        // Auto-set start_date when status changes to "In Progress"
        // Always set to current date (system date) when admin marks ticket as In Progress
        if ($status === 'In Progress') {
            $start_date = date('Y-m-d'); // Set to today's date (system date)
            $updateFields[] = 'start_date = ?';
            $params[] = $start_date;
        }
        
        // Auto-set end_date when status changes to "Done"
        // Always set to current date (system date) when admin marks ticket as Done
        if ($status === 'Done') {
            $end_date = date('Y-m-d'); // Set to today's date (system date)
            $updateFields[] = 'end_date = ?';
            $params[] = $end_date;
        }
    }
    
    // Only add start_date if it's provided manually and status is not "In Progress"
    // (If status is "In Progress", it's already handled above with auto-set)
    if (!empty($start_date) && (empty($status) || $status !== 'In Progress')) {
        $updateFields[] = 'start_date = ?';
        $params[] = $start_date;
    }
    
    // Only add end_date if it's provided manually and status is not "Done"
    // (If status is "Done", it's already handled above with auto-set)
    if (!empty($end_date) && (empty($status) || $status !== 'Done')) {
        $updateFields[] = 'end_date = ?';
        $params[] = $end_date;
    }
    
    if (!empty($sla_priority)) {
        $updateFields[] = 'sla_priority = ?';
        $params[] = $sla_priority;
    }

    // Optional editable fields by admin
    if (!empty($category)) {
        $updateFields[] = 'category = ?';
        $params[] = $category;
    }
    if (!empty($description)) {
        $updateFields[] = 'description = ?';
        $params[] = $description;
    }
    if (!empty($priority)) {
        $updateFields[] = 'priority = ?';
        $params[] = $priority;
    }
    if (!empty($assign_to)) {
        $updateFields[] = 'assign_to = ?';
        $params[] = $assign_to;
    }
    
    // Handle admin comment (untuk memberikan reason kepada user)
    $old_admin_comment = $current['admin_comment'] ?? '';
    if (isset($data['admin_comment'])) {
        // Allow empty string to clear comment
        $updateFields[] = 'admin_comment = ?';
        $params[] = $admin_comment;
    }
    
    // Compute due_date if we have start_date or sla change
    $effectiveStart = !empty($start_date) ? $start_date : ($current['start_date'] ?? null);
    $effectiveSla = !empty($sla_priority) ? $sla_priority : ($current['sla_priority'] ?? '');
    if ($effectiveStart && $effectiveSla) {
        // SLA to hours mapping
        $slaMap = [
            'low' => 120,
            'medium' => 72,
            'high' => 8,
            'critical' => 4,
        ];
        $hours = $slaMap[strtolower($effectiveSla)] ?? null;
        if ($hours !== null) {
            $dueDate = (new DateTime($effectiveStart))->modify("+{$hours} hours")->format('Y-m-d');
            $updateFields[] = 'due_date = ?';
            $params[] = $dueDate;
        }
    }

    // First response tracking: set when status first changed by assigned admin
    if (!empty($status) && $status !== 'Open' && empty($current['first_response_at']) && $current['assign_to'] === ($_SESSION['user_email'] ?? '')) {
        $updateFields[] = 'first_response_at = NOW()';
        // Calculate minutes from created_at to now
        $updateFields[] = 'response_minutes = TIMESTAMPDIFF(MINUTE, created_at, NOW())';
    }

    if (empty($updateFields)) {
        json_response(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $params[] = $ticket_id;
    
    $sql = "UPDATE tickets SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    // Ambil info ticket sebelum update untuk cek reassign
    $old_assign_to = $current['assign_to'] ?? '';
    
    // Ambil info ticket setelah update
    $stmt_ticket = $db->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt_ticket->execute([$ticket_id]);
    $ticket = $stmt_ticket->fetch();
    
    // Jika ada reassign (assign_to berubah), kirim notifikasi ke admin baru
    if (!empty($assign_to) && $old_assign_to !== $assign_to) {
        $new_admin_stmt = $db->prepare("SELECT id, email, name FROM users WHERE email = ?");
        $new_admin_stmt->execute([$assign_to]);
        $new_admin = $new_admin_stmt->fetch();
        if ($new_admin) {
            $notif_msg = "You have been assigned a new ticket #{$ticket['ticket_number']} from {$current['reporter_name']}";
            insert_notification($db, $new_admin['id'], $notif_msg);
            // Ambil email reporter untuk digunakan sebagai sender
            $reporter_stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $reporter_stmt->execute([$ticket['reporter_id']]);
            $reporter = $reporter_stmt->fetch();
            $reporter_email = $reporter ? $reporter['email'] : '';
            $html_body = "<h2>New Ticket Assigned</h2><p>Dear {$new_admin['name']},<br><br>You have been assigned a new ticket:</p>"
                . "<table style='margin-bottom:20px'><tr><td><b>Ticket ID</b></td><td>#{$ticket['ticket_number']}</td></tr>"
                . "<tr><td><b>Reporter</b></td><td>{$current['reporter_name']}</td></tr>"
                . "<tr><td><b>Category</b></td><td>" . (!empty($category) ? $category : $ticket['category']) . "</td></tr>"
                . "<tr><td><b>Description</b></td><td>" . (!empty($description) ? $description : $ticket['description']) . "</td></tr>"
                . "<tr><td><b>Priority</b></td><td>" . (!empty($priority) ? $priority : $ticket['priority']) . "</td></tr></table>"
                . "<p>Click <a href='http://localhost/helpdesk_system'>here</a> to see details in Helpdesk System.</p>";
            send_ticket_email($new_admin['email'], $new_admin['name'], "[Helpdesk] New Ticket Assigned #{$ticket['ticket_number']}", $html_body, $assign_to, $reporter_email);
        }
    }
    
    // Ambil reporter info
    $user_stmt = $db->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $user_stmt->execute([$ticket['reporter_id']]);
    $user = $user_stmt->fetch();
    if ($user) {
        $changes = [];
        if (!empty($status)) { $changes[] = "Status: $status"; }
        if (!empty($category)) { $changes[] = "Category: {$current['category']} → $category"; }
        if (!empty($priority)) { $changes[] = "Priority: {$current['priority']} → $priority"; }
        if (!empty($assign_to) && $old_assign_to !== $assign_to) { 
            $oldAdminName = explode('@', $old_assign_to)[0];
            $newAdminName = explode('@', $assign_to)[0];
            $changes[] = "Reassigned to: $oldAdminName → $newAdminName"; 
        }
        if (!empty($description)) { $changes[] = "Description updated"; }
        
        // Check if admin comment was added or updated
        $commentAdded = false;
        if (isset($data['admin_comment']) && !empty($admin_comment) && $admin_comment !== $old_admin_comment) {
            $commentAdded = true;
            $changes[] = "Admin comment added";
            // Send specific notification for comment
            $comment_notif_msg = "Admin IT has added a comment on ticket #{$ticket['ticket_number']}";
            insert_notification($db, $user['id'], $comment_notif_msg);
        }
        
        $changeText = empty($changes) ? 'Ticket updated' : ('Updated: ' . implode(', ', $changes));
        insert_notification($db, $user['id'], "Ticket #{$ticket['ticket_number']} updated. $changeText");
        
        // Prepare email body
        $html_body = "<h2>Ticket Update Notification</h2><p>Dear {$user['name']},<br><br>Your ticket has been updated by Helpdesk.</p>"
            . "<table style='margin-bottom:20px'>"
            . "<tr><td><b>Ticket ID</b></td><td>#{$ticket['ticket_number']}</td></tr>"
            . "<tr><td><b>Category</b></td><td>" . (!empty($category) ? htmlspecialchars($category) : htmlspecialchars($ticket['category'])) . "</td></tr>"
            . "<tr><td><b>Description</b></td><td>" . (!empty($description) ? htmlspecialchars($description) : htmlspecialchars($ticket['description'])) . "</td></tr>"
            . (!empty($status) ? "<tr><td><b>Status</b></td><td>" . htmlspecialchars($status) . "</td></tr>" : '')
            . (!empty($priority) ? "<tr><td><b>Priority</b></td><td>" . htmlspecialchars($priority) . "</td></tr>" : '')
            . (!empty($assign_to) && $old_assign_to !== $assign_to ? "<tr><td><b>Assigned to</b></td><td>" . htmlspecialchars(explode('@', $assign_to)[0]) . "</td></tr>" : '');
        
        // Add admin comment to email if it was added
        if ($commentAdded && !empty($admin_comment)) {
            $html_body .= "<tr><td colspan='2' style='padding-top:15px;'><b>Admin Comment:</b></td></tr>"
                . "<tr><td colspan='2' style='background-color:#f5f5f5;padding:10px;border-left:3px solid #4f46e5;'>" . nl2br(htmlspecialchars($admin_comment)) . "</td></tr>";
        }
        
        $html_body .= "</table>"
            . "<p>Click <a href='http://localhost/helpdesk_system'>here</a> for details.</p>";
        
        $assign_email = !empty($assign_to) ? $assign_to : $ticket['assign_to'];
        
        // Send email with appropriate subject
        $email_subject = $commentAdded ? "[Helpdesk] New Comment on Ticket #{$ticket['ticket_number']}" : "[Helpdesk] Ticket Update #{$ticket['ticket_number']}";
        send_ticket_email($user['email'], $user['name'], $email_subject, $html_body, $assign_email);
    }
    
    json_response(['success' => true, 'message' => 'Ticket updated successfully']);
}

function admin_delete_ticket($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $ticket_id = intval($data['ticket_id'] ?? 0);
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Invalid ticket ID'], 400);
    }
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    $db->prepare("DELETE FROM attachments WHERE ticket_id = ?")->execute([$ticket_id]);
    $db->prepare("DELETE FROM tickets WHERE id = ?")->execute([$ticket_id]);

    $user_stmt = $db->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $user_stmt->execute([$ticket['reporter_id']]);
    $user = $user_stmt->fetch();
    if ($user) {
        insert_notification($db, $user['id'], "Your ticket #{$ticket['ticket_number']} has been deleted by Helpdesk.");
        $html_body = "<h2>Ticket Deleted</h2><p>Dear {$user['name']},<br><br>Your ticket has been deleted by Helpdesk.</p>"
            . "<table style='margin-bottom:20px'>"
            . "<tr><td><b>Ticket ID</b></td><td>#{$ticket['ticket_number']}</td></tr>"
            . "<tr><td><b>Category</b></td><td>{$ticket['category']}</td></tr>"
            . "<tr><td><b>Description</b></td><td>{$ticket['description']}</td></tr>"
            . "</table>";
        $assign_email = $ticket['assign_to'];
        send_ticket_email($user['email'], $user['name'], "[Helpdesk] Ticket Deleted #{$ticket['ticket_number']}", $html_body, $assign_email);
    }

    json_response(['success' => true, 'message' => 'Ticket deleted successfully']);
}

function get_ticket_stats($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Total tickets
    $stmt = $db->query("SELECT COUNT(*) as total FROM tickets");
    $total = $stmt->fetch()['total'];
    
    // Count by status
    $statusCounts = [];
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    foreach ($stmt->fetchAll() as $row) {
        $statusCounts[$row['status']] = $row['count'];
    }
    
    // Count by category
    $categoryCounts = [];
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM tickets GROUP BY category");
    foreach ($stmt->fetchAll() as $row) {
        $categoryCounts[$row['category']] = $row['count'];
    }
    
    // Recent activity (last 10 tickets created)
    $stmt = $db->query("SELECT ticket_number, reporter_name, created_at FROM tickets ORDER BY created_at DESC LIMIT 10");
    $recent_activity = $stmt->fetchAll();
    
    json_response([
        'success' => true,
        'stats' => [
            'total' => $total,
            'status' => $statusCounts,
            'categories' => $categoryCounts,
            'recent_activity' => $recent_activity
        ]
    ]);
}

// --- User Management Functions (Admin only) ---

function list_users($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $stmt = $db->query("SELECT id, username, name, email, department, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    json_response(['success' => true, 'users' => $users]);
}

function create_user($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = strtolower(trim($data['username'] ?? ''));
    $name = clean_input($data['name'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $dept = clean_input($data['department'] ?? '');
    $password = $data['password'] ?? '';
    $role = clean_input($data['role'] ?? 'user');
    
    if (empty($username) || empty($name) || empty($email) || empty($dept) || empty($password)) {
        json_response(['success' => false, 'message' => 'All fields are required'], 400);
    }
    
    if (strlen($password) < 6) {
        json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Validate username format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        json_response(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores'], 400);
    }
    
    // Check if username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Username already exists'], 409);
    }
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Email already exists'], 409);
    }
    
    // Override role if email is admin
    if (in_array($email, ADMIN_EMAILS)) {
        $role = 'admin';
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $name, $email, $dept, $hashedPassword, $role]);
    
    json_response([
        'success' => true,
        'message' => 'User created successfully',
        'user_id' => $db->lastInsertId()
    ]);
}

function update_user($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = intval($data['user_id'] ?? 0);
    $name = clean_input($data['name'] ?? '');
    $dept = clean_input($data['department'] ?? '');
    $role = clean_input($data['role'] ?? 'user');
    $password = $data['password'] ?? null;
    
    if ($userId <= 0 || empty($name) || empty($dept)) {
        json_response(['success' => false, 'message' => 'Invalid input'], 400);
    }
    
    // Get user email
    $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Override role if email is admin
    if (in_array($user['email'], ADMIN_EMAILS)) {
        $role = 'admin';
    }
    
    // Build update query
    if ($password && strlen($password) >= 6) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET name = ?, department = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $dept, $role, $hashedPassword, $userId]);
    } else {
        $stmt = $db->prepare("UPDATE users SET name = ?, department = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $dept, $role, $userId]);
    }
    
    json_response(['success' => true, 'message' => 'User updated successfully']);
}

function delete_user($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = intval($data['user_id'] ?? 0);
    
    if ($userId <= 0) {
        json_response(['success' => false, 'message' => 'Invalid user ID'], 400);
    }
    
    // Check if user is admin
    $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Prevent deleting main admin accounts
    if (in_array($user['email'], ADMIN_EMAILS)) {
        json_response(['success' => false, 'message' => 'Main admin accounts cannot be deleted'], 403);
    }
    
    // Delete user (CASCADE will delete their tickets)
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    json_response(['success' => true, 'message' => 'User deleted successfully']);
}

// --- Notification Functions ---

function get_notifications($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get unread count
    $stmt = $db->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['unread_count'];
    
    // Get all notifications (latest first)
    $stmt = $db->prepare("SELECT id, message, url, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    
    json_response([
        'success' => true,
        'unread_count' => (int)$unread_count,
        'notifications' => $notifications
    ]);
}

function mark_notification_read($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $notification_id = intval($data['notification_id'] ?? 0);
    
    if ($notification_id <= 0) {
        json_response(['success' => false, 'message' => 'Notification ID required'], 400);
    }
    
    // Verify notification belongs to user
    $stmt = $db->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Notification not found'], 404);
    }
    
    // Mark as read
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$notification_id]);
    
    json_response(['success' => true, 'message' => 'Notification marked as read']);
}

function mark_all_notifications_read($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Mark all notifications as read for this user
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    
    $affected_rows = $stmt->rowCount();
    
    json_response([
        'success' => true,
        'message' => 'All notifications marked as read',
        'affected_count' => $affected_rows
    ]);
}

function delete_notification($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $notification_id = intval($data['notification_id'] ?? 0);
    
    if ($notification_id <= 0) {
        json_response(['success' => false, 'message' => 'Notification ID required'], 400);
    }
    
    // Verify notification belongs to user
    $stmt = $db->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notification_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Notification not found'], 404);
    }
    
    // Delete notification
    $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$notification_id]);
    
    json_response(['success' => true, 'message' => 'Notification deleted']);
}

function clear_read_notifications($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Delete all read notifications for this user
    $stmt = $db->prepare("DELETE FROM notifications WHERE user_id = ? AND is_read = 1");
    $stmt->execute([$user_id]);
    
    $deleted_count = $stmt->rowCount();
    
    json_response([
        'success' => true,
        'message' => 'Read notifications cleared',
        'deleted_count' => $deleted_count
    ]);
}

// === SDLC Form Functions ===

function save_sdlc_data($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $ticket_id = intval($data['ticket_id'] ?? 0);
    $ticket_number = clean_input($data['ticket_number'] ?? '');
    
    if ($ticket_id <= 0 || empty($ticket_number)) {
        json_response(['success' => false, 'message' => 'Ticket ID and ticket number are required'], 400);
    }
    
    // Verify ticket belongs to user
    $stmt = $db->prepare("SELECT id, reporter_id FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    
    if ($ticket['reporter_id'] != $_SESSION['user_id']) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Validate required fields
    $required_fields = ['judul', 'unit', 'nama', 'jabatan', 'email', 'tanggal', 'prioritas', 
                        'latarBelakang', 'masalah', 'dampakWaktu', 'dampakTransparansi', 
                        'dampakBiaya', 'dampakAkuntabilitas', 'tujuan', 'manfaat1', 'manfaat2',
                        'pemohonTtd', 'manajerTtd'];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        error_log("Missing required SDLC fields: " . implode(', ', $missing_fields));
        json_response(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing_fields)], 400);
    }
    
    // Validate userRoles and features
    if (empty($data['userRoles']) || !is_array($data['userRoles']) || count($data['userRoles']) === 0) {
        json_response(['success' => false, 'message' => 'Pengguna utama sistem harus diisi minimal 1'], 400);
    }
    
    if (empty($data['features']) || !is_array($data['features']) || count($data['features']) < 2) {
        json_response(['success' => false, 'message' => 'Fitur kunci harus diisi minimal 2'], 400);
    }
    
    // Prepare SDLC data
    $sdlc_data = [
        'ticket_id' => $ticket_id,
        'ticket_number' => $ticket_number,
        'judul_permintaan' => clean_input($data['judul'] ?? ''),
        'unit_departemen' => clean_input($data['unit'] ?? ''),
        'nama_pemohon' => clean_input($data['nama'] ?? ''),
        'jabatan' => clean_input($data['jabatan'] ?? ''),
        'email_pemohon' => clean_input($data['email'] ?? ''),
        'tanggal_pengajuan' => clean_input($data['tanggal'] ?? ''),
        'prioritas_diusulkan' => clean_input($data['prioritas'] ?? 'Sedang'),
        'latar_belakang' => clean_input($data['latarBelakang'] ?? ''),
        'pernyataan_masalah' => clean_input($data['masalah'] ?? ''),
        'dampak_waktu' => clean_input($data['dampakWaktu'] ?? ''),
        'dampak_transparansi' => clean_input($data['dampakTransparansi'] ?? ''),
        'dampak_biaya' => clean_input($data['dampakBiaya'] ?? ''),
        'dampak_akuntabilitas' => clean_input($data['dampakAkuntabilitas'] ?? ''),
        'tujuan_utama' => clean_input($data['tujuan'] ?? ''),
        'manfaat_1' => clean_input($data['manfaat1'] ?? ''),
        'manfaat_2' => clean_input($data['manfaat2'] ?? ''),
        'manfaat_3' => clean_input($data['manfaat3'] ?? ''),
        'pengguna_utama_sistem' => json_encode($data['userRoles'] ?? []),
        'fitur_kunci' => json_encode($data['features'] ?? []),
        'pemohon_ttd' => clean_input($data['pemohonTtd'] ?? ''),
        'manajer_ttd' => clean_input($data['manajerTtd'] ?? '')
    ];
    
    // Check if SDLC data already exists
    $check_stmt = $db->prepare("SELECT id FROM ticket_sdlc_data WHERE ticket_id = ?");
    $check_stmt->execute([$ticket_id]);
    $existing = $check_stmt->fetch();
    
    try {
        if ($existing) {
            // Update existing record
            $sql = "UPDATE ticket_sdlc_data SET 
                judul_permintaan = ?, unit_departemen = ?, nama_pemohon = ?, jabatan = ?, 
                email_pemohon = ?, tanggal_pengajuan = ?, prioritas_diusulkan = ?,
                latar_belakang = ?, pernyataan_masalah = ?, dampak_waktu = ?, 
                dampak_transparansi = ?, dampak_biaya = ?, dampak_akuntabilitas = ?,
                tujuan_utama = ?, manfaat_1 = ?, manfaat_2 = ?, manfaat_3 = ?,
                pengguna_utama_sistem = ?, fitur_kunci = ?, pemohon_ttd = ?, manajer_ttd = ?
                WHERE ticket_id = ?";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $sdlc_data['judul_permintaan'], $sdlc_data['unit_departemen'], 
                $sdlc_data['nama_pemohon'], $sdlc_data['jabatan'],
                $sdlc_data['email_pemohon'], $sdlc_data['tanggal_pengajuan'], 
                $sdlc_data['prioritas_diusulkan'], $sdlc_data['latar_belakang'],
                $sdlc_data['pernyataan_masalah'], $sdlc_data['dampak_waktu'],
                $sdlc_data['dampak_transparansi'], $sdlc_data['dampak_biaya'],
                $sdlc_data['dampak_akuntabilitas'], $sdlc_data['tujuan_utama'],
                $sdlc_data['manfaat_1'], $sdlc_data['manfaat_2'], $sdlc_data['manfaat_3'],
                $sdlc_data['pengguna_utama_sistem'], $sdlc_data['fitur_kunci'],
                $sdlc_data['pemohon_ttd'], $sdlc_data['manajer_ttd'], $ticket_id
            ]);
        } else {
            // Insert new record
            $sql = "INSERT INTO ticket_sdlc_data (
                ticket_id, ticket_number, judul_permintaan, unit_departemen, nama_pemohon, 
                jabatan, email_pemohon, tanggal_pengajuan, prioritas_diusulkan,
                latar_belakang, pernyataan_masalah, dampak_waktu, dampak_transparansi, 
                dampak_biaya, dampak_akuntabilitas, tujuan_utama, manfaat_1, manfaat_2, 
                manfaat_3, pengguna_utama_sistem, fitur_kunci, pemohon_ttd, manajer_ttd
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([
                $sdlc_data['ticket_id'], $sdlc_data['ticket_number'], 
                $sdlc_data['judul_permintaan'], $sdlc_data['unit_departemen'],
                $sdlc_data['nama_pemohon'], $sdlc_data['jabatan'], 
                $sdlc_data['email_pemohon'], $sdlc_data['tanggal_pengajuan'],
                $sdlc_data['prioritas_diusulkan'], $sdlc_data['latar_belakang'],
                $sdlc_data['pernyataan_masalah'], $sdlc_data['dampak_waktu'],
                $sdlc_data['dampak_transparansi'], $sdlc_data['dampak_biaya'],
                $sdlc_data['dampak_akuntabilitas'], $sdlc_data['tujuan_utama'],
                $sdlc_data['manfaat_1'], $sdlc_data['manfaat_2'], $sdlc_data['manfaat_3'],
                $sdlc_data['pengguna_utama_sistem'], $sdlc_data['fitur_kunci'],
                $sdlc_data['pemohon_ttd'], $sdlc_data['manajer_ttd']
            ]);
        }
        
        if (!$result) {
            $error = $stmt->errorInfo();
            error_log("Failed to save SDLC data: " . print_r($error, true));
            json_response(['success' => false, 'message' => 'Failed to save SDLC data: ' . ($error[2] ?? 'Unknown error')], 500);
        }
        
        json_response([
            'success' => true,
            'message' => 'SDLC data saved successfully'
        ]);
    } catch (Exception $e) {
        error_log("Exception in save_sdlc_data: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        json_response(['success' => false, 'message' => 'Error saving SDLC data: ' . $e->getMessage()], 500);
    }
}

function get_sdlc_data($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Ticket ID required'], 400);
    }
    
    // Verify ticket belongs to user or user is admin
    $stmt = $db->prepare("SELECT id, reporter_id FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    
    $is_admin = ($_SESSION['user_role'] ?? '') === 'admin';
    if (!$is_admin && $ticket['reporter_id'] != $_SESSION['user_id']) {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Get SDLC data
    $stmt = $db->prepare("SELECT * FROM ticket_sdlc_data WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $sdlc_data = $stmt->fetch();
    
    if (!$sdlc_data) {
        json_response(['success' => false, 'message' => 'SDLC data not found'], 404);
    }
    
    // Decode JSON fields
    $sdlc_data['pengguna_utama_sistem'] = json_decode($sdlc_data['pengguna_utama_sistem'] ?? '[]', true);
    $sdlc_data['fitur_kunci'] = json_decode($sdlc_data['fitur_kunci'] ?? '[]', true);
    
    json_response([
        'success' => true,
        'data' => $sdlc_data
    ]);
}

function view_sdlc_html($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        // Return HTML error page instead of JSON
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Unauthorized</title></head><body>';
        echo '<h1>Unauthorized</h1><p>You must be logged in as admin to view this form.</p>';
        echo '</body></html>';
        exit;
    }
    
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    if ($ticket_id <= 0) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body>';
        echo '<h1>Error</h1><p>Ticket ID required</p>';
        echo '</body></html>';
        exit;
    }
    
    // Get ticket info
    $stmt = $db->prepare("SELECT id, ticket_number, category FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body>';
        echo '<h1>Error</h1><p>Ticket not found</p>';
        echo '</body></html>';
        exit;
    }
    
    // Get SDLC data
    $stmt = $db->prepare("SELECT * FROM ticket_sdlc_data WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $sdlc_data = $stmt->fetch();
    
    if (!$sdlc_data) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body>';
        echo '<h1>Error</h1><p>SDLC data not found for this ticket</p>';
        echo '</body></html>';
        exit;
    }
    
    // Decode JSON fields
    $user_roles = json_decode($sdlc_data['pengguna_utama_sistem'] ?? '[]', true);
    $features = json_decode($sdlc_data['fitur_kunci'] ?? '[]', true);
    
    // Generate HTML for viewing in browser
    $html = generate_sdlc_html($sdlc_data, $ticket, $user_roles, $features);
    
    // Add print button and download buttons at the top
    $printButtons = '
    <div style="text-align: center; margin-bottom: 20px; padding: 15px; background-color: #f3f4f6; border-radius: 8px;">
        <button onclick="window.print()" style="background-color: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; font-size: 14px;">
            <i class="fas fa-print"></i> Print / Save as PDF
        </button>
        <a href="api.php?action=download_sdlc&ticket_id=' . $ticket_id . '" style="background-color: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; font-size: 14px; text-decoration: none; display: inline-block;">
            <i class="fas fa-download"></i> Download HTML
        </a>
        <a href="api.php?action=download_sdlc_pdf&ticket_id=' . $ticket_id . '" style="background-color: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; font-size: 14px; text-decoration: none; display: inline-block;">
            <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <a href="javascript:history.back()" style="background-color: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; font-size: 14px; text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>';
    
    // Insert buttons after opening body tag
    $html = str_replace('<body>', '<body>' . $printButtons, $html);
    
    // Set headers for viewing in browser (not download)
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

function download_sdlc($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Ticket ID required'], 400);
    }
    
    // Get ticket info
    $stmt = $db->prepare("SELECT id, ticket_number, category FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    
    // Get SDLC data
    $stmt = $db->prepare("SELECT * FROM ticket_sdlc_data WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $sdlc_data = $stmt->fetch();
    
    if (!$sdlc_data) {
        json_response(['success' => false, 'message' => 'SDLC data not found'], 404);
    }
    
    // Decode JSON fields
    $user_roles = json_decode($sdlc_data['pengguna_utama_sistem'] ?? '[]', true);
    $features = json_decode($sdlc_data['fitur_kunci'] ?? '[]', true);
    
    // Generate HTML for download
    $html = generate_sdlc_html($sdlc_data, $ticket, $user_roles, $features);
    
    // Set headers for download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="SDLC_Form_' . $ticket['ticket_number'] . '_' . date('Y-m-d') . '.html"');
    echo $html;
    exit;
}

function generate_sdlc_html($sdlc_data, $ticket, $user_roles, $features) {
    $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMULIR PERMINTAAN SISTEM / PERUBAHAN - ' . htmlspecialchars($ticket['ticket_number']) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #666;
            margin: 10px 0 0 0;
            font-size: 18px;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .section-title.green {
            color: #10b981;
            border-bottom-color: #10b981;
        }
        .section-title.purple {
            color: #8b5cf6;
            border-bottom-color: #8b5cf6;
        }
        .field-group {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }
        .field-value {
            color: #1f2937;
            padding: 8px;
            background-color: #f9fafb;
            border-left: 3px solid #2563eb;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .impact-box {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .impact-box.red {
            background-color: #fef2f2;
            border-color: #ef4444;
        }
        .impact-box.yellow {
            background-color: #fefce8;
            border-color: #eab308;
        }
        .impact-box.indigo {
            background-color: #eef2ff;
            border-color: #6366f1;
        }
        .impact-box.purple {
            background-color: #faf5ff;
            border-color: #a855f7;
        }
        .list-item {
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .signature-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px dashed #d1d5db;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            margin: 10px;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
        }
        @media print {
            body {
                background-color: white;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 15px;">
                <img src="assets/images/Logo BVT - Primary.png" alt="BVT Logo" style="max-height: 80px; max-width: 200px; object-fit: contain;">
            </div>
            <h1>FORMULIR PERMINTAAN SISTEM / PERUBAHAN</h1>
            <h2>Yayasan Perhutanan Bambu Sosial</h2>
            <p style="margin-top: 10px; color: #666;">Ticket Number: <strong>' . htmlspecialchars($ticket['ticket_number']) . '</strong></p>
        </div>
        
        <!-- BAGIAN A: INFORMASI UMUM -->
        <div class="section">
            <div class="section-title">BAGIAN A: INFORMASI UMUM</div>
            <div class="grid-2">
                <div class="field-group">
                    <span class="field-label">Judul Permintaan</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['judul_permintaan']) . '</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Unit/Departemen Pemohon</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['unit_departemen']) . '</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Nama Pemohon</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['nama_pemohon']) . '</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Jabatan</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['jabatan']) . '</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Email</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['email_pemohon']) . '</div>
                </div>
                <div class="field-group">
                    <span class="field-label">Tanggal Pengajuan</span>
                    <div class="field-value">' . htmlspecialchars($sdlc_data['tanggal_pengajuan']) . '</div>
                </div>
            </div>
            <div class="field-group">
                <span class="field-label">Prioritas yang Diusulkan</span>
                <div class="field-value">' . htmlspecialchars($sdlc_data['prioritas_diusulkan']) . '</div>
            </div>
        </div>
        
        <!-- BAGIAN B: DESKRIPSI KEBUTUHAN -->
        <div class="section">
            <div class="section-title green">BAGIAN B: DESKRIPSI KEBUTUHAN</div>
            <div class="field-group">
                <span class="field-label">1. Latar Belakang & Situasi Saat Ini</span>
                <div class="field-value">' . nl2br(htmlspecialchars($sdlc_data['latar_belakang'])) . '</div>
            </div>
            <div class="field-group">
                <span class="field-label">2. Pernyataan Masalah</span>
                <div class="field-value">' . nl2br(htmlspecialchars($sdlc_data['pernyataan_masalah'])) . '</div>
            </div>
            <div class="field-group">
                <span class="field-label">3. Dampak Masalah Terhadap Organisasi</span>
                <div class="impact-box red">
                    <span class="field-label" style="color: #dc2626;">Efisiensi Waktu</span>
                    <div class="field-value" style="background-color: transparent; border: none; padding: 5px 0;">' . nl2br(htmlspecialchars($sdlc_data['dampak_waktu'])) . '</div>
                </div>
                <div class="impact-box yellow">
                    <span class="field-label" style="color: #ca8a04;">Transparansi/Visibilitas</span>
                    <div class="field-value" style="background-color: transparent; border: none; padding: 5px 0;">' . nl2br(htmlspecialchars($sdlc_data['dampak_transparansi'])) . '</div>
                </div>
                <div class="impact-box indigo">
                    <span class="field-label" style="color: #4f46e5;">Kontrol Anggaran/Biaya</span>
                    <div class="field-value" style="background-color: transparent; border: none; padding: 5px 0;">' . nl2br(htmlspecialchars($sdlc_data['dampak_biaya'])) . '</div>
                </div>
                <div class="impact-box purple">
                    <span class="field-label" style="color: #9333ea;">Akuntabilitas</span>
                    <div class="field-value" style="background-color: transparent; border: none; padding: 5px 0;">' . nl2br(htmlspecialchars($sdlc_data['dampak_akuntabilitas'])) . '</div>
                </div>
            </div>
        </div>
        
        <!-- BAGIAN C: SOLUSI YANG DIUSULKAN -->
        <div class="section">
            <div class="section-title purple">BAGIAN C: SOLUSI YANG DIUSULKAN</div>
            <div class="field-group">
                <span class="field-label">1. Tujuan Utama (SMART)</span>
                <div class="field-value">' . nl2br(htmlspecialchars($sdlc_data['tujuan_utama'])) . '</div>
            </div>
            <div class="field-group">
                <span class="field-label">2. Manfaat yang Diharapkan</span>
                <div class="field-value">
                    <div class="list-item">1. ' . htmlspecialchars($sdlc_data['manfaat_1']) . '</div>
                    <div class="list-item">2. ' . htmlspecialchars($sdlc_data['manfaat_2']) . '</div>';
    if (!empty($sdlc_data['manfaat_3'])) {
        $html .= '<div class="list-item">3. ' . htmlspecialchars($sdlc_data['manfaat_3']) . '</div>';
    }
    $html .= '</div>
            </div>
            <div class="field-group">
                <span class="field-label">3. Pengguna Utama Sistem</span>
                <div class="field-value">';
    foreach ($user_roles as $index => $role) {
        $html .= '<div class="list-item">' . ($index + 1) . '. ' . htmlspecialchars($role['role'] ?? '') . ' - ' . htmlspecialchars($role['function'] ?? '') . '</div>';
    }
    $html .= '</div>
            </div>
            <div class="field-group">
                <span class="field-label">4. Fitur-fitur Kunci yang Dibutuhkan</span>
                <div class="field-value">';
    foreach ($features as $index => $feature) {
        $html .= '<div class="list-item">' . ($index + 1) . '. ' . htmlspecialchars($feature) . '</div>';
    }
    $html .= '</div>
            </div>
        </div>
        
        <!-- PERSETUJUAN -->
        <div class="section signature-section">
            <div class="section-title">PERSETUJUAN PENGAJUAN</div>
            <div style="text-align: center;">
                <div class="signature-box">
                    <span class="field-label">Nama Pemohon</span>
                    <div style="margin-top: 40px; padding-top: 10px; border-top: 1px solid #d1d5db;">
                        ' . htmlspecialchars($sdlc_data['pemohon_ttd']) . '
                    </div>
                </div>
                <div class="signature-box">
                    <span class="field-label">Nama Kepala/Manajer Departemen</span>
                    <div style="margin-top: 40px; padding-top: 10px; border-top: 1px solid #d1d5db;">
                        ' . htmlspecialchars($sdlc_data['manajer_ttd']) . '
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #666; font-size: 12px;">
            <p>Dokumen ini di-generate otomatis dari IT Helpdesk System pada ' . date('d F Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

function download_sdlc_pdf($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    if ($ticket_id <= 0) {
        json_response(['success' => false, 'message' => 'Ticket ID required'], 400);
    }
    
    // Get ticket info
    $stmt = $db->prepare("SELECT id, ticket_number, category FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        json_response(['success' => false, 'message' => 'Ticket not found'], 404);
    }
    
    // Get SDLC data
    $stmt = $db->prepare("SELECT * FROM ticket_sdlc_data WHERE ticket_id = ?");
    $stmt->execute([$ticket_id]);
    $sdlc_data = $stmt->fetch();
    
    if (!$sdlc_data) {
        json_response(['success' => false, 'message' => 'SDLC data not found'], 404);
    }
    
    // Decode JSON fields
    $user_roles = json_decode($sdlc_data['pengguna_utama_sistem'] ?? '[]', true);
    $features = json_decode($sdlc_data['fitur_kunci'] ?? '[]', true);
    
    // Check if mPDF is available
    $mpdf_available = false;
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $mpdf_available = class_exists('Mpdf\Mpdf');
    }
    
    if (!$mpdf_available) {
        // Fallback: Return HTML with instructions to use browser print-to-PDF
        $html = generate_sdlc_html($sdlc_data, $ticket, $user_roles, $features);
        $fallback_html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SDLC Form - ' . htmlspecialchars($ticket['ticket_number']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .info-box { background-color: #fef3c7; border: 2px solid #f59e0b; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info-box h3 { margin-top: 0; color: #92400e; }
        .info-box p { margin: 5px 0; }
        @media print {
            .info-box { display: none; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <div class="info-box">
        <h3>PDF Library Not Installed</h3>
        <p>To download as PDF, please use your browser\'s print function:</p>
        <ol>
            <li>Click the "Print" button below</li>
            <li>In the print dialog, select "Save as PDF" as the destination</li>
            <li>Click Save</li>
        </ol>
        <button class="print-button" onclick="window.print()" style="background-color: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">
            Print / Save as PDF
        </button>
    </div>
    ' . $html . '
</body>
</html>';
        
        header('Content-Type: text/html; charset=UTF-8');
        echo $fallback_html;
        exit;
    }
    
    // Generate HTML for PDF - convert logo to base64 for PDF compatibility
    $html = generate_sdlc_html($sdlc_data, $ticket, $user_roles, $features);
    
    // For PDF, convert logo image to base64
    $logo_path = __DIR__ . '/assets/images/Logo BVT - Primary.png';
    if (file_exists($logo_path)) {
        $logo_data = file_get_contents($logo_path);
        $logo_base64 = base64_encode($logo_data);
        $logo_mime = mime_content_type($logo_path);
        $logo_src = 'data:' . $logo_mime . ';base64,' . $logo_base64;
        // Replace logo src in HTML for PDF
        $html = str_replace('src="assets/images/Logo BVT - Primary.png"', 'src="' . $logo_src . '"', $html);
    }
    
    try {
        // Create mPDF instance
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);
        
        // Set document properties
        $mpdf->SetTitle('SDLC Form - ' . $ticket['ticket_number']);
        $mpdf->SetAuthor('IT Helpdesk System BVT');
        $mpdf->SetSubject('Formulir Permintaan Sistem / Perubahan');
        
        // Write HTML content
        $mpdf->WriteHTML($html);
        
        // Output PDF
        $filename = 'SDLC_Form_' . $ticket['ticket_number'] . '_' . date('Y-m-d') . '.pdf';
        $mpdf->Output($filename, 'D'); // D = Download
        exit;
        
    } catch (Exception $e) {
        error_log("Error generating PDF: " . $e->getMessage());
        // Fallback to HTML download
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="SDLC_Form_' . $ticket['ticket_number'] . '_' . date('Y-m-d') . '.html"');
        echo $html;
        exit;
    }
}

// --- Report Functions ---

function get_admin_list($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Get all admin emails from tickets (unique assign_to values)
    $stmt = $db->query("SELECT DISTINCT assign_to FROM tickets WHERE assign_to IS NOT NULL AND assign_to != '' ORDER BY assign_to");
    $adminEmails = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Also get admin users from users table
    $stmt = $db->query("SELECT DISTINCT email FROM users WHERE role = 'admin' ORDER BY email");
    $adminUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Merge and remove duplicates
    $allAdminEmails = array_unique(array_merge($adminEmails, $adminUsers));
    sort($allAdminEmails);
    
    // Get username for each admin email
    $admins = [];
    foreach ($allAdminEmails as $email) {
        
        $stmt = $db->prepare("SELECT username, name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Use username if available, otherwise use name, otherwise use email prefix
            $displayName = !empty($user['username']) ? ucfirst($user['username']) : 
                          (!empty($user['name']) ? $user['name'] : ucfirst(explode('@', $email)[0]));
        } else {
            // If user not found in users table, use email prefix
            $displayName = ucfirst(explode('@', $email)[0]);
        }
        
        $admins[] = [
            'email' => $email,
            'username' => $displayName
        ];
    }
    
    // Ensure we return a valid array even if empty
    if (empty($admins)) {
        $admins = [];
    }
    
    // Log for debugging
    error_log("get_admin_list returning " . count($admins) . " admins");
    error_log("Admin data: " . json_encode($admins));
    
    json_response(['success' => true, 'admins' => array_values($admins)]);
}

function update_profile($db) {
    if (!isset($_SESSION['user_id'])) {
        json_response(['success' => false, 'message' => 'Not logged in'], 401);
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if it's FormData (file upload) or JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isFormData = strpos($contentType, 'multipart/form-data') !== false;
    
    if ($isFormData) {
        // Handle FormData (with file upload)
        $name = clean_input($_POST['name'] ?? '');
        $department = clean_input($_POST['department'] ?? '');
        
        if (empty($name) || empty($department)) {
            json_response(['success' => false, 'message' => 'Name and department are required'], 400);
        }
        
        // Handle profile picture upload
        $profilePicturePath = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            $fileTmpName = $file['tmp_name'];
            
            // Validate file size (max 2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'File size must be less than 2MB'], 400);
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                json_response(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed'], 400);
            }
            
            // Create uploads/profiles directory if it doesn't exist
            $uploadDir = __DIR__ . '/uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Get old profile picture to delete later
            $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $oldUser = $stmt->fetch();
            $oldProfilePicture = $oldUser ? $oldUser['profile_picture'] : null;
            
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFileName = 'profile_' . $user_id . '_' . uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $uniqueFileName;
            
            // Move uploaded file
            if (move_uploaded_file($fileTmpName, $filePath)) {
                $profilePicturePath = 'uploads/profiles/' . $uniqueFileName;
                
                // Delete old profile picture if exists
                if ($oldProfilePicture && file_exists(__DIR__ . '/' . $oldProfilePicture)) {
                    @unlink(__DIR__ . '/' . $oldProfilePicture);
                }
            } else {
                json_response(['success' => false, 'message' => 'Failed to upload file'], 500);
            }
        }
        
        // Update user profile
        if ($profilePicturePath) {
            $stmt = $db->prepare("UPDATE users SET name = ?, department = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$name, $department, $profilePicturePath, $user_id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, department = ? WHERE id = ?");
            $stmt->execute([$name, $department, $user_id]);
        }
        
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_department'] = $department;
        
        // Get updated user data
        $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch();
        
        json_response([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $name,
                'department' => $department,
                'profile_picture' => $userData ? $userData['profile_picture'] : $profilePicturePath
            ]
        ]);
    } else {
        // Handle JSON data (no file upload)
        $data = json_decode(file_get_contents('php://input'), true);
        $name = clean_input($data['name'] ?? '');
        $department = clean_input($data['department'] ?? '');
        
        if (empty($name) || empty($department)) {
            json_response(['success' => false, 'message' => 'Name and department are required'], 400);
        }
        
        // Update user profile
        $stmt = $db->prepare("UPDATE users SET name = ?, department = ? WHERE id = ?");
        $stmt->execute([$name, $department, $user_id]);
        
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_department'] = $department;
        
        json_response([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $name,
                'department' => $department
            ]
        ]);
    }
}

function get_report($db) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $filter_admin = isset($_GET['admin']) ? clean_input($_GET['admin']) : '';
    
    // Build query with calculated fields
    $query = "
        SELECT 
            t.*,
            -- Respon Time: Calculate from created_at to first_response_at (in minutes/hours)
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    CONCAT(
                        FLOOR(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at) / 60), 'h ',
                        MOD(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at), 60), 'm'
                    )
                ELSE '-'
            END AS response_time,
            -- Reassign: Since we don't have history table, we'll set to 0 as default
            -- This can be enhanced later with a ticket_history table to track actual reassignments
            '0' AS reassign_count,
            -- Admin respon: Get admin name from assign_to at first_response_at (or current assign_to if first_response_at exists)
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    COALESCE(
                        (SELECT u.name FROM users u WHERE u.email = t.assign_to LIMIT 1),
                        SUBSTRING_INDEX(t.assign_to, '@', 1)
                    )
                ELSE '-'
            END AS admin_respon,
            -- Resolution Time: Calculate from created_at to updated_at when status = 'Done' (in hours/days)
            CASE 
                WHEN t.status = 'Done' AND t.updated_at IS NOT NULL THEN 
                    CASE 
                        WHEN TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at) < 24 THEN
                            CONCAT(TIMESTAMPDIFF(HOUR, t.created_at, t.updated_at), 'h')
                        ELSE
                            CONCAT(TIMESTAMPDIFF(DAY, t.created_at, t.updated_at), 'd')
                    END
                ELSE '-'
            END AS resolution_time
        FROM tickets t
    ";
    
    if ($filter_admin && $filter_admin !== 'all') {
        $query .= " WHERE t.assign_to = ?";
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$filter_admin]);
    } else {
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $db->query($query);
    }
    
    $tickets = $stmt->fetchAll();
    
    // Post-process to calculate reassign count more accurately
    // Since we can't track reassigns without history, we'll set it to 0 for now
    // In a real system, you'd need a ticket_history table
    foreach ($tickets as &$ticket) {
        // Reassign count: simplified - if ticket was updated and assign_to might have changed
        // For now, we'll check if there's a pattern, but without history we can't be accurate
        // Set to 0 as default, can be enhanced later with history table
        $ticket['reassign_count'] = '0';
        
        // Format response time better
        if ($ticket['response_time'] && $ticket['response_time'] !== '-') {
            // Already formatted in query
        }
        
        // Format resolution time better
        if ($ticket['resolution_time'] && $ticket['resolution_time'] !== '-') {
            // Already formatted in query
        }
    }
    unset($ticket);
    
    json_response([
        'success' => true,
        'tickets' => $tickets,
        'filter' => $filter_admin ?: 'all',
        'count' => count($tickets)
    ]);
}
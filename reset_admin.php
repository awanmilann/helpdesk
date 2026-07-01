<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // Hash the password properly with demo123
    $hashedPassword = password_hash('demo123', PASSWORD_BCRYPT);
    
    // Delete existing users
    $stmt = $db->prepare("DELETE FROM users WHERE role = 'user' OR (role = 'admin' AND email NOT IN (?))");
    $stmt->execute(['admin@helpdesk.local']);
    
    // Check if admin already exists
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute(['admin@helpdesk.local']);
    $adminCount = $stmt->fetch()['count'];
    
    if ($adminCount == 0) {
        // Insert fresh admin user
        $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['awan', 'Awan', 'admin@helpdesk.local', 'IT', $hashedPassword, 'admin']);
    }
    
    echo "<h2>✅ Master admin reset successfully!</h2>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> awan</li>";
    echo "<li><strong>Email:</strong> admin@helpdesk.local</li>";
    echo "<li><strong>Password:</strong> demo123</li>";
    echo "</ul>";
    echo "</div>";
    echo "<p><strong>Note:</strong> You can login using either username or email address.</p>";
    echo "<p><a href='index.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error resetting admin users:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
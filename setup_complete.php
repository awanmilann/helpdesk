<?php
/**
 * Complete Setup Script
 * Run this to setup everything at once
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h1>🚀 Complete Setup Script</h1>";
    echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;'>";
    
    // Step 1: Check and add username column
    echo "<h2>Step 1: Database Schema Update</h2>";
    
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'username'");
    $usernameExists = $stmt->rowCount() > 0;
    
    if (!$usernameExists) {
        echo "<p>📋 Adding username column to users table...</p>";
        
        try {
            $db->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) NOT NULL UNIQUE AFTER id");
            $db->exec("ALTER TABLE users ADD INDEX idx_username (username)");
            echo "<p>✅ Username column added successfully!</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Username column might already exist or error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ Username column already exists.</p>";
    }
    
    // Step 2: Create attachments table
    $stmt = $db->query("SHOW TABLES LIKE 'attachments'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p>📋 Creating attachments table...</p>";
        
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            attachment_type ENUM('file', 'url') NOT NULL,
            file_name VARCHAR(255) NULL,
            file_path VARCHAR(500) NULL,
            file_type VARCHAR(100) NULL,
            file_size INT NULL,
            url TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            INDEX idx_ticket_id (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($createTableSQL);
        echo "<p>✅ Attachments table created successfully!</p>";
    } else {
        echo "<p>✅ Attachments table already exists.</p>";
    }
    
    // Step 3: Setup admin users
    echo "<h2>Step 2: Admin Users Setup</h2>";
    
    // Hash the password properly with demo123
    $hashedPassword = password_hash('demo123', PASSWORD_BCRYPT);
    
    // Delete existing admin users first (reset)
    $stmt = $db->prepare("DELETE FROM users WHERE email IN (?, ?, ?)");
    $stmt->execute(['admin@helpdesk.local', 'elky@bamboovillagetrust.earth', 'fathih@bamboovillagetrust.earth']);
    
    echo "<p>📋 Creating master admin user...</p>";
    
    // Insert admin user
    $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['awan', 'Awan', 'admin@helpdesk.local', 'IT', $hashedPassword, 'admin']);
    
    echo "<p>✅ Master admin created successfully!</p>";
    
    // Step 4: Check uploads folder
    echo "<h2>Step 3: File System Setup</h2>";
    
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
        echo "<p>✅ Uploads folder created.</p>";
    } else {
        echo "<p>✅ Uploads folder exists.</p>";
    }
    
    // Create .htaccess for uploads security
    $htaccessContent = "Options -Indexes\n<Files \"*.php\">\n    Order Deny,Allow\n    Deny from all\n</Files>\n\n# Allow common file types\n<FilesMatch \"\\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt|zip|rar)$\">\n    Order Allow,Deny\n    Allow from all\n</FilesMatch>";
    
    if (!file_exists('uploads/.htaccess')) {
        file_put_contents('uploads/.htaccess', $htaccessContent);
        echo "<p>✅ Security .htaccess created for uploads folder.</p>";
    } else {
        echo "<p>✅ Security .htaccess already exists.</p>";
    }
    
    // Check uploads folder permissions
    if (is_writable('uploads')) {
        echo "<p>✅ Uploads folder is writable.</p>";
    } else {
        echo "<p>⚠️ Warning: Uploads folder is not writable. Please set permissions to 755.</p>";
    }
    
    // Final summary
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✨ New Features Added:</h3>";
    echo "<ul>";
    echo "<li>✅ Login with username or email</li>";
    echo "<li>✅ Signup form includes username field</li>";
    echo "<li>✅ Admin can login with username: <strong>awan</strong></li>";
    echo "<li>✅ Password changed to: <strong>demo123</strong></li>";
    echo "<li>✅ Users can edit and delete their own tickets (when status is 'Open')</li>";
    echo "<li>✅ Admin can reset any user's password</li>";
    echo "<li>✅ All users can change their own password</li>";
    echo "<li>✅ File attachments and URL links support</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🔑 Admin Login Credentials:</h3>";
    echo "<ul>";
    echo "<li>Username: <strong>awan</strong></li>";
    echo "<li>Email: <strong>admin@helpdesk.local</strong></li>";
    echo "<li>Password: <strong>demo123</strong></li>";
    echo "</ul>";
    echo "<p><em>Note: You can login using either username or email address.</em></p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='index.php' style='background: #4f46e5; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold;'>🚀 Launch Application</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Setup Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
<?php
/**
 * Database Update Script
 * Run this once to add missing attachments table
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h2>🔧 Database Update Script</h2>";
    
    // Check if username column exists in users table
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'username'");
    $usernameExists = $stmt && $stmt->rowCount() > 0;
    
    if (!$usernameExists) {
        echo "<p>📋 Adding username column to users table...</p>";
        
        // Add username column
        $db->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) NOT NULL UNIQUE AFTER id");
        $db->exec("ALTER TABLE users ADD INDEX idx_username (username)");
        
        // Update existing users with username based on email
        $stmt = $db->query("SELECT id, email FROM users");
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            $username = explode('@', $user['email'])[0]; // Use part before @ as username
            $updateStmt = $db->prepare("UPDATE users SET username = ? WHERE id = ?");
            $updateStmt->execute([$username, $user['id']]);
        }
        
        echo "<p>✅ Username column added and populated successfully!</p>";
    } else {
        echo "<p>✅ Username column already exists.</p>";
    }
    
    // Check if attachments table exists
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
    
    // Check uploads folder
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
        echo "<p>✅ Uploads folder created.</p>";
    } else {
        echo "<p>✅ Uploads folder exists.</p>";
    }
    
    // Add new ticket columns if missing (due_date, first_response_at, response_minutes)
    echo "<h3>📦 Checking additional ticket columns...</h3>";
    $columnsToAdd = [
        ['name' => 'due_date', 'sql' => "ALTER TABLE tickets ADD COLUMN due_date DATE NULL AFTER end_date"],
        ['name' => 'first_response_at', 'sql' => "ALTER TABLE tickets ADD COLUMN first_response_at DATETIME NULL AFTER due_date"],
        ['name' => 'response_minutes', 'sql' => "ALTER TABLE tickets ADD COLUMN response_minutes INT NULL AFTER first_response_at"],
        // Ensure priority columns exist for ticket creation on some hosts with older schema
        ['name' => 'priority', 'sql' => "ALTER TABLE tickets ADD COLUMN priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium' AFTER description"],
        ['name' => 'sla_priority', 'sql' => "ALTER TABLE tickets ADD COLUMN sla_priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium' AFTER priority"],
        ['name' => 'admin_comment', 'sql' => "ALTER TABLE tickets ADD COLUMN admin_comment TEXT NULL AFTER status"],
    ];
    foreach ($columnsToAdd as $col) {
        // SHOW COLUMNS doesn't support prepared statements, so we need to escape the column name manually
        $columnName = $db->quote($col['name']);
        $stmt = $db->query("SHOW COLUMNS FROM tickets LIKE $columnName");
        if ($stmt->rowCount() === 0) {
            echo "<p>📋 Adding column: {$col['name']}...</p>";
            $db->exec($col['sql']);
            echo "<p>✅ Column {$col['name']} added.</p>";
        } else {
            echo "<p>✅ Column {$col['name']} already exists.</p>";
        }
    }

    // Check uploads folder permissions
    if (is_writable('uploads')) {
        echo "<p>✅ Uploads folder is writable.</p>";
    } else {
        echo "<p>⚠️ Warning: Uploads folder is not writable. Please set permissions to 755.</p>";
    }
    
    echo "<h3>🎉 Database update completed!</h3>";
    echo "<p><a href='reset_admin.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Reset Admin Password</a>";
    echo "<a href='index.php' style='background: #22c55e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Application</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Database Update Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
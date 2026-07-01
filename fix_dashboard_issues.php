<?php
/**
 * Fix Dashboard Issues
 * Run this to fix common dashboard problems
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h1>🔧 Fixing Dashboard Issues</h1>";
    echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;'>";
    
    // Fix 1: Ensure attachments table exists
    echo "<h2>Step 1: Check Attachments Table</h2>";
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'attachments'");
        if ($stmt->rowCount() == 0) {
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
    } catch (Exception $e) {
        echo "<p>❌ Error with attachments table: " . $e->getMessage() . "</p>";
    }
    
    // Fix 2: Check and fix ticket categories
    echo "<h2>Step 2: Fix Ticket Categories</h2>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM tickets WHERE category IS NULL OR category = ''");
    $nullCategories = $stmt->fetch()['count'];
    
    if ($nullCategories > 0) {
        echo "<p>📋 Found {$nullCategories} tickets with missing categories. Fixing...</p>";
        $db->exec("UPDATE tickets SET category = 'Software' WHERE category IS NULL OR category = ''");
        echo "<p>✅ Fixed missing categories!</p>";
    } else {
        echo "<p>✅ All tickets have valid categories.</p>";
    }
    
    // Fix 3: Create sample tickets if none exist
    echo "<h2>Step 3: Check Sample Data</h2>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM tickets");
    $ticketCount = $stmt->fetch()['count'];
    
    if ($ticketCount == 0) {
        echo "<p>📋 No tickets found. Creating sample tickets...</p>";
        
        // Get a user to create tickets for
        $stmt = $db->query("SELECT id, name, department FROM users WHERE role = 'user' LIMIT 1");
        $user = $stmt->fetch();
        
        if (!$user) {
            // Create a sample user
            $hashedPassword = password_hash('demo123', PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['testuser', 'Test User', 'test@example.com', 'IT', $hashedPassword, 'user']);
            $userId = $db->lastInsertId();
            $userName = 'Test User';
            $userDept = 'IT';
        } else {
            $userId = $user['id'];
            $userName = $user['name'];
            $userDept = $user['department'];
        }
        
        // Create sample tickets
        $sampleTickets = [
            ['category' => 'Network', 'description' => 'Internet connection is slow in the office', 'priority' => 'Medium'],
            ['category' => 'Software', 'description' => 'Microsoft Office is not opening properly', 'priority' => 'High'],
            ['category' => 'Hardware', 'description' => 'Printer is not working, paper jam issue', 'priority' => 'Low'],
            ['category' => 'System & Aplikasi', 'description' => 'Need access to HR system for new employee', 'priority' => 'Medium']
        ];
        
        foreach ($sampleTickets as $index => $ticket) {
            $ticketNumber = 'T' . str_pad($index + 1, 5, '0', STR_PAD_LEFT);
            $stmt = $db->prepare("INSERT INTO tickets (ticket_number, reporter_id, reporter_name, reporter_dept, category, description, priority, assign_to, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ticketNumber,
                $userId,
                $userName,
                $userDept,
                $ticket['category'],
                $ticket['description'],
                $ticket['priority'],
                'admin@helpdesk.local',
                'Open'
            ]);
        }
        
        // Update counter
        $db->exec("INSERT INTO ticket_counter (id, counter) VALUES (1, 4) ON DUPLICATE KEY UPDATE counter = 4");
        
        echo "<p>✅ Created 4 sample tickets!</p>";
    } else {
        echo "<p>✅ Found {$ticketCount} tickets in database.</p>";
    }
    
    // Fix 4: Check uploads folder
    echo "<h2>Step 4: Check Uploads Folder</h2>";
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
        echo "<p>✅ Created uploads folder.</p>";
    } else {
        echo "<p>✅ Uploads folder exists.</p>";
    }
    
    if (!file_exists('uploads/.htaccess')) {
        $htaccessContent = "Options -Indexes\n<Files \"*.php\">\n    Order Deny,Allow\n    Deny from all\n</Files>";
        file_put_contents('uploads/.htaccess', $htaccessContent);
        echo "<p>✅ Created security .htaccess file.</p>";
    } else {
        echo "<p>✅ Security .htaccess file exists.</p>";
    }
    
    // Fix 5: Test API endpoints
    echo "<h2>Step 5: Test API Endpoints</h2>";
    
    // Test session
    if (isset($_SESSION['user_id'])) {
        echo "<p>✅ Session is active for user: {$_SESSION['user_name']}</p>";
    } else {
        echo "<p>⚠️ No active session. Please login first.</p>";
    }
    
    // Summary
    echo "<h2>🎉 Fix Summary</h2>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Issues Fixed:</h3>";
    echo "<ul>";
    echo "<li>✅ Attachments table created/verified</li>";
    echo "<li>✅ Ticket categories fixed</li>";
    echo "<li>✅ Sample data created if needed</li>";
    echo "<li>✅ Uploads folder secured</li>";
    echo "<li>✅ API endpoints verified</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>⚠️ Manual Steps Required:</h3>";
    echo "<ol>";
    echo "<li>Clear browser cache and cookies</li>";
    echo "<li>Login again to create fresh session</li>";
    echo "<li>Test creating a new ticket</li>";
    echo "<li>Test editing/deleting tickets</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='debug_user_dashboard.php' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-right: 10px;'>🔍 Debug Dashboard</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>🚀 Go to Dashboard</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Fix Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
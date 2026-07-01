<?php
/**
 * Database Migration Script
 * Add profile_picture column to users table
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h2>🔧 Database Migration: Add Profile Picture Column</h2>";
    
    // Check if profile_picture column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $columnExists = $stmt && $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "<p>📋 Adding profile_picture column to users table...</p>";
        
        // Add profile_picture column
        $db->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(500) NULL AFTER department");
        
        echo "<p>✅ Profile picture column added successfully!</p>";
    } else {
        echo "<p>✅ Profile picture column already exists.</p>";
    }
    
    // Create uploads/profiles directory if it doesn't exist
    $profilesDir = __DIR__ . '/uploads/profiles';
    if (!file_exists($profilesDir)) {
        mkdir($profilesDir, 0755, true);
        echo "<p>✅ Created uploads/profiles directory</p>";
    } else {
        echo "<p>✅ uploads/profiles directory already exists</p>";
    }
    
    // Create .htaccess for security
    $htaccessFile = $profilesDir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Options -Indexes\n<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n    Order allow,deny\n    Deny from all\n</FilesMatch>");
        echo "<p>✅ Created .htaccess for security</p>";
    }
    
    echo "<p><strong>✅ Migration completed successfully!</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>


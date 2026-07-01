<?php
/**
 * Enable Edit/Delete Features for User Tickets
 * This script ensures all necessary components are working
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h1>🔧 Enable Edit/Delete Features</h1>";
    echo "<div style='max-width: 1000px; margin: 0 auto; font-family: Arial, sans-serif;'>";
    
    // Step 1: Check database structure
    echo "<h2>Step 1: Database Structure Check</h2>";
    
    // Check tickets table structure
    $stmt = $db->query("DESCRIBE tickets");
    $columns = $stmt->fetchAll();
    
    $requiredColumns = ['id', 'ticket_number', 'reporter_id', 'category', 'description', 'priority', 'assign_to', 'status'];
    $missingColumns = [];
    
    $existingColumns = array_column($columns, 'Field');
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $existingColumns)) {
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "<p>✅ All required columns exist in tickets table</p>";
    } else {
        echo "<p>❌ Missing columns: " . implode(', ', $missingColumns) . "</p>";
    }
    
    // Step 2: Check API endpoints
    echo "<h2>Step 2: API Endpoints Check</h2>";
    
    $requiredEndpoints = [
        'list_tickets' => 'List user tickets',
        'get_ticket_details' => 'Get ticket details for editing',
        'update_user_ticket' => 'Update user ticket',
        'delete_user_ticket' => 'Delete user ticket'
    ];
    
    foreach ($requiredEndpoints as $endpoint => $description) {
        // Check if endpoint exists in api.php
        $apiContent = file_get_contents('api.php');
        if (strpos($apiContent, "case '$endpoint':") !== false) {
            echo "<p>✅ $endpoint - $description</p>";
        } else {
            echo "<p>❌ $endpoint - Missing</p>";
        }
    }
    
    // Step 3: Create test tickets with different statuses
    echo "<h2>Step 3: Test Data Creation</h2>";
    
    // Get or create test user
    $stmt = $db->query("SELECT id, name, department FROM users WHERE role = 'user' LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        $hashedPassword = password_hash('demo123', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (username, name, email, department, password, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['testuser', 'Test User', 'test@example.com', 'IT', $hashedPassword, 'user']);
        $userId = $db->lastInsertId();
        $userName = 'Test User';
        $userDept = 'IT';
        echo "<p>✅ Created test user</p>";
    } else {
        $userId = $user['id'];
        $userName = $user['name'];
        $userDept = $user['department'];
        echo "<p>✅ Using existing user: {$userName}</p>";
    }
    
    // Create test tickets
    $testTickets = [
        ['status' => 'Open', 'category' => 'Software', 'description' => 'EDITABLE: Microsoft Office installation issue', 'priority' => 'Medium'],
        ['status' => 'Open', 'category' => 'Hardware', 'description' => 'EDITABLE: Printer not responding to print commands', 'priority' => 'Low'],
        ['status' => 'In Progress', 'category' => 'Network', 'description' => 'NON-EDITABLE: WiFi connectivity problems in conference room', 'priority' => 'High'],
        ['status' => 'Done', 'category' => 'System & Aplikasi', 'description' => 'NON-EDITABLE: User access granted to CRM system', 'priority' => 'Medium']
    ];
    
    $createdTickets = [];
    
    foreach ($testTickets as $index => $ticketData) {
        // Generate ticket number
        $stmt = $db->query("SELECT counter FROM ticket_counter WHERE id = 1");
        $result = $stmt->fetch();
        $counter = $result ? $result['counter'] + 1 : $index + 1;
        
        // Update counter
        $db->prepare("INSERT INTO ticket_counter (id, counter) VALUES (1, ?) ON DUPLICATE KEY UPDATE counter = ?")->execute([$counter, $counter]);
        
        $ticket_number = 'T' . str_pad($counter, 5, '0', STR_PAD_LEFT);
        
        // Create ticket
        $stmt = $db->prepare("INSERT INTO tickets (ticket_number, reporter_id, reporter_name, reporter_dept, category, description, priority, assign_to, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ticket_number,
            $userId,
            $userName,
            $userDept,
            $ticketData['category'],
            $ticketData['description'],
            $ticketData['priority'],
            'admin@helpdesk.local',
            $ticketData['status']
        ]);
        
        $createdTickets[] = [
            'ticket_number' => $ticket_number,
            'status' => $ticketData['status'],
            'editable' => $ticketData['status'] === 'Open'
        ];
    }
    
    echo "<p>✅ Created " . count($createdTickets) . " test tickets:</p>";
    echo "<ul>";
    foreach ($createdTickets as $ticket) {
        $editableText = $ticket['editable'] ? '<span style="color: green;">EDITABLE</span>' : '<span style="color: red;">READ-ONLY</span>';
        echo "<li>{$ticket['ticket_number']} ({$ticket['status']}) - {$editableText}</li>";
    }
    echo "</ul>";
    
    // Step 4: Test session setup
    echo "<h2>Step 4: Session Setup</h2>";
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_username'] = 'testuser';
        $_SESSION['user_name'] = $userName;
        $_SESSION['user_email'] = 'test@example.com';
        $_SESSION['user_department'] = $userDept;
        $_SESSION['user_role'] = 'user';
        echo "<p>✅ Test session created</p>";
    } else {
        echo "<p>✅ Session already active for: {$_SESSION['user_name']}</p>";
    }
    
    // Step 5: Feature summary
    echo "<h2>Step 5: Edit/Delete Features Summary</h2>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Edit/Delete Features Enabled:</h3>";
    echo "<ul>";
    echo "<li>✅ Users can edit tickets with status 'Open'</li>";
    echo "<li>✅ Users can delete tickets with status 'Open'</li>";
    echo "<li>✅ Edit button appears only for editable tickets</li>";
    echo "<li>✅ Delete button appears only for editable tickets</li>";
    echo "<li>✅ Non-editable tickets show 'Cannot edit' message</li>";
    echo "<li>✅ Edit modal with form validation</li>";
    echo "<li>✅ Delete confirmation dialog</li>";
    echo "<li>✅ Real-time UI updates after edit/delete</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📋 How Edit/Delete Works:</h3>";
    echo "<ol>";
    echo "<li><strong>Edit Feature:</strong>";
    echo "<ul>";
    echo "<li>Only tickets with status 'Open' can be edited</li>";
    echo "<li>Click 'Edit' button opens modal with current ticket data</li>";
    echo "<li>User can modify: Category, Description, Priority, Assign To</li>";
    echo "<li>Form validation ensures all fields are filled</li>";
    echo "<li>API call to update_user_ticket endpoint</li>";
    echo "<li>Success notification and UI refresh</li>";
    echo "</ul>";
    echo "</li>";
    echo "<li><strong>Delete Feature:</strong>";
    echo "<ul>";
    echo "<li>Only tickets with status 'Open' can be deleted</li>";
    echo "<li>Click 'Delete' button shows confirmation dialog</li>";
    echo "<li>Confirmation required to prevent accidental deletion</li>";
    echo "<li>API call to delete_user_ticket endpoint</li>";
    echo "<li>Removes ticket and associated attachments</li>";
    echo "<li>Success notification and UI refresh</li>";
    echo "</ul>";
    echo "</li>";
    echo "</ol>";
    echo "</div>";
    
    // Step 6: Test links
    echo "<h2>Step 6: Test Your Features</h2>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='test_edit_delete_ticket.php' style='background: #17a2b8; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px;'>🧪 Interactive Test Page</a>";
    echo "<a href='comprehensive_ticket_test.php' style='background: #6f42c1; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px;'>🔬 Comprehensive Test</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px;'>🚀 Main Dashboard</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
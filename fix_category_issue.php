<?php
/**
 * Fix Category N/A Issue
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h1>🔧 Fix Category N/A Issue</h1>";
    echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;'>";
    
    // Check current tickets
    echo "<h2>Current Tickets in Database:</h2>";
    $stmt = $db->query("SELECT id, ticket_number, category, description, status FROM tickets ORDER BY id DESC LIMIT 10");
    $tickets = $stmt->fetchAll();
    
    if (count($tickets) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Ticket Number</th><th>Category</th><th>Description</th><th>Status</th></tr>";
        foreach ($tickets as $ticket) {
            $categoryDisplay = empty($ticket['category']) ? '<span style="color: red;">NULL/EMPTY</span>' : $ticket['category'];
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>{$categoryDisplay}</td>";
            echo "<td>" . substr($ticket['description'], 0, 50) . "...</td>";
            echo "<td>{$ticket['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tickets found.</p>";
    }
    
    // Fix NULL/empty categories
    echo "<h2>Fixing NULL/Empty Categories:</h2>";
    $stmt = $db->query("SELECT COUNT(*) as count FROM tickets WHERE category IS NULL OR category = '' OR category = 'N/A'");
    $nullCount = $stmt->fetch()['count'];
    
    if ($nullCount > 0) {
        echo "<p>Found {$nullCount} tickets with NULL/empty categories. Fixing...</p>";
        
        // Update NULL/empty categories to 'Software' as default
        $stmt = $db->prepare("UPDATE tickets SET category = 'Software' WHERE category IS NULL OR category = '' OR category = 'N/A'");
        $stmt->execute();
        
        echo "<p>✅ Fixed {$nullCount} tickets with missing categories!</p>";
    } else {
        echo "<p>✅ All tickets have valid categories.</p>";
    }
    
    // Check table structure
    echo "<h2>Table Structure Check:</h2>";
    $stmt = $db->query("DESCRIBE tickets");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test create ticket
    echo "<h2>Test Create Ticket:</h2>";
    echo "<form method='POST' action='api.php?action=create_ticket' style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Category:</label><br>";
    echo "<select name='category' required style='width: 100%; padding: 8px;'>";
    echo "<option value=''>-- Select Category --</option>";
    echo "<option value='Network'>Network</option>";
    echo "<option value='Software'>Software</option>";
    echo "<option value='Hardware'>Hardware</option>";
    echo "<option value='System & Aplikasi'>System & Aplikasi</option>";
    echo "</select>";
    echo "</div>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Description:</label><br>";
    echo "<textarea name='description' required style='width: 100%; padding: 8px; height: 80px;' placeholder='Describe your issue...'></textarea>";
    echo "</div>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Priority:</label><br>";
    echo "<select name='priority' style='width: 100%; padding: 8px;'>";
    echo "<option value='Low'>Low</option>";
    echo "<option value='Medium' selected>Medium</option>";
    echo "<option value='High'>High</option>";
    echo "<option value='Critical'>Critical</option>";
    echo "</select>";
    echo "</div>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label>Assign to:</label><br>";
    echo "<select name='assign_to' required style='width: 100%; padding: 8px;'>";
    echo "<option value=''>-- Select Assignee --</option>";
    echo "<option value='admin@helpdesk.local'>Awan</option>";
    echo "</select>";
    echo "</div>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>Test Create Ticket</button>";
    echo "</form>";
    
    // Show updated tickets
    echo "<h2>Updated Tickets:</h2>";
    $stmt = $db->query("SELECT id, ticket_number, category, description, status FROM tickets ORDER BY id DESC LIMIT 5");
    $updatedTickets = $stmt->fetchAll();
    
    if (count($updatedTickets) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Ticket Number</th><th>Category</th><th>Description</th><th>Status</th></tr>";
        foreach ($updatedTickets as $ticket) {
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td style='color: green; font-weight: bold;'>{$ticket['category']}</td>";
            echo "<td>" . substr($ticket['description'], 0, 50) . "...</td>";
            echo "<td>{$ticket['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to Dashboard</a>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
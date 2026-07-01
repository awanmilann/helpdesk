<?php
/**
 * Fix Category Data - Perbaiki ticket yang category-nya NULL atau kosong
 * dan pastikan format category benar
 */

require_once 'config.php';

try {
    $db = getDB();
    
    echo "<h1>🔧 Fix Category Data</h1>";
    echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif; padding: 20px;'>";
    
    // Check tickets dengan category NULL atau kosong
    echo "<h2>1. Checking Tickets with NULL/Empty Category:</h2>";
    $stmt = $db->query("SELECT id, ticket_number, category, description FROM tickets WHERE category IS NULL OR category = '' OR category = '-' ORDER BY id DESC");
    $nullTickets = $stmt->fetchAll();
    
    if (count($nullTickets) > 0) {
        echo "<p>Found <strong>" . count($nullTickets) . "</strong> tickets with NULL/empty category:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Ticket Number</th><th>Current Category</th><th>Description</th><th>Action</th></tr>";
        
        foreach ($nullTickets as $ticket) {
            // Coba tebak category dari description (heuristic)
            $description = strtolower($ticket['description'] ?? '');
            $category = 'Software'; // default
            
            if (stripos($description, 'network') !== false || stripos($description, 'internet') !== false || stripos($description, 'wifi') !== false || stripos($description, 'connect') !== false) {
                $category = 'Network';
            } elseif (stripos($description, 'hardware') !== false || stripos($description, 'laptop') !== false || stripos($description, 'printer') !== false || stripos($description, 'mouse') !== false || stripos($description, 'keyboard') !== false) {
                $category = 'Hardware';
            } elseif (stripos($description, 'system') !== false || stripos($description, 'aplikasi') !== false || stripos($description, 'application') !== false || stripos($description, 'access') !== false || stripos($description, 'hr') !== false || stripos($description, 'sales') !== false) {
                $category = 'System & Aplikasi';
            }
            
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>" . ($ticket['category'] ?: 'NULL/EMPTY') . "</td>";
            echo "<td>" . substr($ticket['description'] ?? '', 0, 50) . "...</td>";
            echo "<td>Will set to: <strong>{$category}</strong></td>";
            echo "</tr>";
            
            // Update category
            $updateStmt = $db->prepare("UPDATE tickets SET category = ? WHERE id = ?");
            $updateStmt->execute([$category, $ticket['id']]);
        }
        
        echo "</table>";
        echo "<p style='color: green;'>✅ Updated " . count($nullTickets) . " tickets!</p>";
    } else {
        echo "<p style='color: green;'>✅ No tickets with NULL/empty category found.</p>";
    }
    
    // Check tickets dengan category yang salah format (dengan &amp;)
    echo "<h2>2. Checking Tickets with Wrong Category Format:</h2>";
    $stmt = $db->query("SELECT id, ticket_number, category FROM tickets WHERE category LIKE '%&amp;%' OR category LIKE '%&amp%' ORDER BY id DESC");
    $wrongFormatTickets = $stmt->fetchAll();
    
    if (count($wrongFormatTickets) > 0) {
        echo "<p>Found <strong>" . count($wrongFormatTickets) . "</strong> tickets with wrong category format:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Ticket Number</th><th>Current Category</th><th>Fixed Category</th></tr>";
        
        foreach ($wrongFormatTickets as $ticket) {
            $fixedCategory = str_replace('&amp;', '&', $ticket['category']);
            $fixedCategory = str_replace('&amp', '&', $fixedCategory);
            
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>{$ticket['category']}</td>";
            echo "<td><strong>{$fixedCategory}</strong></td>";
            echo "</tr>";
            
            // Update category
            $updateStmt = $db->prepare("UPDATE tickets SET category = ? WHERE id = ?");
            $updateStmt->execute([$fixedCategory, $ticket['id']]);
        }
        
        echo "</table>";
        echo "<p style='color: green;'>✅ Fixed " . count($wrongFormatTickets) . " tickets!</p>";
    } else {
        echo "<p style='color: green;'>✅ No tickets with wrong category format found.</p>";
    }
    
    // Show current category distribution
    echo "<h2>3. Current Category Distribution:</h2>";
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM tickets GROUP BY category ORDER BY count DESC");
    $categories = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Category</th><th>Count</th></tr>";
    foreach ($categories as $cat) {
        $display = $cat['category'] ?: '(NULL/Empty)';
        echo "<tr><td>{$display}</td><td>{$cat['count']}</td></tr>";
    }
    echo "</table>";
    
    // Test create ticket dengan System & Aplikasi
    echo "<h2>4. Test Create Ticket (System & Aplikasi):</h2>";
    echo "<p>Try creating a new ticket with category 'System & Aplikasi' from the dashboard.</p>";
    echo "<p style='color: blue;'>If the issue persists, check:</p>";
    echo "<ul>";
    echo "<li>Browser console for JavaScript errors</li>";
    echo "<li>Network tab to see the actual data being sent</li>";
    echo "<li>Check if the category dropdown value is exactly 'System & Aplikasi'</li>";
    echo "</ul>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>


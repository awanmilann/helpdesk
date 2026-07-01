<?php
/**
 * Export functionality for tickets
 * Supports PDF and Excel export
 */

require_once 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? clean_input($_GET['action']) : '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'export_pdf':
            exportToPDF($db);
            break;
        case 'export_excel':
            exportToExcel($db);
            break;
        default:
            json_response(['success' => false, 'message' => 'Invalid export action'], 400);
    }
    
} catch (Exception $e) {
    json_response(['success' => false, 'message' => 'Export error: ' . $e->getMessage()], 500);
}

function exportToPDF($db) {
    // Check if user is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Get filter parameter
    $filter_admin = isset($_GET['admin']) ? clean_input($_GET['admin']) : '';
    
    // Use same query as get_report with calculated fields
    $query = "
        SELECT 
            t.*,
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    CONCAT(
                        FLOOR(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at) / 60), 'h ',
                        MOD(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at), 60), 'm'
                    )
                ELSE '-'
            END AS response_time,
            '0' AS reassign_count,
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    COALESCE(
                        (SELECT u.name FROM users u WHERE u.email = t.assign_to LIMIT 1),
                        SUBSTRING_INDEX(t.assign_to, '@', 1)
                    )
                ELSE '-'
            END AS admin_respon,
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
        $tickets = $stmt->fetchAll();
        $filter_label = $filter_admin;
    } else {
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $db->query($query);
        $tickets = $stmt->fetchAll();
        $filter_label = 'All Tickets';
    }
    
    // Generate PDF content
    $html = generatePDFContent($tickets, $filter_label);
    
    // For now, return HTML content that can be printed to PDF
    // In production, you would use a library like TCPDF or mPDF
    json_response([
        'success' => true,
        'html' => $html,
        'filename' => 'tickets_' . ($filter_admin && $filter_admin !== 'all' ? str_replace('@', '_at_', $filter_admin) . '_' : '') . date('Y-m-d_H-i-s') . '.html'
    ]);
}

function exportToExcel($db) {
    // Check if user is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    // Get filter parameter
    $filter_admin = isset($_GET['admin']) ? clean_input($_GET['admin']) : '';
    
    // Use same query as get_report with calculated fields
    $query = "
        SELECT 
            t.*,
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    CONCAT(
                        FLOOR(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at) / 60), 'h ',
                        MOD(TIMESTAMPDIFF(MINUTE, t.created_at, t.first_response_at), 60), 'm'
                    )
                ELSE '-'
            END AS response_time,
            '0' AS reassign_count,
            CASE 
                WHEN t.first_response_at IS NOT NULL THEN 
                    COALESCE(
                        (SELECT u.name FROM users u WHERE u.email = t.assign_to LIMIT 1),
                        SUBSTRING_INDEX(t.assign_to, '@', 1)
                    )
                ELSE '-'
            END AS admin_respon,
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
        $tickets = $stmt->fetchAll();
    } else {
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $db->query($query);
        $tickets = $stmt->fetchAll();
    }
    
    // Generate CSV content (Excel compatible)
    $csv = generateCSVContent($tickets);
    
    json_response([
        'success' => true,
        'csv' => $csv,
        'filename' => 'tickets_' . ($filter_admin && $filter_admin !== 'all' ? str_replace('@', '_at_', $filter_admin) . '_' : '') . date('Y-m-d_H-i-s') . '.csv'
    ]);
}

function generatePDFContent($tickets, $filter_label = 'All Tickets') {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>IT Helpdesk Tickets Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #16a34a; margin: 0; }
            .header p { color: #6b7280; margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; }
            th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
            th { background-color: #f3f4f6; font-weight: bold; }
            td.description { max-width: 400px; word-wrap: break-word; word-break: break-word; white-space: normal; }
            .status-open { background-color: #f3f4f6; }
            .status-progress { background-color: #dbeafe; }
            .status-done { background-color: #dcfce7; }
            .status-delayed { background-color: #fef3c7; }
            .status-revisi { background-color: #fed7aa; }
            .footer { margin-top: 30px; text-align: center; color: #6b7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>IT Helpdesk System</h1>
            <p>Bamboo Village Trust</p>
            <p>Filter: ' . htmlspecialchars($filter_label) . '</p>
            <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Ticket No.</th>
                    <th>Reporter</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Respon Time</th>
                    <th>Reassign</th>
                    <th>Admin Respon</th>
                    <th>Resolution Time</th>
                    <th>Status</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($tickets as $ticket) {
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $ticket['status']));
        $html .= '
            <tr>
                <td>' . htmlspecialchars($ticket['ticket_number']) . '</td>
                <td>' . htmlspecialchars($ticket['reporter_name']) . '</td>
                <td>' . htmlspecialchars($ticket['reporter_dept']) . '</td>
                <td>' . htmlspecialchars($ticket['category']) . '</td>
                <td class="description">' . nl2br(htmlspecialchars($ticket['description'])) . '</td>
                <td>' . htmlspecialchars($ticket['priority']) . '</td>
                <td>' . htmlspecialchars($ticket['assign_to'] ? (strpos($ticket['assign_to'], '@') !== false ? explode('@', $ticket['assign_to'])[0] : $ticket['assign_to']) : '-') . '</td>
                <td>' . htmlspecialchars($ticket['response_time'] ?? '-') . '</td>
                <td>' . htmlspecialchars($ticket['reassign_count'] ?? '0') . '</td>
                <td>' . htmlspecialchars($ticket['admin_respon'] ?? '-') . '</td>
                <td>' . htmlspecialchars($ticket['resolution_time'] ?? '-') . '</td>
                <td class="' . $statusClass . '">' . htmlspecialchars($ticket['status']) . '</td>
                <td>' . date('M j, Y', strtotime($ticket['created_at'])) . '</td>
            </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Total Tickets: ' . count($tickets) . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

function generateCSVContent($tickets) {
    $csv = "Ticket No.,Reporter,Department,Category,Description,Priority,Assigned To,Status,Start Date,End Date,Created Date\n";
    
    foreach ($tickets as $ticket) {
        $csv .= '"' . $ticket['ticket_number'] . '",';
        $csv .= '"' . $ticket['reporter_name'] . '",';
        $csv .= '"' . $ticket['reporter_dept'] . '",';
        $csv .= '"' . $ticket['category'] . '",';
        $csv .= '"' . str_replace('"', '""', $ticket['description']) . '",';
        $csv .= '"' . $ticket['priority'] . '",';
        $assignToDisplay = $ticket['assign_to'] ? (strpos($ticket['assign_to'], '@') !== false ? explode('@', $ticket['assign_to'])[0] : $ticket['assign_to']) : '-';
        $csv .= '"' . $assignToDisplay . '",';
        $csv .= '"' . ($ticket['response_time'] ?? '-') . '",';
        $csv .= '"' . ($ticket['reassign_count'] ?? '0') . '",';
        $csv .= '"' . ($ticket['admin_respon'] ?? '-') . '",';
        $csv .= '"' . ($ticket['resolution_time'] ?? '-') . '",';
        $csv .= '"' . $ticket['status'] . '",';
        $csv .= '"' . ($ticket['start_date'] ?: '') . '",';
        $csv .= '"' . ($ticket['end_date'] ?: '') . '",';
        $csv .= '"' . date('Y-m-d H:i:s', strtotime($ticket['created_at'])) . '"';
        $csv .= "\n";
    }
    
    return $csv;
}
?>

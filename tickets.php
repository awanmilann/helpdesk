<?php
session_start();
require_once 'config.php';

class Ticket {
    private $conn;
    private $table_name = "tickets";
    private $counter_table = "ticket_counter";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generate ticket number - FIXED to use PDO
    private function generateTicketNumber() {
        // Get and update counter
        $stmt = $this->conn->prepare("SELECT counter FROM " . $this->counter_table . " WHERE id = 1 FOR UPDATE");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            $counter = $result['counter'] + 1;
            
            $stmt = $this->conn->prepare("UPDATE " . $this->counter_table . " SET counter = ? WHERE id = 1");
            $stmt->execute([$counter]);
            
            return "T" . str_pad($counter, 5, '0', STR_PAD_LEFT);
        }
        
        return "T00001";
    }

    // Create new ticket - FIXED to use PDO
    public function create($reporter_id, $reporter_name, $reporter_dept, $category, $description, $priority, $assign_to) {
        $ticket_number = $this->generateTicketNumber();
        $reporter_name = clean_input($reporter_name);
        $reporter_dept = clean_input($reporter_dept);
        $category = clean_input($category);
        $description = clean_input($description);
        $priority = clean_input($priority);
        $assign_to = clean_input($assign_to);

        $stmt = $this->conn->prepare("INSERT INTO " . $this->table_name . " 
                 (ticket_number, reporter_id, reporter_name, reporter_dept, category, description, priority, sla_priority, assign_to, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open')");
        
        if ($stmt->execute([$ticket_number, $reporter_id, $reporter_name, $reporter_dept, $category, $description, $priority, $priority, $assign_to])) {
            return response(true, "Ticket created successfully", ['ticket_number' => $ticket_number]);
        }
        
        return response(false, "Failed to create ticket");
    }

    // Get user tickets - FIXED to use PDO
    public function getUserTickets($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE reporter_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $tickets = $stmt->fetchAll();
        
        return response(true, "", $tickets);
    }

    // Get all tickets (for admin) - FIXED to use PDO
    public function getAllTickets() {
        $stmt = $this->conn->query("SELECT t.*, u.name as reporter_name, u.department as reporter_dept 
                 FROM " . $this->table_name . " t 
                 LEFT JOIN users u ON t.reporter_id = u.id 
                 ORDER BY t.created_at DESC");
        
        $tickets = $stmt->fetchAll();
        
        return response(true, "", $tickets);
    }

    // Update ticket status
    public function updateStatus($ticket_id, $status, $user_email) {
        $ticket = $this->getTicketById($ticket_id);
        
        if (!$ticket) {
            return response(false, "Ticket not found");
        }
        
        // Check if user is assigned to this ticket
        if ($ticket['assign_to'] !== $user_email) {
            return response(false, "You can only update tickets assigned to you");
        }

        $updates = [];
        $params = [];
        $types = "";
        
        $updates[] = "status = ?";
        $params[] = $status;
        $types .= "s";
        
        if ($status === 'Done') {
            $updates[] = "end_date = CURDATE()";
        } elseif ($status === 'In Progress' && !$ticket['start_date']) {
            $updates[] = "start_date = CURDATE()";
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $ticket_id;
        $types .= "i";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return response(true, "Ticket updated successfully");
        }
        
        return response(false, "Failed to update ticket");
    }

    // Update SLA priority
    public function updateSLAPriority($ticket_id, $sla_priority, $user_email) {
        $ticket = $this->getTicketById($ticket_id);
        
        if (!$ticket || $ticket['assign_to'] !== $user_email) {
            return response(false, "Ticket not found or access denied");
        }

        $query = "UPDATE " . $this->table_name . " SET sla_priority = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $sla_priority, $ticket_id);
        
        if ($stmt->execute()) {
            return response(true, "SLA priority updated successfully");
        }
        
        return response(false, "Failed to update SLA priority");
    }

    // Get ticket by ID
    private function getTicketById($ticket_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    // Get dashboard statistics
    public function getDashboardStats() {
        $stats = [];
        
        // Total tickets
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $result = $this->conn->query($query);
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Tickets by status
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status";
        $result = $this->conn->query($query);
        
        $status_counts = ['Open' => 0, 'In Progress' => 0, 'Done' => 0, 'Delayed' => 0];
        while ($row = $result->fetch_assoc()) {
            $status_counts[$row['status']] = $row['count'];
        }
        $stats['status'] = $status_counts;
        
        // Tickets by category
        $query = "SELECT category, COUNT(*) as count FROM " . $this->table_name . " GROUP BY category";
        $result = $this->conn->query($query);
        
        $category_counts = [];
        while ($row = $result->fetch_assoc()) {
            $category_counts[$row['category']] = $row['count'];
        }
        $stats['category'] = $category_counts;
        
        return response(true, "", $stats);
    }
}

// Handle AJAX requests - FIXED to use proper database connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'auth.php';
    
    $db = getDB();
    $ticket = new Ticket($db);
    $auth = new Auth($db);
    
    if (!$auth->isLoggedIn()) {
        response(false, "Please login first");
    }
    
    $action = $_POST['action'] ?? '';
    $user = $auth->getCurrentUser();
    
    switch ($action) {
        case 'create_ticket':
            $ticket->create(
                $user['id'],
                $user['name'],
                $user['department'],
                $_POST['category'],
                $_POST['description'],
                $_POST['priority'],
                $_POST['assign_to']
            );
            break;
            
        case 'get_user_tickets':
            $ticket->getUserTickets($user['id']);
            break;
            
        case 'get_all_tickets':
            if ($user['role'] === 'admin') {
                $ticket->getAllTickets();
            } else {
                response(false, "Access denied");
            }
            break;
            
        case 'update_status':
            $ticket->updateStatus($_POST['ticket_id'], $_POST['status'], $user['email']);
            break;
            
        case 'update_sla_priority':
            $ticket->updateSLAPriority($_POST['ticket_id'], $_POST['sla_priority'], $user['email']);
            break;
            
        case 'get_dashboard_stats':
            if ($user['role'] === 'admin') {
                $ticket->getDashboardStats();
            } else {
                response(false, "Access denied");
            }
            break;
            
        default:
            response(false, "Invalid action");
    }
}
?>
-- Database: u335370311_ticket (Hostinger)
-- Note: On shared hosting you may already have the database created. If so, remove the CREATE DATABASE line below and keep only the USE statement.
CREATE DATABASE IF NOT EXISTS u335370311_ticket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u335370311_ticket;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    department VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tickets
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(50) NOT NULL UNIQUE,
    reporter_id INT NOT NULL,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_dept VARCHAR(255) NOT NULL,
    category ENUM('Network', 'Software', 'Hardware', 'System & Aplikasi') NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    sla_priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    assign_to VARCHAR(255) NOT NULL,
    status ENUM('Open', 'In Progress', 'Done', 'Delayed', 'Revisi') DEFAULT 'Open',
    start_date DATE NULL,
    end_date DATE NULL,
    due_date DATE NULL,
    first_response_at DATETIME NULL,
    response_minutes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reporter (reporter_id),
    INDEX idx_status (status),
    INDEX idx_assign_to (assign_to),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ticket_counter (untuk auto-increment ticket number)
CREATE TABLE IF NOT EXISTS ticket_counter (
    id INT PRIMARY KEY DEFAULT 1,
    counter INT DEFAULT 0,
    CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial counter
INSERT INTO ticket_counter (id, counter) VALUES (1, 0) ON DUPLICATE KEY UPDATE counter=counter;

-- Table: attachments (MISSING TABLE - ADDED)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin users with demo123 password
INSERT INTO users (username, name, email, department, password, role) VALUES
('awan', 'Awan', 'awan@bamboovillagetrust.earth', 'IT', '$2y$10$7rLSvRVyTQORapkDOqnKhOFnBKtsNia9/jnE4CJrXQvTlh6LfDSRu', 'admin'),
('elky', 'Elky', 'elky@bamboovillagetrust.earth', 'IT', '$2y$10$7rLSvRVyTQORapkDOqnKhOFnBKtsNia9/jnE4CJrXQvTlh6LfDSRu', 'admin')
ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password);

-- Note: Password default adalah 'demo123' (hashed dengan bcrypt)
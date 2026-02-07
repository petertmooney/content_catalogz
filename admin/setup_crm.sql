-- CRM Database Setup for Content Catalogz
-- This file creates the CRM tables and enhances the quotes table

-- Create activities table for tracking client interactions
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    activity_type ENUM('call', 'email', 'meeting', 'note', 'task', 'quote_sent', 'invoice_sent', 'payment_received', 'other') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    activity_date DATETIME NOT NULL,
    duration_minutes INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_client (client_id),
    INDEX idx_date (activity_date),
    INDEX idx_type (activity_type),
    FOREIGN KEY (client_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tasks table for managing to-dos and follow-ups
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    client_id INT,
    assigned_to INT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_assigned (assigned_to),
    FOREIGN KEY (client_id) REFERENCES quotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create client_notes table for important client information
CREATE TABLE IF NOT EXISTS client_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    note_text TEXT NOT NULL,
    is_important BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_client (client_id),
    INDEX idx_important (is_important),
    FOREIGN KEY (client_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create client_tags table for categorizing clients
CREATE TABLE IF NOT EXISTS client_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client (client_id),
    INDEX idx_tag (tag_name),
    FOREIGN KEY (client_id) REFERENCES quotes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_tag (client_id, tag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance quotes table with CRM fields
-- Note: These will error if columns already exist, which is fine - we can ignore those errors

ALTER TABLE quotes ADD COLUMN lead_source VARCHAR(100);
ALTER TABLE quotes ADD COLUMN expected_value DECIMAL(10,2);
ALTER TABLE quotes ADD COLUMN probability INT DEFAULT 0;
ALTER TABLE quotes ADD COLUMN next_follow_up DATE;
ALTER TABLE quotes ADD COLUMN last_contact_date DATE;
ALTER TABLE quotes ADD COLUMN client_tags TEXT;

-- Display success message
SELECT 'CRM tables created successfully!' AS status;

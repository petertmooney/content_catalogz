-- Migration: Create invoice_services table
-- This table stores individual service items for invoices

CREATE TABLE IF NOT EXISTS invoice_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1.00,
    unit_price DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_invoice (invoice_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample service data for existing invoices
-- Invoice 2: Email Newsletter - January ($600)
INSERT INTO invoice_services (invoice_id, description, quantity, unit_price, total_cost) VALUES
(2, 'Email Newsletter Design and Development', 1, 400.00, 400.00),
(2, 'Content Creation and Copywriting', 1, 200.00, 200.00);

-- Invoice 3: Blog Posts + Content Calendar ($2400)
INSERT INTO invoice_services (invoice_id, description, quantity, unit_price, total_cost) VALUES
(3, 'Blog Post Writing (6 articles)', 6, 300.00, 1800.00),
(3, '3-Month Content Calendar Creation', 1, 600.00, 600.00);

SELECT 'migration_create_invoice_services_table: OK' AS status;
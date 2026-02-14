-- PaintUp Settings Table
-- Run this SQL in phpMyAdmin or MySQL

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT 'PaintUp',
    logo_path VARCHAR(255) NULL,
    primary_color VARCHAR(20) DEFAULT '#2563eb',
    secondary_color VARCHAR(20) DEFAULT '#1e293b',
    support_whatsapp VARCHAR(20) NULL,
    support_email VARCHAR(255) NULL,
    footer_text VARCHAR(500) NULL,
    gst_number VARCHAR(50) NULL,
    address TEXT NULL,
    invoice_prefix VARCHAR(20) DEFAULT 'INV',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings if not exists
INSERT INTO settings (
    id, company_name, primary_color, secondary_color, invoice_prefix, created_at, updated_at
) VALUES (
    1, 'PaintUp', '#2563eb', '#1e293b', 'INV', NOW(), NOW()
) ON DUPLICATE KEY UPDATE id = id;
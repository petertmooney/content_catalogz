-- Migration: add optional CRM fields to `quotes` table (idempotent)
-- Adds `lead_source`, `next_follow_up`, and `expected_value` if they don't already exist.

ALTER TABLE quotes ADD COLUMN IF NOT EXISTS lead_source VARCHAR(100) DEFAULT NULL;
ALTER TABLE quotes ADD COLUMN IF NOT EXISTS expected_value DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE quotes ADD COLUMN IF NOT EXISTS next_follow_up DATE DEFAULT NULL;

SELECT 'migration_2026_02_13_add_crm_fields: OK' AS status;

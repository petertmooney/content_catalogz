-- Migration: add optional CRM fields to `quotes` table
-- Adds `lead_source`, `next_follow_up`, and `expected_value`.
-- NOTE: ALTER will error if a column already exists; that's harmless for one-off migrations.

ALTER TABLE quotes ADD COLUMN lead_source VARCHAR(100) DEFAULT NULL;
ALTER TABLE quotes ADD COLUMN expected_value DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE quotes ADD COLUMN next_follow_up DATE DEFAULT NULL;

SELECT 'migration_2026_02_13_add_crm_fields: OK' AS status;

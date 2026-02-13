-- Migration: Update blank lead_source values to 'Website' for existing clients
-- This ensures all existing clients have a proper lead source for CRM analytics

UPDATE quotes SET lead_source = 'Website' WHERE lead_source IS NULL OR lead_source = '';

SELECT CONCAT('Updated ', ROW_COUNT(), ' clients with blank lead_source to "Website"') AS status;
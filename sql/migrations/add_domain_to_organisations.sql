-- Add domain column to organisations table (People Service)
-- Run this if the column does not already exist.
ALTER TABLE organisations
    ADD COLUMN IF NOT EXISTS domain VARCHAR(255) NULL AFTER name,
    ADD UNIQUE INDEX IF NOT EXISTS uq_org_domain (domain);

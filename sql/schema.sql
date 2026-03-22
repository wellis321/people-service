-- People Service Database Schema
-- UTF-8 / InnoDB throughout
-- Run: mysql -u root -p people_service < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ──────────────────────────────────────────────
-- Shared-auth tables (self-contained copy)
-- ──────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS organisations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT UNSIGNED NOT NULL,
    email           VARCHAR(255) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    first_name      VARCHAR(100) NOT NULL DEFAULT '',
    last_name       VARCHAR(100) NOT NULL DEFAULT '',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email_org (email, organisation_id),
    INDEX idx_users_org (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(100) NOT NULL,
    permissions     JSON,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_slug_org (slug, organisation_id),
    INDEX idx_roles_org (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id         INT UNSIGNED NOT NULL,
    role_id         INT UNSIGNED NOT NULL,
    organisation_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    INDEX idx_user_roles_org (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organisational_units (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT UNSIGNED NOT NULL,
    parent_id       INT UNSIGNED DEFAULT NULL,
    name            VARCHAR(255) NOT NULL,
    type            VARCHAR(100) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ou_org    (organisation_id),
    INDEX idx_ou_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────
-- Core People tables
-- ──────────────────────────────────────────────

-- Master person record
CREATE TABLE IF NOT EXISTS people (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT UNSIGNED NOT NULL,

    -- Identity
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    preferred_name  VARCHAR(100) DEFAULT NULL,
    date_of_birth   DATE         DEFAULT NULL,
    gender          ENUM('male','female','non_binary','other','prefer_not_to_say') DEFAULT NULL,
    pronouns        VARCHAR(50)  DEFAULT NULL,
    photo_path      VARCHAR(500) DEFAULT NULL,

    -- Status
    status          ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
    support_start   DATE         DEFAULT NULL,
    support_end     DATE         DEFAULT NULL,

    -- Identifiers
    nhs_number      VARCHAR(20)  DEFAULT NULL,
    local_authority_ref VARCHAR(50) DEFAULT NULL,

    -- Address
    address_line1   VARCHAR(255) DEFAULT NULL,
    address_line2   VARCHAR(255) DEFAULT NULL,
    city            VARCHAR(100) DEFAULT NULL,
    county          VARCHAR(100) DEFAULT NULL,
    postcode        VARCHAR(20)  DEFAULT NULL,

    -- Notes
    notes           TEXT         DEFAULT NULL,

    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_people_org    (organisation_id),
    INDEX idx_people_status (status),
    INDEX idx_people_name   (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact details (phone, email, next of kin etc.)
CREATE TABLE IF NOT EXISTS person_contacts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id       INT UNSIGNED NOT NULL,
    organisation_id INT UNSIGNED NOT NULL,
    contact_type    ENUM('phone','email','address','next_of_kin','legal_guardian','social_worker','gp','other') NOT NULL,
    label           VARCHAR(100) DEFAULT NULL,   -- e.g. "Mobile", "Work", "Mother"
    value           VARCHAR(500) NOT NULL,
    is_primary      TINYINT(1)   NOT NULL DEFAULT 0,
    notes           VARCHAR(500) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contacts_person (person_id),
    INDEX idx_contacts_org    (organisation_id),
    CONSTRAINT fk_contacts_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Care needs / support requirements
CREATE TABLE IF NOT EXISTS care_needs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id       INT UNSIGNED NOT NULL,
    organisation_id INT UNSIGNED NOT NULL,
    category        VARCHAR(100) NOT NULL,   -- e.g. "Communication", "Mobility", "Personal Care"
    description     TEXT         NOT NULL,
    severity        ENUM('low','medium','high') DEFAULT NULL,
    review_date     DATE         DEFAULT NULL,
    created_by      INT UNSIGNED DEFAULT NULL,   -- user id
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_care_needs_person (person_id),
    INDEX idx_care_needs_org    (organisation_id),
    CONSTRAINT fk_care_needs_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assigned key workers / support staff (cross-service reference)
CREATE TABLE IF NOT EXISTS person_keyworkers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id       INT UNSIGNED NOT NULL,
    organisation_id INT UNSIGNED NOT NULL,
    staff_id        INT UNSIGNED NOT NULL,   -- people.id in the Staff Service (PMS)
    display_name    VARCHAR(255) DEFAULT NULL,
    display_ref     VARCHAR(100) DEFAULT NULL,
    role_label      VARCHAR(100) DEFAULT NULL,   -- e.g. "Key Worker", "Support Lead"
    assigned_at     DATE         DEFAULT NULL,
    ended_at        DATE         DEFAULT NULL,
    INDEX idx_kw_person (person_id),
    INDEX idx_kw_org    (organisation_id),
    CONSTRAINT fk_kw_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link to organisational units (which service/location supports this person)
CREATE TABLE IF NOT EXISTS person_organisational_units (
    person_id INT UNSIGNED NOT NULL,
    unit_id   INT UNSIGNED NOT NULL,
    PRIMARY KEY (person_id, unit_id),
    CONSTRAINT fk_pou_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────
-- API & Webhook tables
-- ──────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS api_keys (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organisation_id INT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    key_hash        VARCHAR(64)  NOT NULL UNIQUE,   -- SHA-256 hex
    permissions     JSON         DEFAULT NULL,       -- e.g. ["read","write"]
    last_used_at    TIMESTAMP    DEFAULT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_keys_org (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────
-- Default data
-- ──────────────────────────────────────────────

-- Default roles (created per-organisation by setup.php; these are templates)
-- (no INSERT here — setup.php creates the first organisation and superadmin)

SET FOREIGN_KEY_CHECKS = 1;

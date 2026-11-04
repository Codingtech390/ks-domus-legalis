
CREATE TABLE IF NOT EXISTS case_highlights (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    case_type ENUM('important', 'pro_bono') NOT NULL,

    title VARCHAR(255) NOT NULL,
    court VARCHAR(180) DEFAULT NULL,
    case_reference VARCHAR(180) DEFAULT NULL,
    decision_year SMALLINT UNSIGNED DEFAULT NULL,

    role_description VARCHAR(500) DEFAULT NULL,
    summary TEXT NOT NULL,
    outcome TEXT DEFAULT NULL,

    public_url VARCHAR(500) DEFAULT NULL,

    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    display_order INT NOT NULL DEFAULT 0,

    created_by BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_case_highlights_admin
        FOREIGN KEY (created_by) REFERENCES admins(id)
        ON DELETE RESTRICT,

    INDEX idx_case_highlights_type_status_order
        (case_type, status, display_order),

    INDEX idx_case_highlights_year
        (decision_year)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

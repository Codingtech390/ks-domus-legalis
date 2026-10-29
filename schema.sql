
CREATE DATABASE IF NOT EXISTS legal_news_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE legal_news_portal;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    excerpt VARCHAR(600) NOT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(500) DEFAULT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_admin
        FOREIGN KEY (admin_id) REFERENCES admins(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_articles_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT,
    INDEX idx_articles_status_date (status, published_at),
    INDEX idx_articles_category (category_id),
    INDEX idx_articles_admin (admin_id),
    FULLTEXT INDEX ft_articles_search (title, excerpt, content)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name, slug, description) VALUES
('Supreme Court', 'supreme-court', 'Supreme Court judgments and legal developments.'),
('Constitutional Law', 'constitutional-law', 'Constitutional questions, rights and public law.'),
('Corporate Law', 'corporate-law', 'Company law, transactions and regulatory developments.'),
('Criminal Law', 'criminal-law', 'Criminal justice, procedure and notable judgments.'),
('Civil Law', 'civil-law', 'Civil litigation, disputes and procedural updates.'),
('Legal Analysis', 'legal-analysis', 'Commentary and analysis of important legal issues.');

-- Development administrator:
-- Email: admin@lawportal.local
-- Password: ChangeMe123!
-- Replace this account's password before deployment.
-- The hash below is a PHP password_hash() bcrypt hash.
INSERT IGNORE INTO admins (name, email, password)
VALUES (
    'Portal Administrator',
    'admin@lawportal.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.'
);

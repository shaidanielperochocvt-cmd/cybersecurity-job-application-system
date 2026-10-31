-- Cybersecurity Job Application PDO project
-- Import this file using phpMyAdmin or the MySQL command line.
CREATE DATABASE IF NOT EXISTS russian_grocery_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE russian_grocery_db;

-- USER ENTITY: one user can create/update many parent and child records.
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- PARENT ENTITY: each job application is created and audited by a user.
CREATE TABLE IF NOT EXISTS job_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    position VARCHAR(120) NOT NULL,
    education VARCHAR(150) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_job_applications_created_by (created_by),
    KEY idx_job_applications_updated_by (updated_by),
    CONSTRAINT fk_job_applications_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_job_applications_updated_by
        FOREIGN KEY (updated_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- CHILD ENTITY: one job application can have many skills.
CREATE TABLE IF NOT EXISTS application_skills (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(120) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_application_skills_application_id (application_id),
    KEY idx_application_skills_created_by (created_by),
    KEY idx_application_skills_updated_by (updated_by),
    CONSTRAINT fk_application_skills_application
        FOREIGN KEY (application_id) REFERENCES job_applications (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_application_skills_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_application_skills_updated_by
        FOREIGN KEY (updated_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

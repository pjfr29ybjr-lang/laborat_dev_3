-- ============================================================
-- WEATHER SYSTEM — IPIL Projecto #03
-- Database Schema: weather_system
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `weather_system`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `weather_system`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`             VARCHAR(100)  NOT NULL,
  `email`            VARCHAR(150)  NOT NULL UNIQUE,
  `password`         VARCHAR(255)  NOT NULL,
  `role`             ENUM('admin','user') NOT NULL DEFAULT 'user',
  `avatar`           VARCHAR(255)  DEFAULT NULL,
  `language`         VARCHAR(10)   NOT NULL DEFAULT 'pt',
  `theme`            ENUM('light','dark') NOT NULL DEFAULT 'light',
  `default_city`     VARCHAR(100)  DEFAULT NULL,
  `reset_token`      VARCHAR(255)  DEFAULT NULL,
  `reset_expires`    DATETIME      DEFAULT NULL,
  `is_active`        TINYINT(1)    NOT NULL DEFAULT 1,
  `last_login`       DATETIME      DEFAULT NULL,
  `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email`  (`email`),
  INDEX `idx_role`   (`role`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: favorites
-- ============================================================
CREATE TABLE IF NOT EXISTS `favorites` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `city_name`   VARCHAR(100) NOT NULL,
  `country`     VARCHAR(10)  NOT NULL,
  `lat`         DECIMAL(10,6) DEFAULT NULL,
  `lon`         DECIMAL(10,6) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_city` (`user_id`, `city_name`, `country`),
  INDEX `idx_user_id` (`user_id`),
  CONSTRAINT `fk_favorites_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: search_history
-- ============================================================
CREATE TABLE IF NOT EXISTS `search_history` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `city_name`   VARCHAR(100) NOT NULL,
  `country`     VARCHAR(10)  DEFAULT NULL,
  `lat`         DECIMAL(10,6) DEFAULT NULL,
  `lon`         DECIMAL(10,6) DEFAULT NULL,
  `weather_data` JSON DEFAULT NULL,
  `searched_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id`    (`user_id`),
  INDEX `idx_searched_at`(`searched_at`),
  CONSTRAINT `fk_history_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED: admin user
-- password: Admin@1234
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `language`, `theme`)
VALUES (
  'Administrador',
  'admin@weather.ipil.ao',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@1234
  'admin',
  'pt',
  'dark'
) ON DUPLICATE KEY UPDATE `id` = `id`;

SET FOREIGN_KEY_CHECKS = 1;
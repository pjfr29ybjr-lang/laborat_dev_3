-- =============================================================
--  Sistema de Previsão do Tempo — IPIL Projecto #03
--  Base de dados: weather_system
--  Autor: Judson Paiva
--  Data: 2025/2026
-- =============================================================

CREATE DATABASE IF NOT EXISTS weather_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE weather_system;

-- -------------------------------------------------------------
-- Tabela: users
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100)    NOT NULL,
  email         VARCHAR(150)    NOT NULL UNIQUE,
  password      VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
  role          ENUM('user','admin') NOT NULL DEFAULT 'user',
  language      VARCHAR(5)      NOT NULL DEFAULT 'pt',
  theme         ENUM('light','dark') NOT NULL DEFAULT 'light',
  unit          ENUM('metric','imperial') NOT NULL DEFAULT 'metric',
  reset_token   VARCHAR(255)    NULL,
  reset_expires DATETIME        NULL,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Tabela: favorites
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  city_name  VARCHAR(100) NOT NULL,
  country    VARCHAR(10)  NOT NULL,
  lat        DECIMAL(9,6) NULL,
  lon        DECIMAL(9,6) NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_city (user_id, city_name, country),
  INDEX idx_user_id (user_id),
  CONSTRAINT fk_favorites_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Tabela: search_history
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS search_history (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  city_name  VARCHAR(100) NOT NULL,
  country    VARCHAR(10)  NOT NULL,
  temp_c     DECIMAL(5,2) NULL COMMENT 'temperatura registada no momento da pesquisa',
  condition  VARCHAR(100) NULL COMMENT 'descrição do tempo (ex: clear sky)',
  searched_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_user_history (user_id, searched_at),
  CONSTRAINT fk_history_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Dados iniciais (seed) — utilizador admin
-- password: Admin@123  (hash bcrypt)
-- -------------------------------------------------------------
INSERT INTO users (name, email, password, role) VALUES
(
  'Administrador',
  'admin@weather.ao',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);
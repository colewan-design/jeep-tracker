-- ============================================================
-- Jeep Tracker — Full Schema + Seed Data
-- Generated: 2026-05-07
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255) NOT NULL,
  `email`             VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password`          VARCHAR(255) NOT NULL,
  `role`              ENUM('admin','driver','passenger') NOT NULL DEFAULT 'passenger',
  `remember_token`    VARCHAR(100) NULL DEFAULT NULL,
  `created_at`        TIMESTAMP NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_reset_tokens
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: sessions
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255) NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address`    VARCHAR(45) NULL DEFAULT NULL,
  `user_agent`    TEXT NULL DEFAULT NULL,
  `payload`       LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: personal_access_tokens
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id`   BIGINT UNSIGNED NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `token`          VARCHAR(64) NOT NULL,
  `abilities`      TEXT NULL DEFAULT NULL,
  `last_used_at`   TIMESTAMP NULL DEFAULT NULL,
  `expires_at`     TIMESTAMP NULL DEFAULT NULL,
  `created_at`     TIMESTAMP NULL DEFAULT NULL,
  `updated_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: jeeps
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jeeps` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255) NOT NULL,
  `plate_number` VARCHAR(255) NOT NULL,
  `route_name`   VARCHAR(255) NULL DEFAULT NULL,
  `capacity`     INT UNSIGNED NOT NULL DEFAULT 16,
  `status`       ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'inactive',
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jeeps_plate_number_unique` (`plate_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: jeep_locations
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jeep_locations` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jeep_id`     BIGINT UNSIGNED NOT NULL,
  `latitude`    DECIMAL(10,7) NOT NULL,
  `longitude`   DECIMAL(10,7) NOT NULL,
  `speed`       FLOAT NULL DEFAULT NULL COMMENT 'km/h',
  `heading`     FLOAT NULL DEFAULT NULL COMMENT 'degrees 0-360',
  `accuracy`    FLOAT NULL DEFAULT NULL COMMENT 'meters',
  `recorded_at` TIMESTAMP NOT NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jeep_locations_jeep_id_recorded_at_index` (`jeep_id`, `recorded_at`),
  CONSTRAINT `jeep_locations_jeep_id_foreign` FOREIGN KEY (`jeep_id`) REFERENCES `jeeps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: trips
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `trips` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jeep_id`     BIGINT UNSIGNED NOT NULL,
  `origin`      VARCHAR(255) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `status`      ENUM('ongoing','completed','cancelled') NOT NULL DEFAULT 'ongoing',
  `started_at`  TIMESTAMP NULL DEFAULT NULL,
  `ended_at`    TIMESTAMP NULL DEFAULT NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `trips_jeep_id_foreign` FOREIGN KEY (`jeep_id`) REFERENCES `jeeps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Laravel migrations tracking table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2024_01_01_000001_create_users_table', 1),
('2024_01_01_000002_create_personal_access_tokens_table', 1),
('2024_01_01_000003_create_jeeps_table', 1),
('2024_01_01_000004_create_jeep_locations_table', 1),
('2024_01_01_000005_create_trips_table', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Seed Data
-- ============================================================

-- --------------------------------------------------------
-- Users (password for all accounts: password123)
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Admin',               'admin@jeeptracker.com',     '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'admin',     NOW(), NOW()),
('Juan dela Cruz',      'juan@jeeptracker.com',      '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Pedro Santos',        'pedro@jeeptracker.com',     '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Mario Reyes',         'mario@jeeptracker.com',     '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Jose Bautista',       'jose@jeeptracker.com',      '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Ramon Garcia',        'ramon@jeeptracker.com',     '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Antonio Villanueva',  'antonio@jeeptracker.com',   '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Eduardo Flores',      'eduardo@jeeptracker.com',   '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Roberto Cruz',        'roberto@jeeptracker.com',   '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'driver',    NOW(), NOW()),
('Sample Passenger',    'passenger@jeeptracker.com', '$2y$10$7fGk8j6bpZGHNvdE0jFWTuQW5Kkc82HEkIC5rzU.1EO0NsbbbJCQe', 'passenger', NOW(), NOW());

-- --------------------------------------------------------
-- Jeeps
-- --------------------------------------------------------
INSERT INTO `jeeps` (`name`, `plate_number`, `route_name`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
('Happy Hollow 01',   'BAG-1001', 'Happy Hollow - Town',        20, 'active',      NOW(), NOW()),
('Happy Hollow 02',   'BAG-1002', 'Happy Hollow - Town',        20, 'active',      NOW(), NOW()),
('Aurora Hill 01',    'BAG-2001', 'Aurora Hill - Town',         20, 'active',      NOW(), NOW()),
('Aurora Hill 02',    'BAG-2002', 'Aurora Hill - Town',         20, 'active',      NOW(), NOW()),
('Trancoville 01',    'BAG-3001', 'Trancoville - Town',         20, 'active',      NOW(), NOW()),
('Trancoville 02',    'BAG-3002', 'Trancoville - Town',         20, 'inactive',    NOW(), NOW()),
('Mines View 01',     'BAG-4001', 'Mines View - Baguio Plaza',  20, 'active',      NOW(), NOW()),
('Mines View 02',     'BAG-4002', 'Mines View - Baguio Plaza',  20, 'active',      NOW(), NOW()),
('Loakan 01',         'BAG-5001', 'Loakan - Town',              20, 'active',      NOW(), NOW()),
('Loakan 02',         'BAG-5002', 'Loakan - Town',              20, 'maintenance', NOW(), NOW()),
('La Trinidad 01',    'BAG-6001', 'La Trinidad - Town',         20, 'active',      NOW(), NOW()),
('La Trinidad 02',    'BAG-6002', 'La Trinidad - Town',         20, 'active',      NOW(), NOW()),
('Marcos Highway 01', 'BAG-7001', 'Marcos Highway - SM City',   20, 'active',      NOW(), NOW()),
('Engineers Hill 01', 'BAG-8001', 'Engineers Hill - Town',      20, 'active',      NOW(), NOW());

-- --------------------------------------------------------
-- Jeep Locations (one per jeep, jeep_id 1-14)
-- --------------------------------------------------------
INSERT INTO `jeep_locations` (`jeep_id`, `latitude`, `longitude`, `speed`, `heading`, `accuracy`, `recorded_at`, `created_at`, `updated_at`) VALUES
(1,  16.4090000, 120.5930000, 15, 180, 10, NOW(), NOW(), NOW()),
(2,  16.4120000, 120.5950000, 20, 90,  10, NOW(), NOW(), NOW()),
(3,  16.4220000, 120.6010000, 12, 270, 10, NOW(), NOW(), NOW()),
(4,  16.4200000, 120.5990000, 18, 45,  10, NOW(), NOW(), NOW()),
(5,  16.4180000, 120.6080000, 25, 135, 10, NOW(), NOW(), NOW()),
(6,  16.4160000, 120.6060000, 10, 315, 10, NOW(), NOW(), NOW()),
(7,  16.4102000, 120.6320000, 22, 200, 10, NOW(), NOW(), NOW()),
(8,  16.4115000, 120.6300000, 17, 60,  10, NOW(), NOW(), NOW()),
(9,  16.3750000, 120.5690000, 28, 330, 10, NOW(), NOW(), NOW()),
(10, 16.3770000, 120.5710000, 8,  150, 10, NOW(), NOW(), NOW()),
(11, 16.4600000, 120.5880000, 21, 240, 10, NOW(), NOW(), NOW()),
(12, 16.4580000, 120.5860000, 14, 75,  10, NOW(), NOW(), NOW()),
(13, 16.4350000, 120.6150000, 19, 300, 10, NOW(), NOW(), NOW()),
(14, 16.4280000, 120.5980000, 11, 120, 10, NOW(), NOW(), NOW());

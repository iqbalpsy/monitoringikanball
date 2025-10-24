-- =====================================================
-- Database Export for AquaMonitor System
-- Database: monitoringikan
-- Generated: 2025-10-12 23:30:00
-- Laravel Version: 12.x
-- =====================================================

-- Drop database if exists and create new
DROP DATABASE IF EXISTS `monitoringikan`;
CREATE DATABASE `monitoringikan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `monitoringikan`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Table: migrations
-- =====================================================
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: users
-- =====================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users (password: password123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin IoT Fish', 'admin@fishmonitoring.com', '$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu', 'admin', 1, NOW(), NOW()),
(2, 'User Test', 'user@test.com', '$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu', 'user', 1, NOW(), NOW()),
(3, 'Budi Santoso', 'budi@example.com', '$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu', 'user', 1, NOW(), NOW()),
(4, 'Dewi Lestari', 'dewi@example.com', '$2y$12$LQv3c1yycaGdyBaFcxXNXOXTQrZfXKHLnWdX7XGmXWHJiWx9jFCGu', 'user', 1, NOW(), NOW());

-- =====================================================
-- Table: devices
-- =====================================================
DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devices_device_id_unique` (`device_id`),
  KEY `devices_created_by_foreign` (`created_by`),
  CONSTRAINT `devices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default devices
INSERT INTO `devices` (`id`, `device_id`, `name`, `location`, `description`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'DEVICE001', 'Kolam A - Sensor Utama', 'Kolam A, Sektor 1', 'Sensor monitoring untuk kolam A dengan kapasitas 1000 liter', 1, 1, NOW(), NOW()),
(2, 'DEVICE002', 'Kolam B - Sensor Cadangan', 'Kolam B, Sektor 2', 'Sensor backup untuk kolam B dengan kapasitas 800 liter', 1, 1, NOW(), NOW());

-- =====================================================
-- Table: sensor_data
-- =====================================================
DROP TABLE IF EXISTS `sensor_data`;
CREATE TABLE `sensor_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` bigint(20) unsigned NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `ph` decimal(4,2) DEFAULT NULL,
  `oxygen` decimal(4,2) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sensor_data_device_id_foreign` (`device_id`),
  KEY `sensor_data_recorded_at_index` (`recorded_at`),
  CONSTRAINT `sensor_data_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample sensor data (last 24 hours)
INSERT INTO `sensor_data` (`device_id`, `temperature`, `ph`, `oxygen`, `recorded_at`, `created_at`, `updated_at`) VALUES
-- Device 1 - Recent data
(1, 27.5, 7.2, 6.8, DATE_SUB(NOW(), INTERVAL 5 MINUTE), NOW(), NOW()),
(1, 27.3, 7.1, 6.9, DATE_SUB(NOW(), INTERVAL 10 MINUTE), NOW(), NOW()),
(1, 27.8, 7.3, 6.7, DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), NOW()),
(1, 27.6, 7.2, 6.8, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NOW(), NOW()),
(1, 27.4, 7.0, 7.0, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), NOW()),
(1, 27.7, 7.1, 6.9, DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW(), NOW()),
(1, 27.9, 7.2, 6.8, DATE_SUB(NOW(), INTERVAL 4 HOUR), NOW(), NOW()),
(1, 28.1, 7.3, 6.7, DATE_SUB(NOW(), INTERVAL 8 HOUR), NOW(), NOW()),
(1, 27.2, 7.0, 7.1, DATE_SUB(NOW(), INTERVAL 12 HOUR), NOW(), NOW()),
(1, 26.8, 6.9, 7.2, DATE_SUB(NOW(), INTERVAL 18 HOUR), NOW(), NOW()),
(1, 26.5, 6.8, 7.3, DATE_SUB(NOW(), INTERVAL 24 HOUR), NOW(), NOW()),

-- Device 2 - Recent data
(2, 26.5, 7.5, 7.2, DATE_SUB(NOW(), INTERVAL 5 MINUTE), NOW(), NOW()),
(2, 26.7, 7.4, 7.1, DATE_SUB(NOW(), INTERVAL 10 MINUTE), NOW(), NOW()),
(2, 26.4, 7.6, 7.3, DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), NOW()),
(2, 26.6, 7.5, 7.2, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NOW(), NOW()),
(2, 26.8, 7.4, 7.1, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), NOW()),
(2, 26.3, 7.6, 7.0, DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW(), NOW()),
(2, 26.9, 7.5, 7.2, DATE_SUB(NOW(), INTERVAL 4 HOUR), NOW(), NOW()),
(2, 27.0, 7.4, 7.1, DATE_SUB(NOW(), INTERVAL 8 HOUR), NOW(), NOW()),
(2, 26.2, 7.6, 7.3, DATE_SUB(NOW(), INTERVAL 12 HOUR), NOW(), NOW()),
(2, 25.8, 7.7, 7.4, DATE_SUB(NOW(), INTERVAL 18 HOUR), NOW(), NOW()),
(2, 25.5, 7.8, 7.5, DATE_SUB(NOW(), INTERVAL 24 HOUR), NOW(), NOW());

-- =====================================================
-- Table: device_controls
-- =====================================================
DROP TABLE IF EXISTS `device_controls`;
CREATE TABLE `device_controls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `executed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_controls_device_id_foreign` (`device_id`),
  KEY `device_controls_user_id_foreign` (`user_id`),
  CONSTRAINT `device_controls_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_controls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: user_device_access
-- =====================================================
DROP TABLE IF EXISTS `user_device_access`;
CREATE TABLE `user_device_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_id` bigint(20) unsigned NOT NULL,
  `can_view_data` tinyint(1) NOT NULL DEFAULT '1',
  `can_control` tinyint(1) NOT NULL DEFAULT '0',
  `granted_by` bigint(20) unsigned DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device_access_user_id_device_id_unique` (`user_id`,`device_id`),
  KEY `user_device_access_device_id_foreign` (`device_id`),
  KEY `user_device_access_granted_by_foreign` (`granted_by`),
  CONSTRAINT `user_device_access_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_device_access_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_device_access_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grant access to users
INSERT INTO `user_device_access` (`user_id`, `device_id`, `can_view_data`, `can_control`, `granted_by`, `granted_at`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 1, 1, NOW(), NOW(), NOW()),
(2, 2, 1, 1, 1, NOW(), NOW(), NOW()),
(3, 1, 1, 0, 1, NOW(), NOW(), NOW()),
(4, 1, 1, 0, 1, NOW(), NOW(), NOW());

-- =====================================================
-- Table: user_settings
-- =====================================================
DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE `user_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `temp_min` decimal(5,2) NOT NULL DEFAULT '24.00',
  `temp_max` decimal(5,2) NOT NULL DEFAULT '30.00',
  `ph_min` decimal(4,2) NOT NULL DEFAULT '6.50',
  `ph_max` decimal(4,2) NOT NULL DEFAULT '8.50',
  `oxygen_min` decimal(4,2) NOT NULL DEFAULT '5.00',
  `oxygen_max` decimal(4,2) NOT NULL DEFAULT '8.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_settings_user_id_unique` (`user_id`),
  CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings for users
INSERT INTO `user_settings` (`user_id`, `temp_min`, `temp_max`, `ph_min`, `ph_max`, `oxygen_min`, `oxygen_max`, `created_at`, `updated_at`) VALUES
(1, 24.00, 30.00, 6.50, 8.50, 5.00, 8.00, NOW(), NOW()),
(2, 24.00, 30.00, 6.50, 8.50, 5.00, 8.00, NOW(), NOW()),
(3, 24.00, 30.00, 6.50, 8.50, 5.00, 8.00, NOW(), NOW()),
(4, 24.00, 30.00, 6.50, 8.50, 5.00, 8.00, NOW(), NOW());

-- =====================================================
-- Table: sessions
-- =====================================================
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: cache
-- =====================================================
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: cache_locks
-- =====================================================
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: jobs
-- =====================================================
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: job_batches
-- =====================================================
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: failed_jobs
-- =====================================================
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- END OF DATABASE EXPORT
-- =====================================================

-- IMPORT INSTRUCTIONS:
-- 1. Buka phpMyAdmin di XAMPP (http://localhost/phpmyadmin)
-- 2. Klik tab "Import"
-- 3. Choose File: Pilih file ini (monitoringikan_database.sql)
-- 4. Klik "Go" atau "Kirim"
-- 5. Database akan otomatis terbuat dengan semua data

-- DEFAULT USERS:
-- Admin:
--   Email: admin@fishmonitoring.com
--   Password: password123
--
-- User Test:
--   Email: user@test.com
--   Password: password123
--
-- Other Users:
--   Email: budi@example.com / password123
--   Email: dewi@example.com / password123

-- NOTES:
-- - Semua password di-hash dengan bcrypt
-- - Data sensor sample untuk 24 jam terakhir
-- - 2 devices (Kolam A & B) sudah terdaftar
-- - User settings dengan threshold default
-- - Foreign key constraints aktif

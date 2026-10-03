-- =====================================================================
-- SafeHer: A Violence Against Women and Children Reporting System
-- Database Schema & Initial Seed Data
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `safeher_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `safeher_db`;

-- ---------------------------------------------------------------------
-- Table Drops
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `residents`;
DROP TABLE IF EXISTS `case_updates`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `cases`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `barangays`;

-- ---------------------------------------------------------------------
-- Table: barangays
-- ---------------------------------------------------------------------
CREATE TABLE `barangays` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `municipality` VARCHAR(100) NOT NULL DEFAULT 'Quezon City',
  `province` VARCHAR(100) NOT NULL DEFAULT 'Metro Manila',
  `assigned_staff_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_barangay_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'barangay_staff', 'complainant') NOT NULL DEFAULT 'complainant',
  `barangay_id` INT UNSIGNED NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_barangay` (`barangay_id`),
  INDEX `idx_users_email` (`email`),
  CONSTRAINT `fk_users_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key back to barangays for assigned_staff_id
ALTER TABLE `barangays`
  ADD CONSTRAINT `fk_barangays_staff` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------------------------------------------------------------------
-- Table: cases
-- ---------------------------------------------------------------------
CREATE TABLE `cases` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_number` VARCHAR(30) NOT NULL UNIQUE,
  `complainant_id` INT UNSIGNED NOT NULL,
  `barangay_id` INT UNSIGNED NOT NULL,
  `type_of_violence` ENUM('physical', 'sexual', 'psychological', 'economic', 'others') NOT NULL,
  `incident_date` DATE NOT NULL,
  `location` VARCHAR(255) NULL,
  `description` TEXT NOT NULL,
  `evidence_path` VARCHAR(255) NULL,
  `status` ENUM('pending', 'under_investigation', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
  `remarks` TEXT NULL,
  `assigned_staff_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cases_case_number` (`case_number`),
  INDEX `idx_cases_status` (`status`),
  INDEX `idx_cases_tov` (`type_of_violence`),
  INDEX `idx_cases_barangay` (`barangay_id`),
  INDEX `idx_cases_complainant` (`complainant_id`),
  INDEX `idx_cases_created` (`created_at`),
  CONSTRAINT `fk_cases_complainant` FOREIGN KEY (`complainant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cases_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_cases_staff` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: case_updates (Timeline Audit Trail)
-- ---------------------------------------------------------------------
CREATE TABLE `case_updates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` INT UNSIGNED NOT NULL,
  `updated_by` INT UNSIGNED NOT NULL,
  `old_status` ENUM('pending', 'under_investigation', 'resolved', 'dismissed') NOT NULL,
  `new_status` ENUM('pending', 'under_investigation', 'resolved', 'dismissed') NOT NULL,
  `remarks` TEXT NOT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_updates_case` (`case_id`),
  CONSTRAINT `fk_updates_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_updates_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: notifications (SMS logs & user notifications)
-- ---------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `case_id` INT UNSIGNED NULL,
  `recipient_phone` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `sms_status` ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
  `sent_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notifications_user` (`user_id`),
  INDEX `idx_notifications_case` (`case_id`),
  INDEX `idx_notifications_status` (`sms_status`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: residents (Resident Profiling & Community Registry)
-- ---------------------------------------------------------------------
CREATE TABLE `residents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `barangay_id` INT UNSIGNED NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `suffix` VARCHAR(20) NULL,
  `gender` ENUM('female', 'male', 'other') NOT NULL,
  `birth_date` DATE NOT NULL,
  `civil_status` ENUM('single', 'married', 'widowed', 'separated', 'live_in') NOT NULL DEFAULT 'single',
  `contact_number` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL,
  `address` VARCHAR(255) NOT NULL,
  `is_vulnerable` TINYINT(1) NOT NULL DEFAULT 0,
  `vulnerability_type` VARCHAR(100) NULL,
  `emergency_contact_name` VARCHAR(150) NULL,
  `emergency_contact_phone` VARCHAR(20) NULL,
  `occupation` VARCHAR(100) NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_residents_barangay` (`barangay_id`),
  INDEX `idx_residents_name` (`last_name`, `first_name`),
  INDEX `idx_residents_gender` (`gender`),
  INDEX `idx_residents_vulnerable` (`is_vulnerable`),
  CONSTRAINT `fk_residents_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_residents_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SEED DATA - BARANGAY GOGON CENTRO, VIRAC, CATANDUANES
-- Default Passwords:
-- Admin (Barangay Captain): Admin@12345
-- Staff: Staff@12345
-- Complainant: User@12345
-- =====================================================================

-- 1. Insert Barangay (Single Barangay Scope: Gogon Centro)
INSERT INTO `barangays` (`id`, `name`, `municipality`, `province`, `created_at`) VALUES
(1, 'Barangay Gogon Centro', 'Virac', 'Catanduanes', '2026-01-01 08:00:00');

-- 2. Insert Users (Barangay Captain / Admin, Staff, Complainants)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `barangay_id`, `status`, `created_at`) VALUES
(1, 'Hon. Roberto Dela Cruz (Barangay Captain)', 'admin@safeher.gov.ph', '09171234567', '$2y$12$YCP4WloNCruol00lkidoJeYyQk5ArwLYtUqg1ToS2/KDIvL6MPVhC', 'admin', 1, 'active', '2026-01-01 08:00:00'),
(2, 'Elena Santos (VAW Desk Officer)', 'staff.gogon@safeher.gov.ph', '09181234567', '$2y$12$qMl16Egj.7oegW0LOVU0PeI6CsA91cNZLJUHm62XQ3gy10Ytzg.KG', 'barangay_staff', 1, 'active', '2026-01-02 09:00:00'),
(3, 'Carla Ramos (Barangay Staff)', 'staff2.gogon@safeher.gov.ph', '09191234568', '$2y$12$qMl16Egj.7oegW0LOVU0PeI6CsA91cNZLJUHm62XQ3gy10Ytzg.KG', 'barangay_staff', 1, 'active', '2026-01-02 09:30:00'),
(4, 'Maria Clara Reyes', 'maria.clara@example.com', '09201234569', '$2y$12$5.hIsG3OtkTQTK.I8TJuh.9NMdNIDvVloounXrKhQH89wWPRFWzcK', 'complainant', 1, 'active', '2026-01-15 10:00:00'),
(5, 'Juana Dela Cruz', 'juana.delacruz@example.com', '09211234570', '$2y$12$5.hIsG3OtkTQTK.I8TJuh.9NMdNIDvVloounXrKhQH89wWPRFWzcK', 'complainant', 1, 'active', '2026-02-01 11:15:00'),
(6, 'Grace Mendoza', 'grace.mendoza@example.com', '09221234571', '$2y$12$5.hIsG3OtkTQTK.I8TJuh.9NMdNIDvVloounXrKhQH89wWPRFWzcK', 'complainant', 1, 'active', '2026-02-10 14:20:00');

-- Link staff to barangay
UPDATE `barangays` SET `assigned_staff_id` = 2 WHERE `id` = 1;

-- 3. Insert Sample Cases in Gogon Centro
INSERT INTO `cases` (`id`, `case_number`, `complainant_id`, `barangay_id`, `type_of_violence`, `incident_date`, `location`, `description`, `evidence_path`, `status`, `remarks`, `assigned_staff_id`, `created_at`, `updated_at`) VALUES
(1, 'SH-2026-0001', 4, 1, 'physical', '2026-01-14', 'Purok 3 - Near Gogon Centro Elementary School', 'Physical altercation and harassment occurred inside residence causing physical injury.', NULL, 'resolved', 'BPO issued and conciliation completed at Gogon Centro Barangay Hall. Victim provided support services.', 2, '2026-01-15 10:30:00', '2026-01-20 16:00:00'),
(2, 'SH-2026-0002', 5, 1, 'economic', '2026-01-28', 'Purok 1 - Riverside Zone, Gogon Centro', 'Deprivation of financial support for minor children and emotional threats.', NULL, 'under_investigation', 'Scheduled first hearing with both parties at the Barangay Gogon Centro Hall.', 2, '2026-02-01 11:30:00', '2026-02-05 13:45:00'),
(3, 'SH-2026-0003', 6, 1, 'psychological', '2026-02-08', 'Purok 4 - Near Chapel, Gogon Centro', 'Repeated public intimidation, verbal harassment, and stalking behavior.', NULL, 'pending', NULL, NULL, '2026-02-10 14:45:00', '2026-02-10 14:45:00'),
(4, 'SH-2026-0004', 4, 1, 'sexual', '2026-03-01', 'Purok 2 - Commercial boundary area, Virac', 'Unwanted sexual advance and aggressive physical boundary violation.', NULL, 'under_investigation', 'Referred to PNP Virac Women and Children Protection Desk (WCPD).', 2, '2026-03-02 09:15:00', '2026-03-03 10:00:00'),
(5, 'SH-2026-0005', 5, 1, 'psychological', '2026-03-10', 'Purok 5 - Residential area, Gogon Centro', 'Threats through text messages and social media causing severe distress.', NULL, 'resolved', 'Victim referred to Virac MSWDO psychosocial counseling. Case closed peacefully.', 2, '2026-03-11 16:20:00', '2026-03-14 11:00:00');

-- 4. Insert Case Updates (Timeline)
INSERT INTO `case_updates` (`id`, `case_id`, `updated_by`, `old_status`, `new_status`, `remarks`, `updated_at`) VALUES
(1, 1, 2, 'pending', 'under_investigation', 'Case accepted by Barangay Gogon Centro VAW Desk. Contacted complainant for initial intake.', '2026-01-16 09:00:00'),
(2, 1, 2, 'under_investigation', 'resolved', 'BPO issued and conciliation completed. Support provided.', '2026-01-20 16:00:00'),
(3, 2, 2, 'pending', 'under_investigation', 'Scheduled conciliation at Gogon Centro Barangay Hall.', '2026-02-05 13:45:00'),
(4, 4, 2, 'pending', 'under_investigation', 'Referred to PNP Virac WCPD for formal legal assistance.', '2026-03-03 10:00:00'),
(5, 5, 1, 'pending', 'resolved', 'Victim referred to MSWDO Virac counseling. Case resolved.', '2026-03-14 11:00:00');

-- 5. Insert SMS Notifications Log
INSERT INTO `notifications` (`id`, `user_id`, `case_id`, `recipient_phone`, `message`, `sms_status`, `sent_at`, `created_at`) VALUES
(1, 4, 1, '09201234569', '[SafeHer Gogon Centro] Your report SH-2026-0001 has been submitted. The VAW Desk will review your case shortly.', 'sent', '2026-01-15 10:30:05', '2026-01-15 10:30:00'),
(2, 4, 1, '09201234569', '[SafeHer Gogon Centro] Update on Case SH-2026-0001: Status is now RESOLVED. Remarks: BPO issued and conciliation completed.', 'sent', '2026-01-20 16:00:05', '2026-01-20 16:00:00'),
(3, 5, 2, '09211234570', '[SafeHer Gogon Centro] Your report SH-2026-0002 has been submitted. The VAW Desk will review your case shortly.', 'sent', '2026-02-01 11:30:05', '2026-02-01 11:30:00'),
(4, 6, 3, '09221234571', '[SafeHer Gogon Centro] Your report SH-2026-0003 has been submitted. The VAW Desk will review your case shortly.', 'sent', '2026-02-10 14:45:05', '2026-02-10 14:45:00'),
(5, 4, 4, '09201234569', '[SafeHer Gogon Centro] Update on Case SH-2026-0004: Status is now UNDER INVESTIGATION. Remarks: Referred to PNP Virac WCPD.', 'sent', '2026-03-03 10:00:05', '2026-03-03 10:00:00');

-- 6. Insert Resident Profiling Seed Data (Barangay Gogon Centro)
INSERT INTO `residents` (`id`, `barangay_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birth_date`, `civil_status`, `contact_number`, `email`, `address`, `is_vulnerable`, `vulnerability_type`, `emergency_contact_name`, `emergency_contact_phone`, `occupation`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Maria Clara', 'Santos', 'Reyes', NULL, 'female', '1995-04-12', 'single', '09201234569', 'maria.clara@example.com', 'Purok 3 - Near Elementary School, Gogon Centro', 1, 'Solo Parent', 'Juan Reyes', '09170001111', 'Small Business Owner', 2, '2026-01-10 09:00:00', '2026-01-10 09:00:00'),
(2, 1, 'Rosa', 'Alcantara', 'Luna', NULL, 'female', '1958-08-25', 'widowed', '09185552345', 'rosa.luna@example.com', 'Purok 1 - Riverside Zone, Gogon Centro', 1, 'Senior Citizen', 'Teresa Luna', '09185552346', 'Retired', 2, '2026-01-12 11:30:00', '2026-01-12 11:30:00'),
(3, 1, 'Jose', 'Protacio', 'Rizal', 'Jr.', 'male', '1988-06-19', 'married', '09194441234', 'jose.rizal@example.com', 'Purok 2 - Centro Proper, Gogon Centro', 0, NULL, 'Leonor Rivera', '09194441235', 'Teacher', 2, '2026-01-15 14:00:00', '2026-01-15 14:00:00'),
(4, 1, 'Juana', 'Manalo', 'Dela Cruz', NULL, 'female', '1992-11-03', 'married', '09211234570', 'juana.delacruz@example.com', 'Purok 4 - Near Chapel, Gogon Centro', 1, 'Solo Parent', 'Pedro Dela Cruz', '09219998888', 'Online Seller', 2, '2026-01-20 10:15:00', '2026-01-20 10:15:00'),
(5, 1, 'Eduardo', 'Gomez', 'Silang', NULL, 'male', '2001-02-14', 'single', '09228884321', 'eduardo.silang@example.com', 'Purok 5 - Heights Area, Gogon Centro', 1, 'PWD', 'Gabriela Silang', '09228884322', 'Graphic Artist', 2, '2026-02-01 13:45:00', '2026-02-01 13:45:00'),
(6, 1, 'Grace', 'Torres', 'Mendoza', NULL, 'female', '1998-07-30', 'live_in', '09221234571', 'grace.mendoza@example.com', 'Purok 1 - Riverside, Gogon Centro', 0, NULL, 'Ricardo Mendoza', '09221234572', 'Freelancer', 1, '2026-02-05 16:20:00', '2026-02-05 16:20:00');

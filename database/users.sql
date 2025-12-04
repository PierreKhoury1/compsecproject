-- Create database
CREATE DATABASE IF NOT EXISTS lovejoy;
USE lovejoy;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- USERS TABLE (updated with role column)
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',

  `failed_attempts` INT NOT NULL DEFAULT 0,
  `last_failed_login` DATETIME DEFAULT NULL,

  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- INSERT DEFAULT ADMIN ACCOUNT
-- --------------------------------------------------------
-- Default admin login:
-- Username: Administrator
-- Email: admin@lovejoy.com
-- Password: Admin12345!

INSERT INTO users (name, email, phone, password_hash, role)
VALUES (
    'Administrator',
    'admin@lovejoy.com',
    '0000000000',
    '$2y$10$4xOcOGou1cVEy2vliSzRt.qzR2jiiWbeo4p7.QTYOJbtOvGmlEUFC',
    'admin'
);

-- NOTE:
-- The password above is the bcrypt hash of: AdminPassword123

-- --------------------------------------------------------
-- EVALUATIONS TABLE
-- --------------------------------------------------------

CREATE TABLE `evaluations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `details` TEXT NOT NULL,
  `contact_method` VARCHAR(10) NOT NULL,
  `photo_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),

  PRIMARY KEY (`id`),

  CONSTRAINT `fk_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

CREATE DATABASE IF NOT EXISTS `UserBot` DEFAULT CHARACTER SET utf8;
USE `UserBot`;

DROP TABLE IF EXISTS `Admins`;
CREATE TABLE IF NOT EXISTS `Admins` (
  `id` BIGINT,
  `is_self` BOOLEAN DEFAULT NULL,
  `is_contact` BOOLEAN DEFAULT NULL,
  `is_mutual_contact` BOOLEAN DEFAULT NULL,
  `is_deleted` BOOLEAN DEFAULT NULL,
  `is_bot` BOOLEAN DEFAULT NULL,
  `is_verified` BOOLEAN DEFAULT NULL,
  `is_restricted` BOOLEAN DEFAULT NULL,
  `is_scam` BOOLEAN DEFAULT NULL,
  `is_support` BOOLEAN DEFAULT NULL,
  `first_name` TEXT DEFAULT NULL,
  `last_name` TEXT DEFAULT NULL,
  `username` TEXT DEFAULT NULL,
  `language_code` TEXT DEFAULT NULL,
  `phone_number` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `id` BIGINT,
  `is_self` BOOLEAN DEFAULT NULL,
  `is_contact` BOOLEAN DEFAULT NULL,
  `is_mutual_contact` BOOLEAN DEFAULT NULL,
  `is_deleted` BOOLEAN DEFAULT NULL,
  `is_bot` BOOLEAN DEFAULT NULL,
  `is_verified` BOOLEAN DEFAULT NULL,
  `is_restricted` BOOLEAN DEFAULT NULL,
  `is_scam` BOOLEAN DEFAULT NULL,
  `is_support` BOOLEAN DEFAULT NULL,
  `first_name` TEXT DEFAULT NULL,
  `last_name` TEXT DEFAULT NULL,
  `username` TEXT DEFAULT NULL,
  `language_code` TEXT DEFAULT NULL,
  `phone_number` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

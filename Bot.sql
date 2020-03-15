CREATE DATABASE IF NOT EXISTS `Bot` DEFAULT CHARACTER SET utf8;
USE `Bot`;

DROP TABLE IF EXISTS `Admins`;
CREATE TABLE IF NOT EXISTS `Admins` (
  `id` BIGINT,
  `is_self` BOOLEAN DEFAULT False,
  `is_contact` BOOLEAN DEFAULT False,
  `is_mutual_contact` BOOLEAN DEFAULT False,
  `is_deleted` BOOLEAN DEFAULT False,
  `is_bot` BOOLEAN DEFAULT False,
  `is_verified` BOOLEAN DEFAULT False,
  `is_restricted` BOOLEAN DEFAULT False,
  `is_scam` BOOLEAN DEFAULT False,
  `is_support` BOOLEAN DEFAULT False,
  `first_name` TEXT DEFAULT NULL,
  `last_name` TEXT DEFAULT NULL,
  `username` TEXT UNIQUE DEFAULT NULL,
  `language_code` TEXT DEFAULT NULL,
  `phone_number` TEXT DEFAULT NULL
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `id` BIGINT,
  `is_self` BOOLEAN DEFAULT False,
  `is_contact` BOOLEAN DEFAULT False,
  `is_mutual_contact` BOOLEAN DEFAULT False,
  `is_deleted` BOOLEAN DEFAULT False,
  `is_bot` BOOLEAN DEFAULT False,
  `is_verified` BOOLEAN DEFAULT False,
  `is_restricted` BOOLEAN DEFAULT False,
  `is_scam` BOOLEAN DEFAULT False,
  `is_support` BOOLEAN DEFAULT False,
  `first_name` TEXT DEFAULT NULL,
  `last_name` TEXT DEFAULT NULL,
  `username` TEXT UNIQUE DEFAULT NULL,
  `language_code` TEXT DEFAULT NULL,
  `phone_number` TEXT DEFAULT NULL,,
  `flag` BOOLEAN DEFAULT False,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8;

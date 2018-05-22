-- Adminer 4.6.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `tip_db` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_bin */;
USE `tip_db`;

CREATE TABLE `DB_FILES` (
  `id` varchar(10) COLLATE utf8_bin NOT NULL,
  `location` varchar(1024) COLLATE utf8_bin NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `expire_on` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `DB_RELATIONS` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(50) COLLATE utf8_bin NOT NULL,
  `file_id` varchar(10) COLLATE utf8_bin NOT NULL,
  `code` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `DB_RELATIONS_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `DB_USERS` (`email`),
  CONSTRAINT `DB_RELATIONS_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `DB_FILES` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `DB_USERS` (
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `password` varchar(65) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- 2018-04-30 07:57:30


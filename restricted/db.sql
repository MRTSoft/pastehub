
SET foreign_key_checks = 0;


DROP DATABASE IF EXISTS `tip_db`;
CREATE DATABASE `tip_db` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `tip_db`;

DROP TABLE IF EXISTS `db_files`;
CREATE TABLE `db_files` (
  `id` varchar(10) COLLATE utf8_bin NOT NULL,
  `location` varchar(1024) COLLATE utf8_bin NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expire_on` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `db_files`;

DROP TABLE IF EXISTS `db_keys`;
CREATE TABLE `db_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metadata_id` int(11) NOT NULL,
  `key` varchar(50) COLLATE utf8_bin NOT NULL,
  `value` varchar(5000) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `metadata_id` (`metadata_id`),
  CONSTRAINT `DB_KEYS_METADATA_FK` FOREIGN KEY (`metadata_id`) REFERENCES `db_metadata` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `db_keys`;

DROP TABLE IF EXISTS `db_metadata`;
CREATE TABLE `db_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` varchar(10) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `DB_METADATA_FILES_FK` FOREIGN KEY (`file_id`) REFERENCES `db_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `db_metadata`;

DROP TABLE IF EXISTS `db_relations`;
CREATE TABLE `db_relations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) COLLATE utf8_bin NOT NULL,
  `file_id` varchar(10) COLLATE utf8_bin NOT NULL,
  `code` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_id`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `DB_RELATIONS_FILES_FK` FOREIGN KEY (`file_id`) REFERENCES `db_files` (`id`) ON DELETE CASCADE,
  CONSTRAINT `DB_RELATIONS_USERS_FK` FOREIGN KEY (`user_id`) REFERENCES `db_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `db_relations`;

DROP TABLE IF EXISTS `db_users`;
CREATE TABLE `db_users` (
  `id` varchar(50) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `password` varchar(65) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

TRUNCATE `db_users`;
INSERT INTO `db_users` (`id`, `email`, `name`, `password`) VALUES
('bc1d04b91482886f13f384b3ce7d22bc',  'public@pastehub.com',  'Public', '$2y$10$V80TjDwZzqe427DY5heY3eh9uAJkZHKGQxFqg.D9QcsFhT9QzOmBK');

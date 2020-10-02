-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP VIEW IF EXISTS `apps`;
CREATE TABLE `apps` (`id` int, `name` varchar(100), `client_id` varchar(100), `client_secret` varchar(100), `domain` varchar(100), `callback` varchar(100), `css` varchar(256), `falseLoginLimit` int, `admin` varchar(32), `pubkey` text, `policy` varchar(80), `scope` varchar(128), `jwe` int);


DROP VIEW IF EXISTS `oi_users`;
CREATE TABLE `oi_users` (`id` int, `sub` varchar(384), `nickname` varchar(32), `pswhash` varchar(384), `given_name` varchar(64), `middle_name` varchar(64), `family_name` varchar(64), `mothersname` varchar(64), `email` varchar(80), `email_verified` int, `phone_number` varchar(32), `phone_number_verified` int, `street_address` varchar(64), `locality` varchar(64), `postal_code` varchar(16), `birth_date` varchar(10), `gender` varchar(8), `picture` varchar(255), `profile` varchar(255), `updated_at` int, `created_at` int, `audited` int, `auditor` varchar(128), `audit_time` int, `sysadmin` int, `code` varchar(512), `origname` varchar(128), `signdate` varchar(10));


DROP VIEW IF EXISTS `sessions`;
CREATE TABLE `sessions` (`id` varchar(256), `data` text, `time` datetime);


DROP VIEW IF EXISTS `version`;
CREATE TABLE `version` (`ver` varchar(32));


DROP TABLE IF EXISTS `apps`;
CREATE TABLE `apps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `client_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `client_secret` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `domain` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `callback` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `css` varchar(256) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `falseLoginLimit` int NOT NULL,
  `admin` varchar(32) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `pubkey` text CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `policy` varchar(80) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `scope` varchar(128) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `jwe` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `app_idx_client_id` (`client_id`),
  KEY `app_idx_domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

DROP TABLE IF EXISTS `oi_users`;
CREATE TABLE `oi_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sub` varchar(384) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `nickname` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `pswhash` varchar(384) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `given_name` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `middle_name` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `family_name` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `mothersname` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `email` varchar(80) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `email_verified` int DEFAULT NULL,
  `phone_number` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `phone_number_verified` int DEFAULT NULL,
  `street_address` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `locality` varchar(64) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `postal_code` varchar(16) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `birth_date` varchar(10) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `gender` varchar(8) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `profile` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  `created_at` int DEFAULT NULL,
  `audited` int DEFAULT NULL,
  `auditor` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `audit_time` int DEFAULT NULL,
  `sysadmin` int DEFAULT NULL,
  `code` varchar(512) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `origname` varchar(128) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `signdate` varchar(10) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oi_users_id_ndx` (`id`),
  KEY `oi_users_sub_ndx` (`sub`),
  KEY `oi_users_code_ndx` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(256) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `data` text COLLATE utf8_hungarian_ci,
  `time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `ver` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL,
  KEY `version_ver_ndx` (`ver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

-- 2020-09-29 13:57:57

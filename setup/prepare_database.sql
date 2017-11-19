/*
  Use this script for preparing or resetting the database.
  Just Copy&Paste the whole thing and run it in a SQL window.
*/

--
-- Create database for webchat
--
DROP DATABASE IF EXISTS `aitec_webchat`;
CREATE DATABASE `aitec_webchat` /*!40100 DEFAULT CHARACTER SET utf8 */;

--
-- Create webchat user
--
DROP USER IF EXISTS `CookieMonster`@`localhost`;
CREATE USER `CookieMonster`@`localhost` IDENTIFIED BY 'WrongPassNoCookie!';

--
-- Set user privileges
--
GRANT SELECT, INSERT, UPDATE, DELETE ON aitec_webchat.* TO 'CookieMonster'@'localhost';

--
-- Set current database
--
USE `aitec_webchat`;

--
-- Create tables for webchat
--
CREATE TABLE `webchat_lines` (
  `id`        int(10)        unsigned NOT NULL auto_increment,
  `author`    varchar(16)    NOT NULL,
  `gravatar`  varchar(32)    NOT NULL,
  `text`      varchar(2048)  NOT NULL,
  `ts`        timestamp      NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `webchat_users` (
  `id`            int(10)     unsigned NOT NULL auto_increment,
  `name`          varchar(16) NOT NULL,
  `gravatar`      varchar(32) NOT NULL,
  `last_activity` timestamp   NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `last_activity` (`last_activity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
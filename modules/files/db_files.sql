-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 23. Juni 2010 um 14:52
-- Server Version: 5.1.41
-- PHP-Version: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `netzcraftwerk3`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ncw_files_file`
--

DROP TABLE IF EXISTS `ncw_files_file`;
CREATE TABLE IF NOT EXISTS `ncw_files_file` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` mediumint(8) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `tags` text COLLATE utf8_unicode_ci NOT NULL,
  `size` mediumint(8) unsigned NOT NULL,
  `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ncw_files_filefolder`
--

DROP TABLE IF EXISTS `ncw_files_folder`;
DROP TABLE IF EXISTS `ncw_files_folder`;
CREATE TABLE IF NOT EXISTS `ncw_files_folder` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `ncw_files_filefolder`
--

INSERT INTO `ncw_files_folder` (`id`, `parent_id`, `name`, `created`, `modified`) VALUES
(1, 0, 'root', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

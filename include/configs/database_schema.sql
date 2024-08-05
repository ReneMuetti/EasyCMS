-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 14. Jul 2024 um 05:07
-- Server-Version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP-Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `steinmetz_geith_rebuild`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accounts`
--

CREATE TABLE `accounts` (
  `userid` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `chash` varchar(32) NOT NULL,
  `lastaccess` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `username` varchar(100) NOT NULL,
  `email` varchar(80) NOT NULL,
  `baduser` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `blocks`
--

CREATE TABLE `blocks` (
  `block_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `block_title` varchar(100) NOT NULL DEFAULT '',
  `block_content` text NOT NULL,
  `block_enable` int(1) NOT NULL DEFAULT 1,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE `config` (
  `config_id` int(10) NOT NULL,
  `config_path` varchar(1024) NOT NULL DEFAULT '',
  `config_value` text NOT NULL,
  `config_type` varchar(1024) NOT NULL DEFAULT '',
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `gallery_title` varchar(100) NOT NULL DEFAULT '',
  `gallery_type` int(3) NOT NULL DEFAULT '',
  `gallery_options` text NOT NULL,
  `gallery_images` text NOT NULL,
  `gallery_enable` int(1) NOT NULL DEFAULT 1,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `navigation`
--

CREATE TABLE `navigation` (
  `nav_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `parent` int(10) NOT NULL DEFAULT 0,
  `type` int(1) NOT NULL DEFAULT 0,
  `cms_page` int(10) NOT NULL,
  `link` varchar(255) NOT NULL,
  `enable` int(1) NOT NULL DEFAULT 1,
  `position` int(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pages`
--

CREATE TABLE `pages` (
  `page_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `page_title` varchar(100) NOT NULL DEFAULT '',
  `page_enable` int(1) NOT NULL DEFAULT 1,
  `page_layout` text NOT NULL,
  `page_description` varchar(255) NOT NULL,
  `page_keywords` varchar(255) NOT NULL,
  `is_home` int(1) NOT NULL DEFAULT 0,
  `seo_code` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `secure`
--

CREATE TABLE `secure` (
  `id` int(10) NOT NULL,
  `secure` varchar(20) NOT NULL,
  `hash` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `passhash` varchar(32) NOT NULL,
  `pass` varchar(60) NOT NULL,
  `secret` tinyblob NOT NULL,
  `email` varchar(80) NOT NULL,
  `language` varchar(2) NOT NULL,
  `status` enum('pending','confirmed') NOT NULL,
  `added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(15) NOT NULL,
  `enabled` enum('yes','no') NOT NULL,
  `admin` enum('yes','no') NOT NULL DEFAULT 'no',
  `session` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`userid`);

--
-- Indizes für die Tabelle `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`block_id`);

--
-- Indizes für die Tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`config_id`);

--
-- Indizes für die Tabelle `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`);

--
-- Indizes für die Tabelle `navigation`
--
ALTER TABLE `navigation`
  ADD PRIMARY KEY (`nav_id`);

--
-- Indizes für die Tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `secure`
--
ALTER TABLE `secure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_secure_hash` (`secure`,`hash`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_name` (`username`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `config`
--
ALTER TABLE `config`
  MODIFY `config_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `navigation`
--
ALTER TABLE `navigation`
  MODIFY `nav_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `secure`
--
ALTER TABLE `secure`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

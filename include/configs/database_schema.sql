-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Server-Version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP-Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
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
-- Table structure for table `blocks`
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
-- Table structure for table `chronik`
--

CREATE TABLE `chronik` (
  `chronik_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `chronik_position` int(5) NOT NULL DEFAULT 1,
  `chronik_title` varchar(100) NOT NULL DEFAULT '',
  `chronik_text` text NOT NULL DEFAULT '',
  `chronik_enable` int(1) NOT NULL DEFAULT 1,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
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
-- Data for table `config`
--

INSERT INTO `config` (`config_id`, `config_path`, `config_value`, `config_type`, `datetime`, `username`) VALUES
  (null, 'default/layout/header', '', 'gridster', CURRENT_TIMESTAMP(), 'Install'),
  (null, 'default/layout/footer', '', 'gridster', CURRENT_TIMESTAMP(), 'Install'),
  (null, 'image/thumbnail/size'        , '400'    , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'image/thumbnail/quality'     , '85'     , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'image/thumbnail/prefix_small', 'small_' , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'image/thumbnail/prefix_low'  , 'low_'   , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'design/theme/skin'           , 'default', 'select', CURRENT_TIMESTAMP(), 'Install'),
  (null, 'design/theme/page_width'     , '1200'   , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'design/theme/page_back_dark' , ''       , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'design/theme/page_back_light', ''       , 'input' , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/host'    , '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/username', '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/password', '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/address' , '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/port'    , '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/smtpauth', '', 'boolean', CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/secure'  , '', 'boolean', CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/protocol', '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/sender'  , '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/subject' , '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/rec_mail', '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/rec_name', '', 'input'  , CURRENT_TIMESTAMP(), 'Install'),
  (null, 'system/email/dev_mail', '', 'input'  , CURRENT_TIMESTAMP(), 'Install');

--
-- Table structure for table `gallery`
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
-- Table structure for table `navigation`
--

CREATE TABLE `navigation` (
  `nav_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `item_id` int(10) NOT NULL DEFAULT 1,
  `item_element` varchar(100) NOT NULL DEFAULT '',
  `item_title` varchar(100) NOT NULL DEFAULT '',
  `item_class` varchar(100) NOT NULL DEFAULT '',
  `item_pos` int(5) NOT NULL DEFAULT 1,
  `item_parent` varchar(100) NOT NULL DEFAULT '',
  `item_enable` int(1) NOT NULL DEFAULT 1,
  `item_home` int(1) NOT NULL DEFAULT 1,
  `item_type` int(1) NOT NULL DEFAULT 0,
  `item_cms` int(10) NOT NULL DEFAULT -1,
  `item_url` varchar(1024) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `page_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `page_title` varchar(100) NOT NULL DEFAULT '',
  `page_internal` varchar(100) NOT NULL DEFAULT '',
  `page_enable` int(1) NOT NULL DEFAULT 1,
  `page_layout` text NOT NULL,
  `page_class` varchar(255) NOT NULL,
  `page_description` varchar(255) NOT NULL,
  `page_keywords` varchar(255) NOT NULL,
  `is_home` int(1) NOT NULL DEFAULT 0,
  `seo_code` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secure`
--

CREATE TABLE `secure` (
  `id` int(10) NOT NULL,
  `secure` varchar(20) NOT NULL,
  `hash` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
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

-- --------------------------------------------------------

--
-- Table structure for table `vita`
--

CREATE TABLE `vita` (
  `vita_id` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `vita_title` varchar(100) NOT NULL DEFAULT '',
  `vita_text` varchar(1024) NOT NULL DEFAULT '',
  `vita_image` varchar(1024) NOT NULL DEFAULT '',
  `vita_enable` int(1) NOT NULL DEFAULT 1,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes of the exported tables
--

--
-- Indizes für die Tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`userid`);

--
-- Indexes for the table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`block_id`);

--
-- Indexes for the table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`config_id`);

--
-- Indexes for the table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`);

--
-- Indexes for the table `navigation`
--
ALTER TABLE `navigation`
  ADD PRIMARY KEY (`nav_id`);

--
-- Indexes for the table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `idx_page_internal` (`page_internal`);

--
-- Indexes for the table `secure`
--
ALTER TABLE `secure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_secure_hash` (`secure`,`hash`);

--
-- Indexes for the table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_name` (`username`);

--
-- Indexes for the table `vita`
--
ALTER TABLE `vita`
  ADD PRIMARY KEY (`vita_id`);

--
-- AUTO_INCREMENT for exported tables
--

--
-- AUTO_INCREMENT for the table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `config`
--
ALTER TABLE `config`
  MODIFY `config_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `navigation`
--
ALTER TABLE `navigation`
  MODIFY `nav_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `secure`
--
ALTER TABLE `secure`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for the table `vita`
--
ALTER TABLE `vita`
  MODIFY `vita_id` int(10) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

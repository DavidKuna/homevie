-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Počítač: localhost:3306
-- Vygenerováno: Čtv 27. bře 2014, 17:45
-- Verze serveru: 5.5.36-MariaDB-log
-- Verze PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `homevie`
--
CREATE DATABASE IF NOT EXISTS `homevie` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `homevie`;

-- --------------------------------------------------------

--
-- Struktura tabulky `client`
--

CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT 'Uživatelské jméno klienta',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `room`
--

CREATE TABLE IF NOT EXISTS `room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `room`
--

INSERT INTO `room` (`id`, `name`, `created_at`) VALUES
(1, 'test1', '2014-01-30 19:20:41'),
(2, 'test2', '2014-01-30 19:20:41');

-- --------------------------------------------------------

--
-- Struktura tabulky `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL COMMENT 'token pro vytvoření relace mezi phpsession a websocket',
  `phpsessid` varchar(64) NOT NULL COMMENT 'php sesion_id HTTP klienta',
  `client` int(11) NOT NULL COMMENT 'WebSocket connection id',
  `room_id` int(11) NOT NULL COMMENT 'ID místnosti',
  `owner` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - Owner of room / 0 - listener',
  `ip` varchar(16) DEFAULT NULL COMMENT 'Client IP address',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last modified time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `phpsessid_socketconnid_room_id` (`phpsessid`,`client`,`room_id`),
  UNIQUE KEY `phpsessid_socketconnid` (`phpsessid`,`client`),
  UNIQUE KEY `token` (`token`),
  KEY `client_id` (`phpsessid`,`room_id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

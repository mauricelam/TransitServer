-- phpMyAdmin SQL Dump
-- version 4.0.5
-- http://www.phpmyadmin.net
--
-- Host: engr-cpanel-mysql.engr.illinois.edu
-- Generation Time: Sep 09, 2013 at 06:15 PM
-- Server version: 5.1.69
-- PHP Version: 5.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lam25_mtd`
--

-- --------------------------------------------------------

--
-- Table structure for table `apikey`
--

CREATE TABLE IF NOT EXISTS `apikey` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `description` varchar(63) NOT NULL,
  `turn` smallint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `platforms`
--

CREATE TABLE IF NOT EXISTS `platforms` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `stopid` int(255) unsigned NOT NULL,
  `latitude` int(11) NOT NULL,
  `longitude` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2473 ;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(4) NOT NULL,
  `name` varchar(63) NOT NULL,
  `date` int(4) unsigned NOT NULL,
  `time` int(4) unsigned NOT NULL,
  `schedule` varchar(255) NOT NULL,
  `map` varchar(255) NOT NULL,
  `color` int(9) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stopid` varchar(127) NOT NULL,
  `stopcode` int(4) unsigned zerofill NOT NULL,
  `route` varchar(127) NOT NULL,
  `departure` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=392120 ;

-- --------------------------------------------------------

--
-- Table structure for table `stoproutes`
--

CREATE TABLE IF NOT EXISTS `stoproutes` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `stopcode` int(4) unsigned zerofill NOT NULL,
  `routeid` int(255) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`stopcode`,`routeid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16561 ;

-- --------------------------------------------------------

--
-- Table structure for table `stops`
--

CREATE TABLE IF NOT EXISTS `stops` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` int(4) unsigned zerofill NOT NULL,
  `query` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `latitude` int(11) NOT NULL,
  `longitude` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `query` (`query`),
  UNIQUE KEY `code` (`code`),
  FULLTEXT KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1348 ;

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE IF NOT EXISTS `suggestions` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `stringid` varchar(255) NOT NULL,
  `query` varchar(255) NOT NULL,
  `stopcode` int(4) unsigned zerofill NOT NULL,
  `frequency` int(127) unsigned NOT NULL,
  `stopid` varchar(127) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stringid` (`stringid`),
  FULLTEXT KEY `query` (`query`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9534 ;

-- --------------------------------------------------------

--
-- Table structure for table `watchroutes`
--

CREATE TABLE IF NOT EXISTS `watchroutes` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=134 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

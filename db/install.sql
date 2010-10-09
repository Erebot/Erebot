-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2009 at 12:13 AM
-- Server version: 5.0.67
-- PHP Version: 5.2.6-2ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `erebot`
--

-- --------------------------------------------------------

--
-- Table structure for table `axx_accesses`
--

CREATE TABLE IF NOT EXISTS `axx_accesses` (
  `nck_id` int(10) unsigned NOT NULL,
  `chn_id` int(10) unsigned NULL,
  `axx_level` tinyint(4) NOT NULL,
  PRIMARY KEY  (`nck_id`,`chn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `chn_chans`
--

CREATE TABLE IF NOT EXISTS `chn_chans` (
  `chn_id` int(11) unsigned NOT NULL,
  `net_id` int(11) unsigned NOT NULL,
  `chn_name` varchar(64) NOT NULL,
  PRIMARY KEY  (`chn_id`),
  UNIQUE KEY `net_id` (`net_id`,`chn_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gam_games`
--

CREATE TABLE IF NOT EXISTS `gam_games` (
  `gam_id` int(10) unsigned NOT NULL,
  `gam_name` varchar(32) NOT NULL,
  PRIMARY KEY  (`gam_id`),
  UNIQUE KEY `gam_name` (`gam_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `nck_nicks`
--

CREATE TABLE IF NOT EXISTS `nck_nicks` (
  `nck_id` int(10) unsigned NOT NULL,
  `net_id` int(10) unsigned NOT NULL,
  `nck_nick` varchar(32) character set utf8 collate utf8_bin NOT NULL,
  `nck_password` char(32) NOT NULL,
  PRIMARY KEY  (`nck_id`),
  UNIQUE KEY `nck_nick` (`nck_nick`,`net_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `net_networks`
--

CREATE TABLE IF NOT EXISTS `net_networks` (
  `net_id` int(10) unsigned NOT NULL,
  `net_name` varchar(64) NOT NULL,
  PRIMARY KEY  (`net_id`),
  UNIQUE KEY `net_name` (`net_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sco_scores`
--

CREATE TABLE IF NOT EXISTS `sco_scores` (
  `gam_id` int(10) unsigned NOT NULL,
  `nck_id` int(10) unsigned NOT NULL,
  `sco_score` int(10) unsigned NOT NULL,
  `sco_players` tinyint(3) unsigned NOT NULL,
  `sco_date` datetime NOT NULL,
  PRIMARY KEY  (`gam_id`,`nck_id`,`sco_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `srv_servers`
--

CREATE TABLE IF NOT EXISTS `srv_servers` (
  `net_id` int(10) unsigned NOT NULL,
  `srv_host` varchar(255) character set ascii collate ascii_bin NOT NULL,
  `srv_port` smallint(6) NOT NULL,
  `srv_ssl` enum('F','T') NOT NULL default 'F',
  PRIMARY KEY  (`net_id`,`srv_host`,`srv_port`,`srv_ssl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

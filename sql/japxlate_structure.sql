-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2011 at 06:07 PM
-- Server version: 5.1.57
-- PHP Version: 5.3.8-ZS5.5.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `japxlate`
--

-- --------------------------------------------------------

--
-- Table structure for table `cached_tweets`
--

CREATE TABLE IF NOT EXISTS `cached_tweets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tweet_id` char(20) CHARACTER SET utf8 NOT NULL,
  `tweet_text` varchar(140) CHARACTER SET utf8 NOT NULL,
  `tweet_user` varchar(15) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='@japxlate tweets saved because we might ''lose'' them';

-- --------------------------------------------------------

--
-- Table structure for table `edict`
--

CREATE TABLE IF NOT EXISTS `edict` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kanji` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'orig jap word being defined',
  `kana` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'furigana (in this case hiragana)',
  `definition` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'english def of orig jap word',
  `common` enum('n','y') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='EDICT Japanese to ENglish freeware dictionary (jdic.com)';

-- --------------------------------------------------------

--
-- Table structure for table `handled_tweets`
--

CREATE TABLE IF NOT EXISTS `handled_tweets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tweet_id` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='@japxlate tweets handled (inlcudes ignored tweets)';

-- --------------------------------------------------------

--
-- Table structure for table `sentence`
--

CREATE TABLE IF NOT EXISTS `sentence` (
  `id` int(11) NOT NULL,
  `lang_code` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `sentence` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sentences_per_word`
--

CREATE TABLE IF NOT EXISTS `sentences_per_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kanji` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `kana` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `sentence_id` int(11) NOT NULL,
  `meaning_id` int(11) NOT NULL,
  `checked` enum('y','n') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sentence_breakdown`
--

CREATE TABLE IF NOT EXISTS `sentence_breakdown` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentence_id` int(11) NOT NULL COMMENT 'jap',
  `meaning_id` int(11) NOT NULL COMMENT 'eng',
  `breakdown` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a holding table only?';

-- --------------------------------------------------------

--
-- Table structure for table `sentence_link`
--

CREATE TABLE IF NOT EXISTS `sentence_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentence_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

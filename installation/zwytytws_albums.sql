-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2016 at 02:34 PM
-- Server version: 5.5.53-0+deb8u1
-- PHP Version: 5.6.27-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zwytytws_albums`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE IF NOT EXISTS `albums` (
  `id`          INT(10) UNSIGNED        NOT NULL,
  `title`       VARCHAR(255)
                CHARACTER SET utf8
                COLLATE utf8_unicode_ci NOT NULL,
  `last_played` MEDIUMINT(9)       DEFAULT NULL,
  `hits`        INT(11)            DEFAULT 0,
  `added_on`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `genre`       INT(10) UNSIGNED NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

CREATE TABLE IF NOT EXISTS `artists` (
  `id`   INT(10) UNSIGNED        NOT NULL,
  `name` VARCHAR(255)
         CHARACTER SET utf8
         COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE IF NOT EXISTS `songs` (
  `id`       INT(10) UNSIGNED        NOT NULL,
  `album_id` INT(10) UNSIGNED        NOT NULL,
  `cd`       TINYINT(255) UNSIGNED   NOT NULL DEFAULT 1,
  `track_no` TINYINT(255) UNSIGNED   NOT NULL,
  `length`   TINYINT(255) UNSIGNED   NOT NULL,
  `title`    VARCHAR(255)
             CHARACTER SET utf8
             COLLATE utf8_unicode_ci NOT NULL,
  `url`      VARCHAR(255)
             CHARACTER SET utf8
             COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `song_artists`
--

CREATE TABLE IF NOT EXISTS `song_artists` (
  `song_id`   INT(10) UNSIGNED NOT NULL,
  `artist_id` INT(10) UNSIGNED NOT NULL

)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE IF NOT EXISTS `genres` (
  `id`   INT(10) UNSIGNED        NOT NULL,
  `name` VARCHAR(255)
         CHARACTER SET utf8
         COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `radio_stations`
--

CREATE TABLE IF NOT EXISTS `radio_stations` (
  `id`                 SMALLINT(6) NOT NULL,
  `name`               VARCHAR(50) NOT NULL,
  `url`                TEXT        NOT NULL,
  `cover_cached_token` INT(11)     NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `id`                         INT(11)    NOT NULL,
  `name`                       VARCHAR(25)         DEFAULT NULL,
  `text_color`                 CHAR(7)    NOT NULL,
  `background_color`           CHAR(7)    NOT NULL,
  `background_color_highlight` CHAR(7)    NOT NULL,
  `border_color`               CHAR(7)    NOT NULL,
  `overlays`                   CHAR(7)    NOT NULL,
  `highlight_color`            CHAR(7)    NOT NULL,
  `dark_accents`               TINYINT(1) NOT NULL,
  `read_only`                  TINYINT(1) NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `song_artists`
--
ALTER TABLE `song_artists`
  ADD PRIMARY KEY (`song_id`, `artist_id`);

--
-- Indexes for table `radio_stations`
--
ALTER TABLE `radio_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;
--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;
--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;
--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;
--
-- AUTO_INCREMENT for table `radio_stations`
--
ALTER TABLE `radio_stations`
  MODIFY `id` SMALLINT(6) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;
--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 1;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.4.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 22, 2016 at 05:21 AM
-- Server version: 5.5.47-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.14

-- Populates the theme table with the default themes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zwytytws_albums`
--

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `name`, `text_color`, `background_color`, `background_color_highlight`, `border_color`, `overlays`, `highlight_color`, `dark_accents`, `read_only`)
VALUES
  (1, 'Dark & Blue', '#f5f5f5', '#2a2a2a', '#1e1e1e', '#48485a', '#323232', '#03a9f4', 0, 1),
  (2, 'Dark & Green', '#f5f5f5', '#2a2a2a', '#1e1e1e', '#48485a', '#323232', '#1db954', 0, 1),
  (3, 'Dark & Red', '#F0F0F0', '#141414', '#050505', '#282828', '#141414', '#EB1400', 1, 1),
  (4, 'Deep Red', '#ffffff', '#3C1518', '#69140E', '#D58936', '#4D5061', '#3C1518', 0, 1),
  (5, 'Arctic', '#303030', '#ffffff', '#C4C4C4', '#a8a8a8', '#C4C4C4', '#4c77a9', 0, 1);

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;

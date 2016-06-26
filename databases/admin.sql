-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2016 at 05:30 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `messdetails`
--

CREATE TABLE IF NOT EXISTS `messdetails` (
  `serial` int(3) NOT NULL,
  `password` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` int(1) NOT NULL,
  `detail` varchar(200) NOT NULL,
  `image` varchar(20) NOT NULL,
  `mess` varchar(20) NOT NULL,
  `day` int(2) NOT NULL,
  `members` int(4) NOT NULL,
  `start` date DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `expense` decimal(12,2) NOT NULL,
  `offLimit` int(2) NOT NULL,
  `mode` varchar(1) NOT NULL DEFAULT 'd',
  PRIMARY KEY (`serial`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `serial` int(3) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fpactive` varchar(1) NOT NULL DEFAULT 'n',
  `remember` varchar(1) NOT NULL DEFAULT 'n',
  `upto` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

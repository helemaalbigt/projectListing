-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2014 at 08:53 PM
-- Server version: 5.6.16
-- PHP Version: 5.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `project_listing`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(75) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `usertype` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`, `usertype`) VALUES
('admin', 'b92e4b57bec47afc8c5b5510332a19e02d81ba1b', 'admin'),
('UPadmin', '$2a$10$Bpki2CfxtKng3qwRrvxhEO90Q8ZUXH87KthEwIPqiBZVy3ndWrI7S', 'admin'),
('UPeditor', '$2a$10$hsGs.0iy3PFHjhL.5MGk8uPOlukvVs5R.7Bwe6g9NUghNA.ZLw046', 'editor'),
('UPlogin', '$2a$10$gKVmRD2FcLo5MZiPAKHOUeKx0QXWXV0KsbvqA/1Fda5hfnKRKJJPC', 'contributor'),
('urban', '1dc67c5b55f3a0b8bf6ec36e8ac88f37fa7f6bfe', 'contributor');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

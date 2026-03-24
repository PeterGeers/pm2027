-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 23, 2026 at 09:36 PM
-- Server version: 10.11.16-MariaDB-cll-lve
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pm_registrations`
--
CREATE DATABASE IF NOT EXISTS `pm_registrations` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pm_registrations`;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` date DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `submitted_date` date DEFAULT NULL,
  `submitted_count` int(11) NOT NULL DEFAULT 0,
  `additional_descr` varchar(255) DEFAULT NULL,
  `additional_costs` float NOT NULL DEFAULT 0,
  `min_delegates` int(11) NOT NULL DEFAULT 1,
  `max_delegates` int(11) NOT NULL DEFAULT 0,
  `max_guests` int(11) NOT NULL DEFAULT 0,
  `max_travels` int(11) NOT NULL DEFAULT 0,
  `max_rooms` int(11) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `comments` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_submitted`
--

CREATE TABLE `booking_submitted` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` date DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `submitted_date` date DEFAULT NULL,
  `submitted_count` int(11) NOT NULL DEFAULT 0,
  `additional_descr` varchar(255) DEFAULT NULL,
  `additional_costs` float NOT NULL DEFAULT 0,
  `min_delegates` int(11) NOT NULL,
  `max_delegates` int(11) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `max_travels` int(11) NOT NULL,
  `max_rooms` int(11) NOT NULL,
  `is_locked` tinyint(1) NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `club_name` varchar(255) DEFAULT NULL,
  `club_address` varchar(255) DEFAULT NULL,
  `club_po` varchar(255) DEFAULT NULL,
  `club_zip` varchar(255) DEFAULT NULL,
  `club_city` varchar(255) DEFAULT NULL,
  `club_country` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='club and contaqct details';

-- --------------------------------------------------------

--
-- Table structure for table `contact_submitted`
--

CREATE TABLE `contact_submitted` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `club_name` varchar(255) DEFAULT NULL,
  `club_address` varchar(255) DEFAULT NULL,
  `club_po` varchar(255) DEFAULT NULL,
  `club_zip` varchar(255) DEFAULT NULL,
  `club_city` varchar(255) DEFAULT NULL,
  `club_country` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='club and contaqct details';

-- --------------------------------------------------------

--
-- Table structure for table `delegate`
--

CREATE TABLE `delegate` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `shirt_size` varchar(10) DEFAULT NULL,
  `party` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delegate_submitted`
--

CREATE TABLE `delegate_submitted` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `shirt_size` varchar(10) DEFAULT NULL,
  `party` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fhdce_clubs`
--

CREATE TABLE `fhdce_clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL,
  `cc` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest`
--

CREATE TABLE `guest` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `shirt_size` varchar(10) DEFAULT NULL,
  `party` varchar(5) DEFAULT NULL,
  `city_tour` varchar(5) NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_submitted`
--

CREATE TABLE `guest_submitted` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `shirt_size` varchar(10) DEFAULT NULL,
  `party` varchar(5) DEFAULT NULL,
  `city_tour` varchar(5) NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

CREATE TABLE `hotel_rooms` (
  `id` int(11) NOT NULL,
  `room_no` varchar(25) NOT NULL,
  `type` varchar(25) NOT NULL,
  `location` varchar(25) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `arr_date` date DEFAULT NULL,
  `dep_date` date DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `room_no` varchar(25) DEFAULT NULL,
  `guest1` varchar(255) DEFAULT NULL,
  `guest2` varchar(255) DEFAULT NULL,
  `guest3` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_submitted`
--

CREATE TABLE `room_submitted` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `arr_date` date DEFAULT NULL,
  `dep_date` date DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `room_no` varchar(25) DEFAULT NULL,
  `guest1` varchar(255) DEFAULT NULL,
  `guest2` varchar(255) DEFAULT NULL,
  `guest3` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `travel`
--

CREATE TABLE `travel` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `arr_type` varchar(255) NOT NULL,
  `arr_other` varchar(255) DEFAULT NULL,
  `arr_airport` varchar(255) DEFAULT NULL,
  `arr_airp_other` varchar(255) DEFAULT NULL,
  `arr_flight_no` varchar(25) DEFAULT NULL,
  `arr_date` char(20) DEFAULT NULL,
  `arr_time` char(20) DEFAULT NULL,
  `arr_amount` int(11) DEFAULT NULL,
  `dep_type` varchar(255) DEFAULT NULL,
  `dep_other` varchar(255) DEFAULT NULL,
  `dep_airport` varchar(255) DEFAULT NULL,
  `dep_airp_other` varchar(255) DEFAULT NULL,
  `dep_flight_no` varchar(255) DEFAULT NULL,
  `dep_date` char(20) DEFAULT NULL,
  `dep_time` char(20) DEFAULT NULL,
  `dep_amount` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `travel_submitted`
--

CREATE TABLE `travel_submitted` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `arr_type` varchar(255) NOT NULL,
  `arr_other` varchar(255) DEFAULT NULL,
  `arr_airport` varchar(255) DEFAULT NULL,
  `arr_airp_other` varchar(255) DEFAULT NULL,
  `arr_flight_no` varchar(25) DEFAULT NULL,
  `arr_date` char(20) DEFAULT NULL,
  `arr_time` char(20) DEFAULT NULL,
  `arr_amount` int(11) DEFAULT NULL,
  `dep_type` varchar(255) DEFAULT NULL,
  `dep_other` varchar(255) DEFAULT NULL,
  `dep_airport` varchar(255) DEFAULT NULL,
  `dep_airp_other` varchar(255) DEFAULT NULL,
  `dep_flight_no` varchar(255) DEFAULT NULL,
  `dep_date` char(20) DEFAULT NULL,
  `dep_time` char(20) DEFAULT NULL,
  `dep_amount` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` char(1) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `active` char(1) NOT NULL DEFAULT '0',
  `actcode` char(50) NOT NULL,
  `salt` char(50) NOT NULL,
  `joined` date NOT NULL DEFAULT current_timestamp(),
  `last_active` date DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='logon for siteusers';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_submitted`
--
ALTER TABLE `booking_submitted`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`) USING BTREE;

--
-- Indexes for table `contact_submitted`
--
ALTER TABLE `contact_submitted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `delegate`
--
ALTER TABLE `delegate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `delegate_submitted`
--
ALTER TABLE `delegate_submitted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `fhdce_clubs`
--
ALTER TABLE `fhdce_clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest`
--
ALTER TABLE `guest`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `guest_submitted`
--
ALTER TABLE `guest_submitted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `room_submitted`
--
ALTER TABLE `room_submitted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `travel`
--
ALTER TABLE `travel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `travel_submitted`
--
ALTER TABLE `travel_submitted`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id_ind` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delegate`
--
ALTER TABLE `delegate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest`
--
ALTER TABLE `guest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_submitted`
--
ALTER TABLE `guest_submitted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_submitted`
--
ALTER TABLE `room_submitted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel`
--
ALTER TABLE `travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel_submitted`
--
ALTER TABLE `travel_submitted`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

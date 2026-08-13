-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 13, 2026 at 03:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `abrot`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `fullname`, `email`, `password`, `modified_date`, `created_at`, `is_active`) VALUES
(1, 'Admin Eds', 'abrotcarl@gmail.com', '$2y$10$n64IxCgLzKEvYqtTACNdE.pRMGNATeC/a6tXAypLWf3dljnHeOq3S', NULL, '2026-03-16 06:39:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_history`
--

CREATE TABLE `admin_login_history` (
  `admin_login_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `login_status` enum('success','failed') NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `maps_link` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_history`
--

INSERT INTO `admin_login_history` (`admin_login_id`, `admin_id`, `login_time`, `ip_address`, `user_agent`, `latitude`, `longitude`, `login_status`, `city`, `region`, `country`, `maps_link`, `remarks`) VALUES
(1, 1, '2026-03-16 14:40:23', '::1', 'Mozilla/5.0', '', '', 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(2, 1, '2026-03-21 09:16:45', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(3, 1, '2026-03-25 13:32:59', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(4, 1, '2026-03-26 15:43:13', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(5, 1, '2026-04-05 20:54:22', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(6, 1, '2026-04-12 08:30:00', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(7, 1, '2026-04-15 10:15:00', '::1', 'Mozilla/5.0', NULL, NULL, 'failed', NULL, NULL, NULL, NULL, 'Wrong password'),
(8, 1, '2026-04-20 14:00:00', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(9, 1, '2026-04-26 09:00:00', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(10, 1, '2026-07-17 07:19:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(16, 1, '2026-07-21 19:44:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(17, 1, '2026-07-21 19:46:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(18, 1, '2026-07-21 19:47:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(19, 1, '2026-07-21 19:55:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(20, 1, '2026-07-21 19:56:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(21, 1, '2026-07-21 19:56:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(22, 1, '2026-07-21 19:57:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(23, 1, '2026-07-21 19:58:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(24, 1, '2026-07-21 19:58:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(25, 1, '2026-07-21 19:59:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(26, 1, '2026-07-21 20:08:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(27, 1, '2026-07-21 20:08:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(28, 1, '2026-07-21 20:44:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(29, 1, '2026-07-21 20:44:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(30, 1, '2026-07-21 20:48:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(31, 1, '2026-07-22 17:50:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Guisijan', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(32, 1, '2026-08-03 18:29:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Pasig City', 'Metro Manila', 'Philippines', NULL, 'Password verified, OTP sent'),
(33, 1, '2026-08-11 20:01:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Guisijan', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

-- --------------------------------------------------------

--
-- Table structure for table `admin_otp_history`
--

CREATE TABLE `admin_otp_history` (
  `admin_otp_history_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `purpose` enum('login_verification','forgot_password') NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `status` enum('sent','used','expired','failed') NOT NULL DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_otp_history`
--

INSERT INTO `admin_otp_history` (`admin_otp_history_id`, `admin_id`, `otp_code`, `purpose`, `sent_at`, `expires_at`, `verified_at`, `status`) VALUES
(1, 1, '947510', 'login_verification', '2026-03-16 14:40:17', '2026-03-16 14:50:17', '2026-03-16 14:40:53', 'used'),
(2, 1, '947851', 'login_verification', '2026-03-16 15:47:39', '2026-03-16 15:57:39', '2026-03-16 15:48:11', 'used'),
(3, 1, '332519', 'login_verification', '2026-03-16 16:04:05', '2026-03-16 16:14:05', '2026-03-16 16:04:28', 'used'),
(4, 1, '338652', 'login_verification', '2026-03-20 20:10:53', '2026-03-20 20:20:53', '2026-03-20 20:11:31', 'used'),
(5, 1, '210037', 'login_verification', '2026-03-21 09:16:51', '2026-03-21 09:26:51', '2026-03-21 09:17:37', 'used'),
(6, 1, '775704', 'login_verification', '2026-03-25 13:38:22', '2026-03-25 13:48:22', '2026-03-25 13:38:45', 'used'),
(7, 1, '513499', 'login_verification', '2026-04-05 20:54:17', '2026-04-05 21:04:17', '2026-04-05 20:54:38', 'used'),
(8, 1, '722888', 'login_verification', '2026-07-17 07:17:38', '2026-07-17 07:27:38', NULL, 'sent'),
(9, 1, '549632', 'login_verification', '2026-07-17 07:19:43', '2026-07-17 07:29:43', '2026-07-17 07:20:10', 'used'),
(10, 1, '823311', 'login_verification', '2026-07-21 20:43:57', '2026-07-21 20:53:57', NULL, 'sent'),
(11, 1, '953834', 'login_verification', '2026-07-21 20:44:06', '2026-07-21 20:54:06', NULL, 'failed'),
(12, 1, '575562', 'login_verification', '2026-07-21 20:48:33', '2026-07-21 20:58:33', NULL, 'failed'),
(13, 1, '356117', '', '2026-07-22 17:48:10', '2026-07-22 17:58:10', NULL, 'sent'),
(14, 1, '737493', 'login_verification', '2026-07-22 17:50:09', '2026-07-22 18:00:09', '2026-07-22 17:50:43', 'used'),
(15, 1, '182963', 'login_verification', '2026-08-03 18:29:53', '2026-08-03 18:39:53', '2026-08-03 18:30:13', 'used'),
(16, 1, '522687', 'login_verification', '2026-08-11 20:01:15', '2026-08-11 20:11:15', '2026-08-11 20:02:05', 'used');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `start_date` date NOT NULL COMMENT 'Must be Sat for TDC',
  `total_hours` tinyint(3) NOT NULL DEFAULT 15,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `student_note` text DEFAULT NULL,
  `instructor_note` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `tdc_overall_grade` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Final TDC overall grade 1-100',
  `pdc_overall_rating` tinyint(1) DEFAULT NULL COMMENT 'PDC overall rating 1-5',
  `pdc_overall_grade` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_schedules`
--

CREATE TABLE `course_schedules` (
  `schedule_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL COMMENT 'The start_date of the appointment',
  `booked_count` tinyint(3) NOT NULL DEFAULT 0,
  `student_limit` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Snapshot of limit at booking time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `certificate_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `fullname`, `email`, `mobile`, `bio`, `mobile_number`, `certificate_image`, `password`, `modified_date`, `created_at`) VALUES
(1, 'Instructor Carl', 'abrotcarl@gmail.com', '099539999', 'yjgtug', '09171234567', 'cert_1_1774057601.png', '$2y$10$U.3s1NOQ2CIXYCEuys.L/.rvbyHEETQL3OvWvuapFOFlkwTOLTMzO', '2026-06-15 19:19:25', '2026-03-16 11:08:26'),
(5, 'John Ronalda Sotomila', 'onar.sotomil.ui@phinmaed.com', NULL, NULL, NULL, NULL, '$2y$10$N4k1mSek47U2qxqPJbOX0uPxYG8yrTBRfLWoDCZrC/YA/7I6cWewy', NULL, '2026-07-16 04:46:05'),
(6, 'Ericka Kate Gregorio', 'erickakategregorio@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$y.TmJV73oAcY0nY9QYOlt.yhIeoCAwKOgDTFzT6gxCKmgud.Q3Dnq', NULL, '2026-08-04 06:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_type` enum('TDC Face-to-Face','TDC Online','PDC Car (Manual/Automatic)','PDC Motorcycle (Manual/Automatic)','PDC Both (Car & Motorcycle)') NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `price_per_hr` decimal(10,2) DEFAULT NULL,
  `student_limit` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'Max students per start_date. NULL = unlimited.',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`course_id`, `instructor_id`, `course_type`, `description`, `requirements`, `price_per_hr`, `student_limit`, `is_active`, `created_at`) VALUES
(1, 1, 'TDC Face-to-Face', 'sjbb', 'nndndn', 134.00, 12, 1, '2026-03-22 10:16:48'),
(2, 1, 'TDC Online', '', NULL, 111.00, 1, 1, '2026-03-22 10:53:54'),
(5, 1, 'PDC Car (Manual/Automatic)', '', NULL, 169.00, 3, 1, '2026-05-07 14:14:42'),
(6, 1, 'PDC Motorcycle (Manual/Automatic)', '', NULL, 100.00, 1, 1, '2026-05-07 14:14:58'),
(7, 5, 'TDC Face-to-Face', 'grgr', NULL, 150.00, NULL, 1, '2026-07-16 05:00:21'),
(9, 5, 'TDC Online', '', NULL, 150.00, NULL, 1, '2026-07-16 05:00:54'),
(10, 5, 'PDC Car (Manual/Automatic)', '', NULL, 200.00, NULL, 1, '2026-07-16 05:01:08'),
(11, 5, 'PDC Motorcycle (Manual/Automatic)', 'wddd', NULL, 474.00, NULL, 1, '2026-07-16 05:01:20'),
(15, 1, 'PDC Both (Car & Motorcycle)', 'eee', 'eeee', 33.00, 3, 1, '2026-08-05 13:48:25');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_login_history`
--

CREATE TABLE `instructor_login_history` (
  `instructor_login_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `login_status` enum('success','failed') NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `maps_link` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_login_history`
--

INSERT INTO `instructor_login_history` (`instructor_login_id`, `instructor_id`, `login_time`, `ip_address`, `user_agent`, `latitude`, `longitude`, `login_status`, `city`, `region`, `country`, `maps_link`, `remarks`) VALUES
(1, 1, '2026-03-16 19:13:25', '::1', 'Mozilla/5.0', '10.698909', '122.541855', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698909,122.541855', 'Password verified, OTP sent'),
(2, 1, '2026-03-21 09:40:29', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(3, 1, '2026-03-25 13:52:04', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(4, 1, '2026-04-05 20:39:35', '::1', 'Mozilla/5.0', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(6, 5, '2026-07-16 12:47:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Wrong password'),
(7, 5, '2026-07-16 12:47:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(8, 5, '2026-07-16 12:58:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(9, 1, '2026-07-16 13:57:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Wrong password'),
(10, 1, '2026-07-16 13:58:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Wrong password'),
(11, 1, '2026-07-16 13:59:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(12, 1, '2026-07-16 14:02:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(13, 5, '2026-07-16 14:03:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Liloan', 'Eastern Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(14, 1, '2026-07-21 20:07:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(15, 1, '2026-07-21 21:10:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Wrong password'),
(16, 1, '2026-07-21 21:11:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(17, 1, '2026-08-03 18:33:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Pasig City', 'Metro Manila', 'Philippines', NULL, 'Wrong password'),
(18, 1, '2026-08-03 18:33:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Pasig City', 'Metro Manila', 'Philippines', NULL, 'Wrong password'),
(19, 1, '2026-08-03 18:34:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Pasig City', 'Metro Manila', 'Philippines', NULL, 'Password verified, OTP sent'),
(20, 1, '2026-08-11 20:03:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Guisijan', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_otp_history`
--

CREATE TABLE `instructor_otp_history` (
  `instructor_otp_history_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `purpose` enum('login_verification','forgot_password','password_reset') NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `status` enum('sent','used','expired','failed') NOT NULL DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_otp_history`
--

INSERT INTO `instructor_otp_history` (`instructor_otp_history_id`, `instructor_id`, `otp_code`, `purpose`, `sent_at`, `expires_at`, `verified_at`, `status`) VALUES
(1, 1, '794560', 'password_reset', '2026-03-18 16:40:07', '2026-03-18 16:50:07', '2026-03-18 16:40:29', 'used'),
(2, 1, '634334', 'login_verification', '2026-03-22 16:15:37', '2026-03-22 16:25:37', '2026-03-22 16:15:58', 'used'),
(3, 1, '740867', 'login_verification', '2026-03-25 13:52:04', '2026-03-25 14:02:04', '2026-03-25 13:53:46', 'used'),
(4, 1, '497802', 'login_verification', '2026-04-05 20:39:27', '2026-04-05 20:49:27', '2026-04-05 20:40:39', 'used'),
(6, 5, '870885', 'login_verification', '2026-07-16 12:47:41', '2026-07-16 12:57:41', NULL, 'failed'),
(7, 5, '695530', 'login_verification', '2026-07-16 12:58:15', '2026-07-16 13:08:15', NULL, 'failed'),
(8, 1, '001226', 'password_reset', '2026-07-16 13:58:30', '2026-07-16 14:08:30', '2026-07-16 13:58:52', 'used'),
(9, 1, '673967', 'login_verification', '2026-07-16 13:59:28', '2026-07-16 14:09:28', '2026-07-16 13:59:50', 'used'),
(10, 1, '266464', 'login_verification', '2026-07-16 14:02:37', '2026-07-16 14:12:37', '2026-07-16 14:02:56', 'used'),
(11, 5, '168142', 'login_verification', '2026-07-16 14:03:45', '2026-07-16 14:13:45', '2026-07-16 14:04:31', 'used'),
(12, 1, '439788', 'login_verification', '2026-07-21 20:07:08', '2026-07-21 20:17:08', '2026-07-21 20:07:57', 'used'),
(13, 1, '873974', 'password_reset', '2026-07-21 20:41:30', '2026-07-21 20:51:30', '2026-07-21 20:41:53', 'used'),
(14, 1, '673062', 'login_verification', '2026-07-21 21:10:59', '2026-07-21 21:20:59', '2026-07-21 21:11:22', 'used'),
(15, 1, '724878', 'password_reset', '2026-08-03 18:33:26', '2026-08-03 18:43:26', '2026-08-03 18:33:53', 'used'),
(16, 1, '115650', 'login_verification', '2026-08-03 18:34:30', '2026-08-03 18:44:30', '2026-08-03 18:34:52', 'used'),
(17, 1, '802621', 'login_verification', '2026-08-11 20:03:45', '2026-08-11 20:13:45', '2026-08-11 20:04:04', 'used');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_password_history`
--

CREATE TABLE `instructor_password_history` (
  `password_history_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `change_type` enum('reset') NOT NULL DEFAULT 'reset',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `maps_link` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_password_history`
--

INSERT INTO `instructor_password_history` (`password_history_id`, `instructor_id`, `change_type`, `ip_address`, `user_agent`, `latitude`, `longitude`, `city`, `region`, `country`, `maps_link`, `changed_at`) VALUES
(1, 1, 'reset', '::1', 'Mozilla/5.0', '10.698800', '122.541712', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698800,122.541712', '2026-03-18 08:35:43'),
(2, 1, 'reset', '::1', 'Mozilla/5.0', NULL, NULL, 'Iloilo City', 'Western Visayas', 'Philippines', NULL, '2026-03-22 08:15:15'),
(3, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'Liloan', 'Eastern Visayas', 'Philippines', NULL, '2026-07-16 05:59:07'),
(4, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'Lapu-Lapu City', 'Central Visayas', 'Philippines', NULL, '2026-07-21 12:42:47'),
(5, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', NULL, NULL, 'Pasig City', 'Metro Manila', 'Philippines', NULL, '2026-08-03 10:34:13');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_ratings`
--

CREATE TABLE `instructor_ratings` (
  `rating_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `rated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('student','instructor') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('student','instructor') NOT NULL,
  `appointment_id` int(11) DEFAULT NULL COMMENT 'Optional: link to enrollment context',
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `appointment_id`, `body`, `is_read`, `created_at`) VALUES
(1, 27, 'student', 1, 'instructor', NULL, 'hi', 1, '2026-05-15 13:37:45'),
(2, 1, 'instructor', 27, 'student', NULL, 'op', 1, '2026-05-15 14:04:35'),
(3, 27, 'student', 1, 'instructor', NULL, 'fff', 1, '2026-05-26 12:30:19'),
(4, 1, 'instructor', 27, 'student', NULL, 'gjgg', 1, '2026-05-26 12:38:51'),
(5, 27, 'student', 1, 'instructor', NULL, 'ff', 1, '2026-05-26 12:39:03'),
(6, 1, 'instructor', 27, 'student', NULL, 'fkv', 1, '2026-05-26 13:19:05'),
(7, 27, 'student', 1, 'instructor', NULL, 'mjj', 1, '2026-07-23 10:15:08'),
(8, 27, 'student', 1, 'instructor', NULL, 'bb', 1, '2026-07-23 10:28:51'),
(9, 1, 'instructor', 28, 'student', NULL, 'ndhd', 0, '2026-08-03 13:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','instructor','admin') NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `recipient_target` enum('all_students','all_instructors','everyone','specific_student','specific_instructor') DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `user_id`, `user_type`, `message`, `type`, `recipient_target`, `is_read`, `created_at`) VALUES
(1, 1, 'instructor', 'New appointment request for TDC Face-to-Face starting 2026-03-28 from Carl Edciel.', 'booking_request', NULL, 1, '2026-03-27 03:28:43'),
(2, 27, 'student', 'Your appointment for TDC Face-to-Face has been APPROVED! TDC sessions are on Saturdays and Sundays.', 'appointment_approved', NULL, 1, '2026-03-27 11:28:57'),
(3, 1, 'instructor', 'New appointment request for TDC Face-to-Face starting 2026-04-19 from Kent John Canas.', 'booking_request', NULL, 1, '2026-04-03 10:53:28'),
(4, 28, 'student', 'Your appointment for TDC Face-to-Face has been APPROVED! TDC sessions are on Saturdays and Sundays.', 'appointment_approved', NULL, 1, '2026-04-03 18:54:03'),
(5, 27, 'student', 'Your TDC course is complete! Please rate your instructor.', 'course_completed', NULL, 1, '2026-04-06 20:43:24'),
(6, 27, 'student', 'Your TDC Certificate (TDC-2026-001) has been issued — Face-to-Face release. PDC courses are now unlocked.', 'certificate_issued', NULL, 1, '2026-04-06 20:45:00'),
(7, 1, 'instructor', 'New appointment request for TDC Online starting 2026-05-02 from A student.', 'booking_request', NULL, 0, '2026-04-28 06:27:23'),
(8, 27, 'student', 'Your appointment for TDC Online has been APPROVED!', 'appointment_approved', NULL, 1, '2026-04-29 02:34:54'),
(9, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 1, '2026-05-01 14:46:45'),
(10, 27, 'student', 'Your TDC Certificate has been issued online! PDC is now unlocked.', 'certificate_issued', NULL, 1, '2026-05-01 14:47:39'),
(11, 1, 'instructor', 'New appointment request for PDC Manual (Vehicle: Car) on 2026-05-15 from A student.', 'booking_request', NULL, 0, '2026-05-07 14:19:08'),
(12, 27, 'student', 'Your appointment for PDC Manual has been APPROVED!', 'appointment_approved', NULL, 1, '2026-05-07 14:19:19'),
(13, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 1, '2026-05-07 14:21:46'),
(14, 28, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-05-07 14:24:41'),
(15, 1, 'instructor', 'New appointment request for PDC Manual (Vehicle: Car) on 2026-05-16 from A student.', 'booking_request', NULL, 0, '2026-05-07 14:34:39'),
(16, 27, 'student', 'Your PDC Certificate has been issued online! Congratulations!', 'certificate_issued', NULL, 1, '2026-05-07 14:40:53'),
(17, 1, 'instructor', '💬 Message from your student.', 'message', NULL, 0, '2026-05-15 13:37:45'),
(18, 27, 'student', '📢 Admin: fail kamo tanan', 'announcement', 'all_students', 1, '2026-05-15 14:04:03'),
(19, 28, 'student', '📢 Admin: fail kamo tanan', 'announcement', 'all_students', 0, '2026-05-15 14:04:03'),
(20, 27, 'student', '💬 Message from your instructor.', 'message', NULL, 1, '2026-05-15 14:04:35'),
(21, 1, 'instructor', 'New appointment request for PDC Manual on 2026-05-28 from Carl Edciel.', 'booking_request', NULL, 0, '2026-05-26 08:47:15'),
(22, 1, 'instructor', 'New appointment request for PDC Manual on 2026-05-28 from Carl Edciel.', 'booking_request', NULL, 0, '2026-05-26 12:29:06'),
(23, 27, 'student', '📢 Admin: cdcv', 'announcement', 'all_students', 1, '2026-05-26 12:29:58'),
(24, 28, 'student', '📢 Admin: cdcv', 'announcement', 'all_students', 0, '2026-05-26 12:29:58'),
(25, 1, 'instructor', '💬 Message from your student.', 'message', NULL, 0, '2026-05-26 12:30:19'),
(26, 27, 'student', '💬 Message from your instructor.', 'message', NULL, 0, '2026-05-26 12:38:51'),
(27, 1, 'instructor', '💬 Message from your student.', 'message', NULL, 0, '2026-05-26 12:39:03'),
(28, 27, 'student', '💬 Message from your instructor.', 'message', NULL, 0, '2026-05-26 13:19:05'),
(29, 1, 'instructor', 'New appointment request for PDC Manual on 2026-06-10 from Carl Edciel.', 'booking_request', NULL, 0, '2026-06-09 11:46:36'),
(30, 27, 'student', 'Your appointment for PDC Manual has been APPROVED!', 'appointment_approved', NULL, 0, '2026-06-09 11:46:51'),
(31, 27, 'student', 'Admin has assigned you to Instructor Carl for TDC Face-to-Face starting 2026-06-16. The appointment is already approved.', 'admin_assign', NULL, 0, '2026-06-15 05:07:22'),
(32, 1, 'instructor', 'Admin has assigned student Carl Edciel to your TDC Face-to-Face course starting 2026-06-16.', 'admin_assign', NULL, 0, '2026-06-15 05:07:22'),
(33, 1, 'instructor', 'Carl Edciel cancelled their TDC Face-to-Face appointment that was scheduled for 2026-06-16.', 'student_cancelled', NULL, 0, '2026-06-15 11:09:11'),
(34, 1, 'instructor', 'Carl Edciel cancelled their PDC Manual appointment that was scheduled for 2026-06-10.', 'student_cancelled', NULL, 0, '2026-06-16 10:49:59'),
(35, 1, 'instructor', 'New appointment request for PDC Manual on 2026-06-17 from Carl Edciel.', 'booking_request', NULL, 0, '2026-06-16 10:50:19'),
(36, 27, 'student', 'Your appointment for PDC Manual has been APPROVED!', 'appointment_approved', NULL, 0, '2026-06-16 10:50:39'),
(37, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-07-15 14:12:56'),
(38, 1, 'instructor', 'New appointment request for PDC Manual on 2026-07-16 from Carl Edciel.', 'booking_request', NULL, 0, '2026-07-15 14:24:16'),
(39, 27, 'student', 'Your appointment for PDC Manual has been APPROVED!', 'appointment_approved', NULL, 0, '2026-07-15 14:26:05'),
(40, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-07-15 22:37:29'),
(41, 1, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-18 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 04:01:54'),
(42, 1, 'instructor', 'Carl Edciel cancelled their PDC Manual booking that was scheduled for 2026-07-18.', 'student_cancelled', NULL, 0, '2026-07-16 04:02:23'),
(43, 1, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-21 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 04:02:59'),
(44, 27, 'student', '❌ Your PDC Manual booking on 2026-07-21 was cancelled by Your instructor: hfccyctygh', 'instructor_cancelled', NULL, 0, '2026-07-16 04:03:29'),
(45, 27, 'student', 'Your PDC Certificate (PDC Manual) has been issued online!', 'certificate_issued', NULL, 0, '2026-07-16 04:38:57'),
(46, 5, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-19 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 05:03:00'),
(47, 5, 'instructor', 'Carl Edciel cancelled their PDC Manual booking that was scheduled for 2026-07-19.', 'student_cancelled', NULL, 0, '2026-07-16 05:05:27'),
(48, 5, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-17 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 05:05:50'),
(49, 5, 'instructor', 'Carl Edciel cancelled their PDC Manual booking that was scheduled for 2026-07-17.', 'student_cancelled', NULL, 0, '2026-07-16 05:12:11'),
(50, 5, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-17 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 05:15:53'),
(51, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-07-18 by Carl Edciel. No action required.', 'booking_confirmed', NULL, 0, '2026-07-16 05:41:16'),
(52, 1, 'instructor', 'Admin reassigned student Jhon Lloyd Abangan\'s TDC Face-to-Face appointment to another instructor.', 'admin_reassign', NULL, 0, '2026-07-21 11:05:20'),
(53, 5, 'instructor', 'Admin assigned student Jhon Lloyd Abangan to your TDC Face-to-Face course starting 2026-07-18.', 'admin_reassign', NULL, 0, '2026-07-21 11:05:20'),
(54, 29, 'student', 'Your TDC Face-to-Face appointment has been reassigned to John Ronalda Sotomila, starting 2026-07-18.', 'admin_reassign', NULL, 0, '2026-07-21 11:05:20'),
(55, 29, 'student', 'Admin has assigned you to Instructor Carl for TDC Face-to-Face starting 2026-07-25. The appointment is already approved.', 'admin_assign', NULL, 0, '2026-07-21 11:07:21'),
(56, 1, 'instructor', 'Admin has assigned student Jhon Lloyd Abangan to your TDC Face-to-Face course starting 2026-07-25.', 'admin_assign', NULL, 0, '2026-07-21 11:07:21'),
(57, 1, 'instructor', 'Admin reassigned student Jhon Lloyd Abangan\'s TDC Face-to-Face appointment to another instructor.', 'admin_reassign', NULL, 0, '2026-07-21 11:07:51'),
(58, 5, 'instructor', 'Admin assigned student Jhon Lloyd Abangan to your TDC Face-to-Face course starting 2026-07-25.', 'admin_reassign', NULL, 0, '2026-07-21 11:07:51'),
(59, 29, 'student', 'Your TDC Face-to-Face appointment has been reassigned to John Ronalda Sotomila, starting 2026-07-25.', 'admin_reassign', NULL, 0, '2026-07-21 11:07:51'),
(60, 5, 'instructor', 'Admin reassigned student Carl Edciel\'s PDC Manual appointment to another instructor.', 'admin_reassign', NULL, 0, '2026-07-21 13:08:15'),
(61, 1, 'instructor', 'Admin assigned student Carl Edciel to your PDC Manual course starting 2026-07-17.', 'admin_reassign', NULL, 0, '2026-07-21 13:08:15'),
(62, 27, 'student', 'Your PDC Manual appointment has been reassigned to Instructor Carl, starting 2026-07-17.', 'admin_reassign', NULL, 0, '2026-07-21 13:08:15'),
(63, 27, 'student', '❌ Your PDC Manual booking on 2026-07-17 was cancelled by Your instructor: jdjjd', 'instructor_cancelled', NULL, 0, '2026-07-22 09:53:36'),
(64, 5, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-24 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-07-22 10:37:22'),
(65, 5, 'instructor', 'A student cancelled their PDC Manual booking that was scheduled for 2026-07-24.', 'student_cancelled', NULL, 0, '2026-07-22 10:37:58'),
(66, 1, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-24 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-07-22 10:38:15'),
(67, 1, 'instructor', 'A student cancelled their PDC Manual booking that was scheduled for 2026-07-24.', 'student_cancelled', NULL, 0, '2026-07-23 05:25:35'),
(68, 1, 'instructor', '💬 Message from your student.', 'message', NULL, 0, '2026-07-23 10:15:08'),
(69, 1, 'instructor', '✅ New booking confirmed: PDC Manual on 2026-07-24 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-07-23 10:26:51'),
(70, 1, 'instructor', '💬 Message from your student.', 'message', NULL, 0, '2026-07-23 10:28:51'),
(71, 27, 'student', '📢 Admin: jjif', 'announcement', 'all_students', 0, '2026-07-23 10:34:05'),
(72, 28, 'student', '📢 Admin: jjif', 'announcement', 'all_students', 0, '2026-07-23 10:34:05'),
(73, 29, 'student', '📢 Admin: jjif', 'announcement', 'all_students', 0, '2026-07-23 10:34:05'),
(74, 1, 'instructor', 'Admin reassigned student Carl Edciel\'s PDC Manual appointment to another instructor.', 'admin_reassign', NULL, 0, '2026-07-23 10:46:33'),
(75, 5, 'instructor', 'Admin assigned student Carl Edciel to your PDC Manual course starting 2026-07-24.', 'admin_reassign', NULL, 0, '2026-07-23 10:46:33'),
(76, 27, 'student', 'Your PDC Manual appointment has been reassigned to John Ronalda Sotomila, starting 2026-07-24.', 'admin_reassign', NULL, 0, '2026-07-23 10:46:33'),
(77, 5, 'instructor', 'Admin reassigned student Carl Edciel\'s PDC Manual appointment to another instructor.', 'admin_reassign', NULL, 0, '2026-07-23 10:47:02'),
(78, 1, 'instructor', 'Admin assigned student Carl Edciel to your PDC Manual course starting 2026-07-24.', 'admin_reassign', NULL, 0, '2026-07-23 10:47:02'),
(79, 27, 'student', 'Your PDC Manual appointment has been reassigned to Instructor Carl, starting 2026-07-24.', 'admin_reassign', NULL, 0, '2026-07-23 10:47:02'),
(80, 28, 'student', '💬 Message from your instructor.', 'message', NULL, 0, '2026-08-03 13:03:01'),
(81, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-08 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-04 15:23:18'),
(82, 27, 'student', 'Your PDC Certificate (PDC Car (Manual/Automatic)) has been issued online!', 'certificate_issued', NULL, 0, '2026-08-05 07:09:27'),
(83, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-07 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-05 07:21:25'),
(84, 1, 'instructor', 'A student cancelled their PDC Car (Manual/Automatic) booking that was scheduled for 2026-08-07.', 'student_cancelled', NULL, 0, '2026-08-05 07:23:59'),
(85, 1, 'instructor', '✅ New booking confirmed: PDC Motorcycle (Manual/Automatic) on 2026-08-07 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-05 07:24:16'),
(86, 1, 'instructor', 'A student cancelled their PDC Motorcycle (Manual/Automatic) booking that was scheduled for 2026-08-07.', 'student_cancelled', NULL, 0, '2026-08-05 07:24:24'),
(87, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-07 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-05 07:24:43'),
(88, 1, 'instructor', 'A student cancelled their PDC Car (Manual/Automatic) booking that was scheduled for 2026-08-07.', 'student_cancelled', NULL, 0, '2026-08-05 07:25:24'),
(89, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-05 13:29:13'),
(90, 1, 'instructor', 'A student cancelled their PDC Car (Manual/Automatic) booking that was scheduled for 2026-08-11.', 'student_cancelled', NULL, 0, '2026-08-05 13:43:46'),
(91, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-05 17:46:13'),
(92, 1, 'instructor', 'A student cancelled their PDC Both (Car & Motorcycle) booking that was scheduled for 2026-08-11.', 'student_cancelled', NULL, 0, '2026-08-07 06:37:07'),
(93, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-16 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 06:39:02'),
(94, 1, 'instructor', 'A student cancelled their PDC Car (Manual/Automatic) booking that was scheduled for 2026-08-16.', 'student_cancelled', NULL, 0, '2026-08-07 06:42:09'),
(95, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 06:58:58'),
(96, 1, 'instructor', 'A student cancelled their PDC Car (Manual/Automatic) booking that was scheduled for 2026-08-15.', 'student_cancelled', NULL, 0, '2026-08-07 06:59:05'),
(97, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-17 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 07:23:15'),
(98, 1, 'instructor', 'A student cancelled their PDC Both (Car & Motorcycle) booking that was scheduled for 2026-08-17.', 'student_cancelled', NULL, 0, '2026-08-07 07:23:25'),
(99, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-17 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 07:27:23'),
(100, 1, 'instructor', 'A student cancelled their PDC Both (Car & Motorcycle) booking that was scheduled for 2026-08-17.', 'student_cancelled', NULL, 0, '2026-08-07 07:27:27'),
(101, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 07:41:41'),
(102, 1, 'instructor', 'A student cancelled their PDC Both (Car & Motorcycle) booking that was scheduled for 2026-08-11.', 'student_cancelled', NULL, 0, '2026-08-07 07:42:45'),
(103, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 07:49:58'),
(104, 1, 'instructor', 'An admin updated your TDC Face-to-Face course — price is now ₱134.00, student limit is now 12/date.', 'admin_course_edit', NULL, 0, '2026-08-07 08:11:25'),
(105, 1, 'instructor', 'An admin updated your TDC Face-to-Face course — description was updated.', 'admin_course_edit', NULL, 0, '2026-08-07 08:24:58'),
(106, 1, 'instructor', 'An admin updated your TDC Face-to-Face course — description was updated, requirements were updated.', 'admin_course_edit', NULL, 0, '2026-08-07 08:25:25'),
(107, 30, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 08:58:36'),
(108, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 08:59:15'),
(109, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:03:15'),
(110, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:13:42'),
(111, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:17:02'),
(112, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:17:33'),
(113, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:25:40'),
(114, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:26:32'),
(115, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-11 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:27:28'),
(116, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:27:39'),
(117, 1, 'instructor', '✅ New booking confirmed: PDC Motorcycle (Manual/Automatic) on 2026-08-25 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:28:47'),
(118, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:28:59'),
(119, 1, 'instructor', '✅ New booking confirmed: PDC Car (Manual/Automatic) on 2026-08-27 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 09:30:03'),
(120, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-07 09:30:15'),
(121, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 11:27:23'),
(122, 1, 'instructor', 'A student cancelled their PDC Both (Car & Motorcycle) booking that was scheduled for 2026-08-18.', 'student_cancelled', NULL, 0, '2026-08-07 11:30:01'),
(123, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-12 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-07 14:17:02'),
(124, 27, 'student', '📢 Admin: hbuxshbhj', 'announcement', 'all_students', 0, '2026-08-11 12:17:05'),
(125, 28, 'student', '📢 Admin: hbuxshbhj', 'announcement', 'all_students', 0, '2026-08-11 12:17:05'),
(126, 29, 'student', '📢 Admin: hbuxshbhj', 'announcement', 'all_students', 0, '2026-08-11 12:17:05'),
(127, 30, 'student', '📢 Admin: hbuxshbhj', 'announcement', 'all_students', 0, '2026-08-11 12:17:05'),
(128, 1, 'instructor', '📢 Admin: bhjBb', 'announcement', 'all_instructors', 0, '2026-08-11 12:17:21'),
(129, 6, 'instructor', '📢 Admin: bhjBb', 'announcement', 'all_instructors', 0, '2026-08-11 12:17:21'),
(130, 5, 'instructor', '📢 Admin: bhjBb', 'announcement', 'all_instructors', 0, '2026-08-11 12:17:21'),
(131, 1, 'instructor', '📢 Admin: bghbh', 'announcement', 'specific_instructor', 0, '2026-08-11 12:18:01'),
(132, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-11 12:33:31'),
(133, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-11 12:34:13'),
(134, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-11 12:45:46'),
(135, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-11 12:46:46'),
(136, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-11 17:29:50'),
(137, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-11 17:36:36'),
(138, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-11 17:38:17'),
(139, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 03:22:27'),
(140, 27, 'student', 'Your TDC session was logged, but you still need more hours to complete the 15-hour requirement. Please book another TDC package to finish.', 'course_completed', NULL, 0, '2026-08-12 03:23:22'),
(141, 27, 'student', 'An admin corrected your TDC Face-to-Face session record.', 'admin_session_edit', NULL, 0, '2026-08-12 03:28:54'),
(142, 27, 'student', 'An admin corrected your TDC Face-to-Face session record.', 'admin_session_edit', NULL, 0, '2026-08-12 03:29:58'),
(143, 27, 'student', '📢 Admin: bobo', 'announcement', 'specific_student', 0, '2026-08-12 03:36:43'),
(144, 1, 'instructor', '📢 Admin: palo', 'announcement', 'specific_instructor', 0, '2026-08-12 03:37:28'),
(145, 27, 'student', 'An admin corrected your TDC Face-to-Face session record.', 'admin_session_edit', NULL, 0, '2026-08-12 03:39:51'),
(146, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 03:42:17'),
(147, 27, 'student', 'An admin corrected your TDC Face-to-Face session record.', 'admin_session_edit', NULL, 0, '2026-08-12 03:52:16'),
(148, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 03:52:42'),
(149, 27, 'student', 'Your TDC session was logged, but you still need more hours to complete the 15-hour requirement. Please book another TDC package to finish.', 'course_completed', NULL, 0, '2026-08-12 03:52:55'),
(150, 1, 'instructor', '✅ New booking confirmed: TDC Face-to-Face on 2026-08-15 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 03:53:30'),
(151, 27, 'student', 'Your TDC course is complete! Check your grade in My Progress.', 'course_completed', NULL, 0, '2026-08-12 03:54:02'),
(152, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 03:58:15'),
(153, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 03:58:33'),
(154, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:00:19'),
(155, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:02:18'),
(156, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:14:43'),
(157, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:15:00'),
(158, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:16:17'),
(159, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:16:32'),
(160, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:19:31'),
(161, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:19:46'),
(162, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-18 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:25:41'),
(163, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:26:41'),
(164, 1, 'instructor', '✅ New booking confirmed: PDC Both (Car & Motorcycle) on 2026-08-25 by A student. No action required.', 'booking_confirmed', NULL, 0, '2026-08-12 04:30:10'),
(165, 27, 'student', 'Your PDC course is complete! Please rate your instructor.', 'course_completed', NULL, 0, '2026-08-12 04:30:25'),
(166, 27, 'student', '📢 Admin: update you profile', 'announcement', 'specific_student', 0, '2026-08-12 04:34:55');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_certificates`
--

CREATE TABLE `pdc_certificates` (
  `cert_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `cert_number` varchar(50) DEFAULT NULL,
  `delivery_method` enum('online','face_to_face') DEFAULT 'face_to_face',
  `cert_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `session_type` enum('TDC','PDC') NOT NULL DEFAULT 'TDC',
  `session_number` tinyint(3) NOT NULL DEFAULT 1,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `session_date` date NOT NULL COMMENT 'For TDC must be Sat or Sun',
  `hours_covered` decimal(4,1) NOT NULL DEFAULT 1.0,
  `pdc_car_hours` decimal(4,1) DEFAULT NULL,
  `pdc_moto_hours` decimal(4,1) DEFAULT NULL,
  `status` enum('completed','no_show','pending') DEFAULT 'pending',
  `performance_rating` tinyint(1) DEFAULT NULL COMMENT '1-5 stars per session by instructor',
  `grade` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '1-100 numeric grade for TDC sessions',
  `improvements` text DEFAULT NULL COMMENT 'Suggested improvements by instructor',
  `performance_notes` text DEFAULT NULL COMMENT 'Feedback and suggestions per session',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `fb_link` varchar(255) DEFAULT NULL,
  `id_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `fullname`, `email`, `mobile`, `address`, `password_hash`, `is_verified`, `created_at`, `gender`, `fb_link`, `id_photo`, `is_active`) VALUES
(27, 'Carl Edciel', 'cara.abrot.ui@phinmaed.com', '09957320690', 'sto nino sur', '$2y$10$oPghc2jQf1tQg3hhJ175COR2KXlksZAxQUxARJC3EPtUmSIjGr.12', 1, '2026-03-22 06:46:56', 'Male', '', 'sid_27_1778206183.jpg', 1),
(28, 'Kent John Canas', 'canaskentjohn@gmail.com', NULL, NULL, '$2y$10$vXjVMSjdtietwHh1CBM3JePsBtopD1X0NRof4bHRW8miMYAfLh6KG', 1, '2026-03-27 02:07:36', NULL, NULL, NULL, 1),
(29, 'Jhon Lloyd Abangan', 'abrotcarl@gmail.com', NULL, NULL, '$2y$10$MnC5eqoPpx3TMO8CoGstJObmClmwkApf.Ffz0pKvInl5CXx.wJX2C', 1, '2026-07-16 05:30:42', NULL, NULL, NULL, 1),
(30, 'Carina Rasco', 'carinaabrot17@gmail.com', NULL, NULL, '$2y$10$u8FUZHAMv0tTMSpqjXgTq.E5aCeKWIMUVsiI4XjKoazTcP8mLvHxK', 1, '2026-08-04 06:18:14', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_lockout_history`
--

CREATE TABLE `student_lockout_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device_info` text NOT NULL,
  `lockout_type` enum('1min','1hr') NOT NULL,
  `lockout_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_login_history`
--

CREATE TABLE `student_login_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `device_info` text NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `login_datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_login_history`
--

INSERT INTO `student_login_history` (`id`, `student_id`, `ip_address`, `device_info`, `status`, `login_datetime`) VALUES
(1, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 145.0.0.0', 'success', '2026-03-22 14:50:04'),
(2, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-22 16:18:45'),
(3, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-25 14:09:38'),
(4, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 15:45:06'),
(5, 28, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-27 10:13:25'),
(6, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-04-05 20:42:26'),
(7, 29, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-07-16 13:34:13'),
(8, 29, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-07-16 13:40:11'),
(11, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-07-16 14:12:02'),
(17, 29, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-07-21 20:53:26'),
(18, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-07-22 12:27:07'),
(19, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 150.0.0.0', 'success', '2026-08-03 18:23:58'),
(21, 30, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 150.0.0.0', 'success', '2026-08-04 23:22:15'),
(22, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 150.0.0.0', 'success', '2026-08-04 23:29:20'),
(23, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 150.0.0.0', 'success', '2026-08-11 20:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `student_otp_history`
--

CREATE TABLE `student_otp_history` (
  `otp_history_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `otp_type` enum('signup','login','password_reset') NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_otp_history`
--

INSERT INTO `student_otp_history` (`otp_history_id`, `student_id`, `otp`, `otp_type`, `otp_expiry`, `is_used`, `created_at`) VALUES
(1, 27, '684753', 'signup', '2026-03-22 14:56:56', 1, '2026-03-22 06:46:56'),
(2, 27, '345846', 'login', '2026-03-22 14:59:34', 1, '2026-03-22 06:49:34'),
(3, 27, '064477', 'login', '2026-03-25 14:17:25', 1, '2026-03-25 06:07:25'),
(4, 27, '848765', 'login', '2026-03-26 21:55:10', 1, '2026-03-26 13:45:10'),
(5, 28, '786312', 'signup', '2026-03-27 10:17:36', 1, '2026-03-27 02:07:36'),
(6, 28, '209342', 'login', '2026-03-27 10:22:54', 1, '2026-03-27 02:12:54'),
(7, 27, '365249', 'login', '2026-04-05 20:51:52', 1, '2026-04-05 12:41:52'),
(8, 29, '576280', 'signup', '2026-07-16 13:40:42', 1, '2026-07-16 05:30:42'),
(9, 29, '048080', 'login', '2026-07-16 13:43:25', 1, '2026-07-16 05:33:25'),
(10, 29, '578092', 'login', '2026-07-16 13:43:32', 1, '2026-07-16 05:33:32'),
(11, 29, '367027', 'login', '2026-07-16 13:43:40', 1, '2026-07-16 05:33:40'),
(12, 29, '924120', 'login', '2026-07-16 13:49:34', 1, '2026-07-16 05:39:34'),
(13, 27, '587017', 'login', '2026-07-16 14:20:28', 1, '2026-07-16 06:10:28'),
(14, 27, '776218', 'password_reset', '2026-07-21 20:19:59', 1, '2026-07-21 12:09:59'),
(15, 27, '496965', 'password_reset', '2026-07-21 20:31:04', 1, '2026-07-21 12:21:04'),
(16, 27, '358727', 'password_reset', '2026-07-21 20:32:53', 1, '2026-07-21 12:22:53'),
(17, 27, '786666', 'password_reset', '2026-07-21 20:35:32', 1, '2026-07-21 12:25:32'),
(18, 27, '170558', 'password_reset', '2026-07-21 20:38:02', 1, '2026-07-21 12:28:02'),
(19, 27, '646258', 'password_reset', '2026-07-21 20:49:30', 1, '2026-07-21 12:39:30'),
(20, 29, '112481', 'login', '2026-07-21 21:03:04', 1, '2026-07-21 12:53:04'),
(21, 27, '613670', 'password_reset', '2026-07-22 12:33:00', 1, '2026-07-22 04:23:00'),
(22, 27, '923024', 'login', '2026-07-22 12:36:40', 1, '2026-07-22 04:26:40'),
(23, 27, '491327', 'login', '2026-08-03 18:31:02', 1, '2026-08-03 10:21:02'),
(24, 27, '930699', 'login', '2026-08-03 18:31:08', 1, '2026-08-03 10:21:08'),
(25, 30, '258320', 'login', '2026-08-04 23:31:08', 1, '2026-08-04 15:21:08'),
(26, 27, '803472', 'login', '2026-08-04 23:38:19', 1, '2026-08-04 15:28:19'),
(27, 27, '831006', 'login', '2026-08-11 20:17:27', 1, '2026-08-11 12:07:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_password_history`
--

CREATE TABLE `student_password_history` (
  `history_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `change_type` enum('reset','change') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_password_history`
--

INSERT INTO `student_password_history` (`history_id`, `student_id`, `change_type`, `ip_address`, `device_info`, `changed_at`) VALUES
(1, 27, 'reset', '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', '2026-07-22 04:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `tdc_certificates`
--

CREATE TABLE `tdc_certificates` (
  `cert_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  `cert_number` varchar(50) DEFAULT NULL,
  `delivery_method` enum('online','face_to_face') NOT NULL DEFAULT 'face_to_face',
  `cert_image` varchar(255) DEFAULT NULL COMMENT 'Uploaded certificate image for online delivery',
  `cert_type` enum('online','face_to_face') NOT NULL DEFAULT 'face_to_face'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD PRIMARY KEY (`admin_login_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_otp_history`
--
ALTER TABLE `admin_otp_history`
  ADD PRIMARY KEY (`admin_otp_history_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `fk_appt_student` (`student_id`),
  ADD KEY `fk_appt_instructor` (`instructor_id`),
  ADD KEY `fk_appt_course` (`course_id`);

--
-- Indexes for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_course_date` (`course_id`,`schedule_date`),
  ADD KEY `fk_cs_course` (`course_id`),
  ADD KEY `fk_cs_instructor` (`instructor_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `fk_ic_instructor` (`instructor_id`);

--
-- Indexes for table `instructor_login_history`
--
ALTER TABLE `instructor_login_history`
  ADD PRIMARY KEY (`instructor_login_id`),
  ADD KEY `fk_instructor_login_history_instructor` (`instructor_id`);

--
-- Indexes for table `instructor_otp_history`
--
ALTER TABLE `instructor_otp_history`
  ADD PRIMARY KEY (`instructor_otp_history_id`),
  ADD KEY `fk_instructor_otp_history_instructor` (`instructor_id`);

--
-- Indexes for table `instructor_password_history`
--
ALTER TABLE `instructor_password_history`
  ADD PRIMARY KEY (`password_history_id`),
  ADD KEY `fk_instructor_password_history` (`instructor_id`);

--
-- Indexes for table `instructor_ratings`
--
ALTER TABLE `instructor_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_instr_rating` (`appointment_id`,`student_id`),
  ADD KEY `fk_ir_student` (`student_id`),
  ADD KEY `fk_ir_instructor` (`instructor_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_msg_receiver` (`receiver_id`,`receiver_type`),
  ADD KEY `idx_msg_sender` (`sender_id`,`sender_type`),
  ADD KEY `fk_msg_appt` (`appointment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `idx_notif_user` (`user_id`,`user_type`);

--
-- Indexes for table `pdc_certificates`
--
ALTER TABLE `pdc_certificates`
  ADD PRIMARY KEY (`cert_id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `fk_sess_appointment` (`appointment_id`),
  ADD KEY `fk_sess_student` (`student_id`),
  ADD KEY `fk_sess_instructor` (`instructor_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_lockout_history`
--
ALTER TABLE `student_lockout_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_login_history`
--
ALTER TABLE `student_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_otp_history`
--
ALTER TABLE `student_otp_history`
  ADD PRIMARY KEY (`otp_history_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_password_history`
--
ALTER TABLE `student_password_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_student_password_history_student` (`student_id`);

--
-- Indexes for table `tdc_certificates`
--
ALTER TABLE `tdc_certificates`
  ADD PRIMARY KEY (`cert_id`),
  ADD UNIQUE KEY `unique_student_cert` (`student_id`,`appointment_id`),
  ADD KEY `fk_cert_appointment` (`appointment_id`),
  ADD KEY `fk_cert_instructor` (`issued_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  MODIFY `admin_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `admin_otp_history`
--
ALTER TABLE `admin_otp_history`
  MODIFY `admin_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `course_schedules`
--
ALTER TABLE `course_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `instructor_login_history`
--
ALTER TABLE `instructor_login_history`
  MODIFY `instructor_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `instructor_otp_history`
--
ALTER TABLE `instructor_otp_history`
  MODIFY `instructor_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `instructor_password_history`
--
ALTER TABLE `instructor_password_history`
  MODIFY `password_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `instructor_ratings`
--
ALTER TABLE `instructor_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `pdc_certificates`
--
ALTER TABLE `pdc_certificates`
  MODIFY `cert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `student_lockout_history`
--
ALTER TABLE `student_lockout_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_login_history`
--
ALTER TABLE `student_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `student_otp_history`
--
ALTER TABLE `student_otp_history`
  MODIFY `otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `student_password_history`
--
ALTER TABLE `student_password_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tdc_certificates`
--
ALTER TABLE `tdc_certificates`
  MODIFY `cert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  ADD CONSTRAINT `admin_login_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_otp_history`
--
ALTER TABLE `admin_otp_history`
  ADD CONSTRAINT `admin_otp_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_course` FOREIGN KEY (`course_id`) REFERENCES `instructor_courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD CONSTRAINT `fk_cs_course` FOREIGN KEY (`course_id`) REFERENCES `instructor_courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cs_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD CONSTRAINT `fk_ic_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_login_history`
--
ALTER TABLE `instructor_login_history`
  ADD CONSTRAINT `fk_instructor_login_history_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructor_otp_history`
--
ALTER TABLE `instructor_otp_history`
  ADD CONSTRAINT `fk_instructor_otp_history_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructor_password_history`
--
ALTER TABLE `instructor_password_history`
  ADD CONSTRAINT `fk_instructor_password_history` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructor_ratings`
--
ALTER TABLE `instructor_ratings`
  ADD CONSTRAINT `fk_ir_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ir_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ir_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL;

--
-- Constraints for table `pdc_certificates`
--
ALTER TABLE `pdc_certificates`
  ADD CONSTRAINT `pdc_certificates_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pdc_certificates_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pdc_certificates_ibfk_3` FOREIGN KEY (`issued_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sess_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sess_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sess_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_lockout_history`
--
ALTER TABLE `student_lockout_history`
  ADD CONSTRAINT `student_lockout_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_login_history`
--
ALTER TABLE `student_login_history`
  ADD CONSTRAINT `student_login_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_otp_history`
--
ALTER TABLE `student_otp_history`
  ADD CONSTRAINT `fk_student_otp_history_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_password_history`
--
ALTER TABLE `student_password_history`
  ADD CONSTRAINT `fk_student_password_history_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tdc_certificates`
--
ALTER TABLE `tdc_certificates`
  ADD CONSTRAINT `fk_cert_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cert_instructor` FOREIGN KEY (`issued_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cert_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

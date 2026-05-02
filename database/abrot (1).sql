-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 05, 2026 at 04:52 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `fullname`, `email`, `password`, `modified_date`, `created_at`) VALUES
(1, 'Admin Eds', 'abrotcarl@gmail.com', '$2y$10$Ut9KPGy2IHBiyOC5mctdZeq5F09r4G92bjVVYLnj/qc1jbuWzMz6e', NULL, '2026-03-16 06:39:38');

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
(1, 1, '2026-03-16 14:40:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '', '', 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(2, 1, '2026-03-16 15:47:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '', '', 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(3, 1, '2026-03-16 16:04:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '', '', 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(4, 1, '2026-03-16 16:53:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698937043694807', '122.54189357083308', 'failed', NULL, NULL, NULL, NULL, 'Wrong password'),
(5, 1, '2026-03-16 17:03:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698937043694807', '122.54189357083308', 'failed', NULL, NULL, NULL, NULL, 'Wrong password'),
(6, 1, '2026-03-16 17:04:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698915137828076', '122.54185208090088', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698915137828076,122.54185208090088', 'Wrong password'),
(7, 1, '2026-03-16 17:06:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698810947825486', '122.54171261125853', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698810947825486,122.54171261125853', 'Wrong password'),
(8, 1, '2026-03-16 17:08:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698810947825486', '122.54171261125853', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698810947825486,122.54171261125853', 'Password verified, OTP sent'),
(9, 1, '2026-03-16 18:27:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.6994', '122.5659', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.6994,122.5659', 'Wrong password'),
(10, 1, '2026-03-16 18:31:06', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698804784622217', '122.54171197140751', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698804784622217,122.54171197140751', 'Password verified, OTP sent'),
(11, 1, '2026-03-16 18:38:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698802905164754', '122.54170747190821', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698802905164754,122.54170747190821', 'Password verified, OTP sent'),
(12, 1, '2026-03-16 18:39:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698794020999042', '122.54169314964754', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698794020999042,122.54169314964754', 'Password verified, OTP sent'),
(13, 1, '2026-03-20 20:10:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(14, 1, '2026-03-20 22:27:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(15, 1, '2026-03-20 22:27:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(16, 1, '2026-03-21 09:16:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(17, 1, '2026-03-21 09:16:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(18, 1, '2026-03-21 09:16:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(19, 1, '2026-03-21 09:21:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(20, 1, '2026-03-21 10:19:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(21, 1, '2026-03-21 10:19:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(22, 1, '2026-03-21 10:20:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(23, 1, '2026-03-21 10:20:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(24, 1, '2026-03-21 10:20:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(25, 1, '2026-03-22 22:35:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(26, 1, '2026-03-25 13:32:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(27, 1, '2026-03-25 13:33:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(28, 1, '2026-03-25 13:33:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(29, 1, '2026-03-25 13:38:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(30, 1, '2026-03-25 21:51:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(31, 1, '2026-03-26 15:43:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(32, 1, '2026-03-26 16:54:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(33, 1, '2026-03-26 21:44:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(34, 1, '2026-03-27 10:15:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(35, 1, '2026-04-05 20:54:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

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
(4, 1, '461017', 'login_verification', '2026-03-16 17:08:47', '2026-03-16 17:18:47', '2026-03-16 17:09:08', 'used'),
(5, 1, '936643', 'login_verification', '2026-03-16 18:31:00', '2026-03-16 18:41:00', NULL, 'sent'),
(6, 1, '083155', 'login_verification', '2026-03-16 18:38:03', '2026-03-16 18:48:03', NULL, 'sent'),
(7, 1, '619823', 'login_verification', '2026-03-16 18:39:23', '2026-03-16 18:49:23', NULL, 'sent'),
(8, 1, '338652', 'login_verification', '2026-03-20 20:10:53', '2026-03-20 20:20:53', '2026-03-20 20:11:31', 'used'),
(9, 1, '823058', 'login_verification', '2026-03-21 09:16:38', '2026-03-21 09:26:38', NULL, 'sent'),
(10, 1, '863816', 'login_verification', '2026-03-21 09:16:45', '2026-03-21 09:26:45', NULL, 'sent'),
(11, 1, '210037', 'login_verification', '2026-03-21 09:16:51', '2026-03-21 09:26:51', '2026-03-21 09:17:37', 'used'),
(12, 1, '070478', 'login_verification', '2026-03-21 09:21:21', '2026-03-21 09:31:21', '2026-03-21 09:21:44', 'used'),
(13, 1, '486669', 'login_verification', '2026-03-22 22:35:35', '2026-03-22 22:45:35', '2026-03-22 22:35:57', 'used'),
(14, 1, '334551', 'login_verification', '2026-03-25 13:32:53', '2026-03-25 13:42:53', NULL, 'sent'),
(15, 1, '771896', 'login_verification', '2026-03-25 13:32:59', '2026-03-25 13:42:59', NULL, 'sent'),
(16, 1, '666853', 'login_verification', '2026-03-25 13:33:05', '2026-03-25 13:43:05', NULL, 'failed'),
(17, 1, '775704', 'login_verification', '2026-03-25 13:38:22', '2026-03-25 13:48:22', '2026-03-25 13:38:45', 'used'),
(18, 1, '786891', 'login_verification', '2026-03-25 21:51:22', '2026-03-25 22:01:22', '2026-03-25 21:51:46', 'used'),
(19, 1, '566309', 'login_verification', '2026-03-26 15:43:07', '2026-03-26 15:53:07', '2026-03-26 15:43:29', 'used'),
(20, 1, '137557', 'login_verification', '2026-03-26 16:54:32', '2026-03-26 17:04:32', '2026-03-26 16:54:51', 'used'),
(21, 1, '875226', 'login_verification', '2026-03-26 21:44:09', '2026-03-26 21:54:09', '2026-03-26 21:44:26', 'used'),
(22, 1, '824561', 'login_verification', '2026-03-27 10:15:10', '2026-03-27 10:25:10', '2026-03-27 10:15:36', 'used'),
(23, 1, '513499', 'login_verification', '2026-04-05 20:54:17', '2026-04-05 21:04:17', '2026-04-05 20:54:38', 'used');

-- --------------------------------------------------------

--
-- Table structure for table `course_schedules`
--

CREATE TABLE `course_schedules` (
  `schedule_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_students` tinyint(3) NOT NULL DEFAULT 5,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slot_date` date DEFAULT NULL,
  `slot_hrs` tinyint(4) DEFAULT NULL,
  `slot_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_schedules`
--

INSERT INTO `course_schedules` (`schedule_id`, `instructor_id`, `course_id`, `day_of_week`, `start_time`, `end_time`, `max_students`, `notes`, `is_active`, `created_at`, `slot_date`, `slot_hrs`, `slot_price`) VALUES
(25, 1, 1, 'Tuesday', '00:00:00', '00:00:00', 5, '', 1, '2026-03-27 03:26:27', '2026-03-31', 15, 388.00),
(26, 1, 1, 'Friday', '00:00:00', '00:00:00', 1, 'Requested by student for approval', 1, '2026-04-03 10:53:28', '2026-04-17', 3, 335.00),
(29, 1, 16, 'Tuesday', '00:00:00', '00:00:00', 1, 'Requested by student for approval', 0, '2026-04-05 14:49:37', '2026-04-07', 8, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `enrolment_requests`
--

CREATE TABLE `enrolment_requests` (
  `request_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `preferred_date` date DEFAULT NULL COMMENT 'Specific date student wants (must match day_of_week)',
  `status` enum('pending','approved','rejected','cancelled','done') NOT NULL DEFAULT 'pending',
  `student_note` text DEFAULT NULL,
  `instructor_note` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `done_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolment_requests`
--

INSERT INTO `enrolment_requests` (`request_id`, `schedule_id`, `student_id`, `preferred_date`, `status`, `student_note`, `instructor_note`, `requested_at`, `reviewed_at`, `done_at`) VALUES
(21, 25, 28, NULL, 'done', '', '', '2026-03-27 03:28:43', '2026-03-27 11:28:57', '2026-03-27 11:29:08'),
(22, 26, 28, NULL, 'done', '', 'gg', '2026-04-03 10:53:28', '2026-04-03 18:54:03', '2026-04-03 18:54:12'),
(24, 25, 27, NULL, 'done', '', 'hhh', '2026-04-05 12:42:53', '2026-04-05 20:43:13', '2026-04-05 20:43:24'),
(25, 29, 27, NULL, 'pending', '', NULL, '2026-04-05 14:49:37', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL COMMENT 'Optional: feedback about a specific instructor',
  `schedule_id` int(11) DEFAULT NULL COMMENT 'Optional: feedback about a specific session',
  `rating` tinyint(1) NOT NULL COMMENT '1 to 5 stars',
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `certificate_image` varchar(255) DEFAULT NULL COMMENT 'Filename of uploaded certificate image',
  `password` varchar(255) NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `fullname`, `email`, `certificate_image`, `password`, `modified_date`, `created_at`) VALUES
(1, 'Instructor Carl', 'abrotcarl@gmail.com', 'cert_1_1774057601.png', '$2y$10$Xjz.KySt83TQ8U26ZRgHTun0LXafyW4cfzio8rSx8NtecklPHVx3.', '2026-03-21 09:46:41', '2026-03-16 11:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_type` enum('TDC Face-to-Face','TDC Online','PDC Car Manual','PDC Car Automatic','PDC Motorcycle Manual','PDC Motorcycle Automatic') NOT NULL,
  `description` text DEFAULT NULL,
  `duration_hrs` decimal(4,1) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`course_id`, `instructor_id`, `course_type`, `description`, `duration_hrs`, `price`, `is_active`, `created_at`) VALUES
(1, 1, 'TDC Face-to-Face', '', 2.5, 335.00, 1, '2026-03-22 10:16:48'),
(2, 1, 'TDC Online', '', 4.0, 444.00, 1, '2026-03-22 10:53:54'),
(12, 1, 'PDC Motorcycle Automatic', '', 8.0, 500.00, 1, '2026-04-05 14:06:41'),
(16, 1, 'PDC Motorcycle Manual', '', 8.0, 500.00, 1, '2026-04-05 14:25:09');

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
(1, 1, '2026-03-16 19:12:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698800411384369', '122.54170630680633', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698800411384369,122.54170630680633', 'Wrong password'),
(2, 1, '2026-03-16 19:13:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698909551688143', '122.54185535383283', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698909551688143,122.54185535383283', 'Password verified, OTP sent'),
(3, 1, '2026-03-16 19:16:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698809740627274', '122.54170791147457', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698809740627274,122.54170791147457', 'Password verified, OTP sent'),
(4, 1, '2026-03-16 19:18:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698802937810179', '122.54170210434256', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698802937810179,122.54170210434256', 'Password verified, OTP sent'),
(5, 1, '2026-03-18 16:05:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698808272157216', '122.54170526687058', 'success', NULL, NULL, NULL, 'https://www.google.com/maps?q=10.698808272157216,122.54170526687058', 'Password verified, OTP sent'),
(6, 1, '2026-03-18 16:09:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.69891145586468', '122.54186162622361', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.69891145586468,122.54186162622361', 'Wrong password'),
(7, 1, '2026-03-18 16:09:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.69891145586468', '122.54186162622361', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.69891145586468,122.54186162622361', 'Password verified, OTP sent'),
(8, 1, '2026-03-18 16:24:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.69880654592831', '122.54171041280233', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.69880654592831,122.54171041280233', 'Password verified, OTP sent'),
(9, 1, '2026-03-21 09:40:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(10, 1, '2026-03-21 09:41:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(11, 1, '2026-03-22 16:13:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(12, 1, '2026-03-22 16:13:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(13, 1, '2026-03-22 16:15:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(14, 1, '2026-03-22 16:16:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(15, 1, '2026-03-22 16:28:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(16, 1, '2026-03-22 16:31:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(17, 1, '2026-03-22 16:38:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(18, 1, '2026-03-22 17:02:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(19, 1, '2026-03-22 17:04:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(20, 1, '2026-03-25 13:50:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(21, 1, '2026-03-25 13:51:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'failed', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Wrong password'),
(22, 1, '2026-03-25 13:52:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(23, 1, '2026-03-25 13:52:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(24, 1, '2026-03-25 13:56:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(25, 1, '2026-03-25 14:17:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(26, 1, '2026-03-26 00:02:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(27, 1, '2026-03-26 00:02:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(28, 1, '2026-03-26 00:05:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(29, 1, '2026-03-26 16:12:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(30, 1, '2026-03-26 16:28:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(31, 1, '2026-03-26 20:56:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(32, 1, '2026-03-26 21:42:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(33, 1, '2026-03-27 09:18:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(34, 1, '2026-03-27 09:18:37', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(35, 1, '2026-03-27 11:25:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', NULL, NULL, NULL, NULL, 'Password verified, OTP sent'),
(36, 1, '2026-04-05 20:39:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

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
(17, 1, '794560', 'password_reset', '2026-03-18 16:40:07', '2026-03-18 16:50:07', '2026-03-18 16:40:29', 'used'),
(18, 1, '996379', 'login_verification', '2026-03-21 09:40:23', '2026-03-21 09:50:23', NULL, 'sent'),
(19, 1, '708370', 'login_verification', '2026-03-21 09:41:23', '2026-03-21 09:51:23', NULL, 'failed'),
(20, 1, '214797', 'password_reset', '2026-03-22 16:14:07', '2026-03-22 16:24:07', '2026-03-22 16:14:43', 'used'),
(21, 1, '634334', 'login_verification', '2026-03-22 16:15:37', '2026-03-22 16:25:37', '2026-03-22 16:15:58', 'used'),
(22, 1, '651476', 'login_verification', '2026-03-22 16:16:45', '2026-03-22 16:26:45', '2026-03-22 16:17:04', 'used'),
(23, 1, '356753', 'login_verification', '2026-03-22 16:28:45', '2026-03-22 16:38:45', '2026-03-22 16:29:42', 'used'),
(24, 1, '449261', 'login_verification', '2026-03-22 16:31:24', '2026-03-22 16:41:24', '2026-03-22 16:31:40', 'used'),
(25, 1, '026943', 'login_verification', '2026-03-22 16:38:19', '2026-03-22 16:48:19', '2026-03-22 16:38:36', 'used'),
(26, 1, '728113', 'login_verification', '2026-03-22 17:04:46', '2026-03-22 17:14:46', '2026-03-22 17:05:19', 'used'),
(27, 1, '062774', 'login_verification', '2026-03-25 13:51:58', '2026-03-25 14:01:58', NULL, 'sent'),
(28, 1, '740867', 'login_verification', '2026-03-25 13:52:04', '2026-03-25 14:02:04', '2026-03-25 13:53:46', 'used'),
(29, 1, '278952', 'login_verification', '2026-03-25 13:56:44', '2026-03-25 14:06:44', '2026-03-25 13:57:26', 'used'),
(30, 1, '384723', 'login_verification', '2026-03-25 14:17:07', '2026-03-25 14:27:07', '2026-03-25 14:17:32', 'used'),
(31, 1, '217010', 'login_verification', '2026-03-26 00:02:45', '2026-03-26 00:12:45', NULL, 'sent'),
(32, 1, '465575', 'login_verification', '2026-03-26 00:02:51', '2026-03-26 00:12:51', '2026-03-26 00:04:03', 'used'),
(33, 1, '143585', 'login_verification', '2026-03-26 00:05:18', '2026-03-26 00:15:18', '2026-03-26 00:05:36', 'used'),
(34, 1, '415598', 'login_verification', '2026-03-26 16:11:58', '2026-03-26 16:21:58', '2026-03-26 16:12:20', 'used'),
(35, 1, '577197', 'login_verification', '2026-03-26 16:27:58', '2026-03-26 16:37:58', '2026-03-26 16:28:20', 'used'),
(36, 1, '817800', 'login_verification', '2026-03-26 20:56:41', '2026-03-26 21:06:41', '2026-03-26 20:57:01', 'used'),
(37, 1, '089393', 'login_verification', '2026-03-26 21:42:13', '2026-03-26 21:52:13', '2026-03-26 21:43:00', 'used'),
(38, 1, '343259', 'login_verification', '2026-03-27 09:17:53', '2026-03-27 09:27:53', NULL, 'sent'),
(39, 1, '081622', 'login_verification', '2026-03-27 09:18:12', '2026-03-27 09:28:12', '2026-03-27 09:18:49', 'used'),
(40, 1, '711284', 'login_verification', '2026-03-27 11:23:10', '2026-03-27 11:33:10', NULL, 'sent'),
(41, 1, '638604', 'login_verification', '2026-03-27 11:23:29', '2026-03-27 11:33:29', NULL, 'sent'),
(42, 1, '536422', 'login_verification', '2026-03-27 11:24:31', '2026-03-27 11:34:31', '2026-03-27 11:25:11', 'used'),
(43, 1, '497802', 'login_verification', '2026-04-05 20:39:27', '2026-04-05 20:49:27', '2026-04-05 20:40:39', 'used');

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
(3, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698800034903984', '122.54171245977966', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698800034903984,122.54171245977966', '2026-03-18 08:35:43'),
(4, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698802845547469', '122.54170406709619', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698802845547469,122.54170406709619', '2026-03-18 08:40:39'),
(5, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'Iloilo City', 'Western Visayas', 'Philippines', NULL, '2026-03-22 08:15:15');

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
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `user_id`, `user_type`, `message`, `type`, `is_read`, `created_at`) VALUES
(42, 28, 'student', 'Your enrolment for TDC Face-to-Face (Mar 31, 2026, 15 hrs) with Instructor Your instructor has been APPROVED!', 'enrolment_approved', 1, '2026-03-27 03:28:57'),
(43, 28, 'student', 'Your session for TDC Face-to-Face is done! Please rate your experience.', 'session_done', 1, '2026-03-27 03:29:08'),
(44, 1, 'instructor', 'New booking request for TDC Face-to-Face on Apr 17, 2026 from a student.', 'booking_request', 0, '2026-04-03 10:53:28'),
(45, 28, 'student', 'Your enrolment for TDC Face-to-Face (Apr 17, 2026, 3 hrs) with Instructor Your instructor has been APPROVED!', 'enrolment_approved', 0, '2026-04-03 10:54:03'),
(46, 28, 'student', 'Your session for TDC Face-to-Face is done! Please rate your experience.', 'session_done', 0, '2026-04-03 10:54:12'),
(47, 1, 'instructor', 'New booking request for  on Apr 06, 2026 from a student.', 'booking_request', 0, '2026-04-05 12:34:54'),
(48, 28, 'student', 'Your enrolment for  (Apr 06, 2026, 15 hrs) with Instructor Your instructor has been APPROVED!', 'enrolment_approved', 0, '2026-04-05 12:36:55'),
(49, 28, 'student', 'Your session for  is done! Please rate your experience.', 'session_done', 0, '2026-04-05 12:37:01'),
(50, 27, 'student', 'Your enrolment for TDC Face-to-Face (Mar 31, 2026, 15 hrs) with Instructor Your instructor has been APPROVED!', 'enrolment_approved', 1, '2026-04-05 12:43:13'),
(51, 27, 'student', 'Your session for TDC Face-to-Face is done! Please rate your experience.', 'session_done', 1, '2026-04-05 12:43:24'),
(52, 28, 'student', '📢 Admin Announcement: sxhygedy', 'announcement', 0, '2026-04-05 13:19:01'),
(53, 27, 'student', '📢 Admin Announcement: sxhygedy', 'announcement', 1, '2026-04-05 13:19:01'),
(54, 28, 'student', '📢 Admin Announcement: sxhygedy', 'announcement', 0, '2026-04-05 13:20:26'),
(55, 27, 'student', '📢 Admin Announcement: sxhygedy', 'announcement', 1, '2026-04-05 13:20:26'),
(56, 1, 'instructor', 'New booking request for PDC Motorcycle Manual on Apr 07, 2026 from a student.', 'booking_request', 0, '2026-04-05 14:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `lesson_type` varchar(100) DEFAULT NULL COMMENT 'e.g. Manual, Automatic, Defensive',
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_ratings`
--

CREATE TABLE `session_ratings` (
  `rating_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `rating` tinyint(1) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `course_type` varchar(100) DEFAULT NULL,
  `student_rating` tinyint(4) DEFAULT NULL,
  `student_feedback` text DEFAULT NULL,
  `student_rated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_ratings`
--

INSERT INTO `session_ratings` (`rating_id`, `request_id`, `student_id`, `instructor_id`, `rating`, `description`, `course_type`, `student_rating`, `student_feedback`, `student_rated_at`, `created_at`) VALUES
(4, 21, 28, 1, 5, 'Excellent', 'TDC Face-to-Face', NULL, NULL, NULL, '2026-04-03 10:46:03'),
(7, 24, 27, 1, 5, 'Excellent', 'TDC Face-to-Face', 5, '', '2026-04-05 21:31:59', '2026-04-05 13:29:31'),
(9, 22, 28, 1, NULL, NULL, 'TDC Face-to-Face', 4, '', '2026-04-05 21:32:08', '2026-04-05 13:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `fullname`, `email`, `password_hash`, `is_verified`, `created_at`) VALUES
(27, 'Carl Edciel', 'cara.abrot.ui@phinmaed.com', '$2y$10$837MiGQAS1yef8dMf1eEwe7DC2gxO.JBLDHn67.iaFhrOFYIddGni', 1, '2026-03-22 06:46:56'),
(28, 'Kent John Canas', 'canaskentjohn@gmail.com', '$2y$10$vXjVMSjdtietwHh1CBM3JePsBtopD1X0NRof4bHRW8miMYAfLh6KG', 1, '2026-03-27 02:07:36');

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
(41, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 145.0.0.0', 'success', '2026-03-22 14:50:04'),
(42, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-22 16:18:45'),
(43, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-25 14:09:38'),
(44, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-25 14:23:54'),
(45, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 15:45:06'),
(46, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 15:52:43'),
(47, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 15:59:10'),
(48, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 16:10:17'),
(49, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 16:13:51'),
(50, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 16:16:13'),
(51, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 21:45:36'),
(53, 28, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-27 10:13:25'),
(54, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-04-05 20:42:26');

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
(35, 27, '684753', 'signup', '2026-03-22 14:56:56', 1, '2026-03-22 06:46:56'),
(36, 27, '745656', 'signup', '2026-03-22 14:57:58', 1, '2026-03-22 06:47:58'),
(37, 27, '345846', 'login', '2026-03-22 14:59:34', 1, '2026-03-22 06:49:34'),
(38, 27, '109043', 'login', '2026-03-22 16:28:18', 1, '2026-03-22 08:18:18'),
(39, 27, '064477', 'login', '2026-03-25 14:17:25', 1, '2026-03-25 06:07:25'),
(40, 27, '334065', 'login', '2026-03-25 14:17:31', 1, '2026-03-25 06:07:31'),
(41, 27, '902461', 'login', '2026-03-25 14:33:10', 1, '2026-03-25 06:23:10'),
(42, 27, '834706', 'login', '2026-03-26 15:54:29', 1, '2026-03-26 07:44:29'),
(43, 27, '231768', 'login', '2026-03-26 16:02:03', 1, '2026-03-26 07:52:03'),
(44, 27, '130942', 'login', '2026-03-26 16:08:39', 1, '2026-03-26 07:58:39'),
(45, 27, '051745', 'login', '2026-03-26 16:19:29', 1, '2026-03-26 08:09:29'),
(46, 27, '502850', 'login', '2026-03-26 16:23:11', 1, '2026-03-26 08:13:11'),
(47, 27, '256397', 'login', '2026-03-26 16:25:49', 1, '2026-03-26 08:15:49'),
(48, 27, '848765', 'login', '2026-03-26 21:55:10', 1, '2026-03-26 13:45:10'),
(49, 28, '786312', 'signup', '2026-03-27 10:17:36', 1, '2026-03-27 02:07:36'),
(50, 28, '015944', 'signup', '2026-03-27 10:20:07', 1, '2026-03-27 02:10:07'),
(51, 28, '209342', 'login', '2026-03-27 10:22:54', 1, '2026-03-27 02:12:54'),
(52, 27, '365249', 'login', '2026-04-05 20:51:52', 1, '2026-04-05 12:41:52');

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
-- Indexes for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_cs_instructor` (`instructor_id`),
  ADD KEY `fk_cs_course` (`course_id`);

--
-- Indexes for table `enrolment_requests`
--
ALTER TABLE `enrolment_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `unique_enrol` (`schedule_id`,`student_id`,`preferred_date`),
  ADD KEY `fk_er_schedule` (`schedule_id`),
  ADD KEY `fk_er_student` (`student_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `fk_feedback_student` (`student_id`),
  ADD KEY `fk_feedback_instructor` (`instructor_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`);

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `idx_notif_user` (`user_id`,`user_type`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_schedule_instructor` (`instructor_id`),
  ADD KEY `fk_schedule_student` (`student_id`);

--
-- Indexes for table `session_ratings`
--
ALTER TABLE `session_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_rating` (`request_id`),
  ADD KEY `fk_sr_student` (`student_id`),
  ADD KEY `fk_sr_instructor` (`instructor_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  MODIFY `admin_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `admin_otp_history`
--
ALTER TABLE `admin_otp_history`
  MODIFY `admin_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `course_schedules`
--
ALTER TABLE `course_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `enrolment_requests`
--
ALTER TABLE `enrolment_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `instructor_login_history`
--
ALTER TABLE `instructor_login_history`
  MODIFY `instructor_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `instructor_otp_history`
--
ALTER TABLE `instructor_otp_history`
  MODIFY `instructor_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `instructor_password_history`
--
ALTER TABLE `instructor_password_history`
  MODIFY `password_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_ratings`
--
ALTER TABLE `session_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `student_lockout_history`
--
ALTER TABLE `student_lockout_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_login_history`
--
ALTER TABLE `student_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `student_otp_history`
--
ALTER TABLE `student_otp_history`
  MODIFY `otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `student_password_history`
--
ALTER TABLE `student_password_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `course_schedules`
--
ALTER TABLE `course_schedules`
  ADD CONSTRAINT `fk_cs_course` FOREIGN KEY (`course_id`) REFERENCES `instructor_courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cs_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrolment_requests`
--
ALTER TABLE `enrolment_requests`
  ADD CONSTRAINT `fk_er_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `course_schedules` (`schedule_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_er_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

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
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedule_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `session_ratings`
--
ALTER TABLE `session_ratings`
  ADD CONSTRAINT `fk_sr_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sr_request` FOREIGN KEY (`request_id`) REFERENCES `enrolment_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

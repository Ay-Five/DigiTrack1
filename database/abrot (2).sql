-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 26, 2026 at 06:43 AM
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
(4, 1, '2026-03-16 16:53:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698937', '122.541893', 'failed', NULL, NULL, NULL, NULL, 'Wrong password'),
(5, 1, '2026-03-16 17:04:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698915', '122.541852', 'failed', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698915,122.541852', 'Wrong password'),
(6, 1, '2026-03-21 09:16:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(7, 1, '2026-03-25 13:32:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(8, 1, '2026-03-26 15:43:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(9, 1, '2026-04-05 20:54:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

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
(7, 1, '513499', 'login_verification', '2026-04-05 20:54:17', '2026-04-05 21:04:17', '2026-04-05 20:54:38', 'used');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `total_hours` tinyint(3) NOT NULL DEFAULT 15,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `student_note` text DEFAULT NULL,
  `instructor_note` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `student_id`, `instructor_id`, `course_id`, `start_date`, `total_hours`, `total_price`, `status`, `student_note`, `instructor_note`, `requested_at`, `reviewed_at`, `completed_at`) VALUES
(1, 27, 1, 1, '2026-03-31', 15, 2010.00, 'completed', '', 'Good student, completed all sessions.', '2026-03-27 03:28:43', '2026-03-27 11:28:57', '2026-04-05 20:43:24'),
(2, 28, 1, 1, '2026-04-17', 15, 2010.00, 'in_progress', '', '', '2026-04-03 10:53:28', '2026-04-03 18:54:03', NULL),
(3, 27, 1, 1, '2026-04-23', 15, 2010.00, 'completed', '', '', '2026-04-22 03:47:18', '2026-04-22 15:13:39', '2026-04-22 15:14:43');

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
  `price_per_hr` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`course_id`, `instructor_id`, `course_type`, `description`, `price_per_hr`, `is_active`, `created_at`) VALUES
(1, 1, 'TDC Face-to-Face', '', 134.00, 1, '2026-03-22 10:16:48'),
(2, 1, 'TDC Online', '', 111.00, 1, '2026-03-22 10:53:54'),
(3, 1, 'PDC Motorcycle Automatic', '', 62.50, 1, '2026-04-05 14:06:41'),
(4, 1, 'PDC Motorcycle Manual', '', 62.50, 1, '2026-04-05 14:25:09');

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
(1, 1, '2026-03-16 19:13:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698909', '122.541855', 'success', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698909,122.541855', 'Password verified, OTP sent'),
(2, 1, '2026-03-21 09:40:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(3, 1, '2026-03-25 13:52:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent'),
(4, 1, '2026-04-05 20:39:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'success', 'Iloilo City', 'Western Visayas', 'Philippines', NULL, 'Password verified, OTP sent');

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
(4, 1, '497802', 'login_verification', '2026-04-05 20:39:27', '2026-04-05 20:49:27', '2026-04-05 20:40:39', 'used');

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
(1, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '10.698800', '122.541712', 'Altavas', 'Western Visayas', 'Philippines', 'https://www.google.com/maps?q=10.698800,122.541712', '2026-03-18 08:35:43'),
(2, 1, 'reset', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', NULL, NULL, 'Iloilo City', 'Western Visayas', 'Philippines', NULL, '2026-03-22 08:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_ratings`
--

CREATE TABLE `instructor_ratings` (
  `rating_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `comment` text DEFAULT NULL,
  `rated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_ratings`
--

INSERT INTO `instructor_ratings` (`rating_id`, `appointment_id`, `student_id`, `instructor_id`, `rating`, `comment`, `rated_at`) VALUES
(1, 1, 27, 1, 5, 'Very patient and knowledgeable instructor. Highly recommended!', '2026-04-05 21:31:59'),
(2, 3, 27, 1, 5, 'very kind', '2026-04-22 15:15:28');

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
(1, 1, 'instructor', 'New appointment request for TDC Face-to-Face on Mar 31, 2026 from Carl Edciel.', 'booking_request', 1, '2026-03-27 03:28:43'),
(2, 27, 'student', 'Your appointment for TDC Face-to-Face has been APPROVED!', 'appointment_approved', 1, '2026-03-27 11:28:57'),
(3, 1, 'instructor', 'New appointment request for TDC Face-to-Face on Apr 17, 2026 from Kent John Canas.', 'booking_request', 1, '2026-04-03 10:53:28'),
(4, 28, 'student', 'Your appointment for TDC Face-to-Face has been APPROVED!', 'appointment_approved', 1, '2026-04-03 18:54:03'),
(5, 27, 'student', 'Your course is complete! Please rate your instructor.', 'course_completed', 1, '2026-04-05 20:43:24'),
(6, 27, 'student', 'Your TDC Certificate has been issued! PDC courses are now unlocked.', 'certificate_issued', 1, '2026-04-05 20:45:00'),
(7, 1, 'instructor', 'New appointment request for TDC Face-to-Face on 2026-04-23 from A student.', 'booking_request', 0, '2026-04-22 03:47:18'),
(8, 27, 'student', '📢 Admin: pkmjiofew', 'announcement', 0, '2026-04-22 07:06:39'),
(9, 28, 'student', '📢 Admin: pkmjiofew', 'announcement', 0, '2026-04-22 07:06:39'),
(10, 27, 'student', 'Your appointment for TDC Face-to-Face has been APPROVED!', 'appointment_approved', 0, '2026-04-22 07:13:39'),
(11, 27, 'student', 'Your appointment has been marked complete by admin.', 'admin_action', 0, '2026-04-22 07:14:43'),
(12, 27, 'student', 'Your TDC Certificate has been issued! PDC is now unlocked.', 'certificate_issued', 0, '2026-04-22 07:15:11');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `hours_covered` decimal(4,1) NOT NULL DEFAULT 1.0,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'completed',
  `performance_rating` tinyint(1) DEFAULT NULL COMMENT '1-5 stars, rated by instructor per session',
  `performance_notes` text DEFAULT NULL COMMENT 'Instructor feedback on student for this session',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `appointment_id`, `student_id`, `instructor_id`, `session_date`, `hours_covered`, `status`, `performance_rating`, `performance_notes`, `completed_at`, `created_at`) VALUES
(1, 1, 27, 1, '2026-03-31', 3.0, 'completed', 4, 'Good focus. Needs more practice on road signs.', '2026-03-31 17:00:00', '2026-03-31 08:00:00'),
(2, 1, 27, 1, '2026-04-01', 3.0, 'completed', 5, 'Excellent improvement. Very attentive.', '2026-04-01 17:00:00', '2026-04-01 08:00:00'),
(3, 1, 27, 1, '2026-04-02', 3.0, 'completed', 4, 'Good performance. Minor errors on lane discipline.', '2026-04-02 17:00:00', '2026-04-02 08:00:00'),
(4, 1, 27, 1, '2026-04-03', 3.0, 'completed', 5, 'Excellent. Ready for practical exam.', '2026-04-03 17:00:00', '2026-04-03 08:00:00'),
(5, 1, 27, 1, '2026-04-04', 3.0, 'completed', 5, 'Perfect performance. TDC complete.', '2026-04-04 17:00:00', '2026-04-04 08:00:00'),
(6, 2, 28, 1, '2026-04-17', 3.0, 'completed', 3, 'Decent start. Needs to review road signs chapter.', '2026-04-17 17:00:00', '2026-04-17 08:00:00'),
(7, 2, 28, 1, '2026-04-18', 3.0, 'completed', 4, 'Improved. Better understanding of traffic rules.', '2026-04-18 17:00:00', '2026-04-18 08:00:00');

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
(1, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 145.0.0.0', 'success', '2026-03-22 14:50:04'),
(2, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-22 16:18:45'),
(3, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-25 14:09:38'),
(4, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-26 15:45:06'),
(5, 28, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-03-27 10:13:25'),
(6, 27, '127.0.0.1', 'WebKit | Windows 10.0 | Chrome 146.0.0.0', 'success', '2026-04-05 20:42:26');

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
(7, 27, '365249', 'login', '2026-04-05 20:51:52', 1, '2026-04-05 12:41:52');

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

-- --------------------------------------------------------

--
-- Table structure for table `tdc_certificates`
--

CREATE TABLE `tdc_certificates` (
  `cert_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL COMMENT 'The completed TDC appointment',
  `issued_by` int(11) NOT NULL COMMENT 'instructor_id who issued it',
  `issued_at` datetime NOT NULL DEFAULT current_timestamp(),
  `cert_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tdc_certificates`
--

INSERT INTO `tdc_certificates` (`cert_id`, `student_id`, `appointment_id`, `issued_by`, `issued_at`, `cert_number`) VALUES
(1, 27, 1, 1, '2026-04-05 20:45:00', 'TDC-2026-001'),
(2, 27, 3, 1, '2026-04-22 15:15:11', '');

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
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `idx_notif_user` (`user_id`,`user_type`);

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_history`
--
ALTER TABLE `admin_login_history`
  MODIFY `admin_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `admin_otp_history`
--
ALTER TABLE `admin_otp_history`
  MODIFY `admin_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructor_login_history`
--
ALTER TABLE `instructor_login_history`
  MODIFY `instructor_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructor_otp_history`
--
ALTER TABLE `instructor_otp_history`
  MODIFY `instructor_otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructor_password_history`
--
ALTER TABLE `instructor_password_history`
  MODIFY `password_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `instructor_ratings`
--
ALTER TABLE `instructor_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `student_lockout_history`
--
ALTER TABLE `student_lockout_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_login_history`
--
ALTER TABLE `student_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_otp_history`
--
ALTER TABLE `student_otp_history`
  MODIFY `otp_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_password_history`
--
ALTER TABLE `student_password_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

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

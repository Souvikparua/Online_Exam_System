-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2025 at 05:42 AM
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
-- Database: `online_exam_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(11) NOT NULL,
  `chapter_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `chapter_name`, `created_by`, `created_at`) VALUES
(1, 'Cloud', 4, '2025-02-16 15:54:04'),
(2, 'Cloud', 4, '2025-02-17 04:23:06'),
(3, 'physics', 4, '2025-02-17 04:45:42'),
(4, 'physics', 4, '2025-02-17 06:04:31'),
(5, 'math', 6, '2025-02-18 14:59:50'),
(6, 'Math2', 7, '2025-02-20 15:33:28'),
(7, 'phy', 7, '2025-02-20 15:38:46'),
(8, 'phy1', 6, '2025-02-20 15:44:18'),
(9, 'Operating System', 15, '2025-02-21 14:59:35'),
(10, 'Cloud', 15, '2025-02-21 15:08:31'),
(11, 'Operating System', 19, '2025-02-21 15:34:40'),
(12, 'Cyber Security', 19, '2025-02-21 15:46:26');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `retake_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `schedule_time` datetime DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `total_marks` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `exam_type` enum('practice','final') NOT NULL DEFAULT 'final'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_name`, `description`, `created_by`, `created_at`, `retake_allowed`, `schedule_time`, `duration`, `is_active`, `total_marks`, `deleted_at`, `updated_at`, `exam_type`) VALUES
(1, 'DBMS', 'DBMS Exam', 2, '2025-02-15 15:56:43', 0, NULL, 0, 1, 0, NULL, NULL, 'final'),
(2, 'CN', 'Computer Networking', NULL, '2025-02-18 17:39:39', 0, '2025-02-21 22:02:00', 60, 1, 0, NULL, '2025-02-21 11:48:47', 'final'),
(3, 'ans', NULL, NULL, '2025-02-18 17:40:10', 0, '2025-02-19 03:09:00', 2, 1, 0, NULL, NULL, 'final'),
(4, 'math', NULL, 16, '2025-02-20 15:28:44', 0, '2025-02-20 22:59:00', 10, 1, 0, NULL, NULL, 'final'),
(5, '3rdsem', NULL, 15, '2025-02-20 15:46:03', 1, '2025-02-20 21:16:00', 7, 1, 0, NULL, NULL, 'final'),
(6, '1st', NULL, 15, '2025-02-20 16:07:11', 1, '2025-02-21 23:39:00', 13, 1, 0, NULL, NULL, 'final'),
(7, '2nd', NULL, 15, '2025-02-20 16:31:43', 1, '2025-02-21 22:01:00', 5, 1, 0, NULL, NULL, 'final'),
(8, '3rd', NULL, 15, '2025-02-20 16:37:59', 1, '2025-02-21 22:07:00', 5, 1, 0, NULL, NULL, 'final'),
(9, 'operating system', NULL, 15, '2025-02-21 14:27:23', 1, '2025-02-21 19:56:00', 15, 1, 0, NULL, NULL, 'final'),
(10, 'DBMS', NULL, 15, '2025-02-21 14:37:01', 0, '2025-02-21 20:06:00', 10, 1, 0, NULL, NULL, 'final'),
(11, 'operating system', NULL, 15, '2025-02-21 14:39:30', 0, '2025-02-21 20:09:00', 20, 1, 0, NULL, NULL, 'final'),
(12, 'operating system', NULL, 15, '2025-02-21 14:44:01', 0, '2025-02-21 20:13:00', 10, 1, 0, NULL, NULL, 'final'),
(13, 'physics', NULL, 15, '2025-02-21 14:45:55', 0, '2025-02-21 20:15:00', 1, 1, 0, NULL, NULL, 'final'),
(14, 'Cloud', NULL, 15, '2025-02-21 15:24:27', 0, '2025-02-21 20:53:00', 1, 1, 0, NULL, NULL, 'final'),
(15, 'operating system', NULL, 15, '2025-02-21 15:37:13', 0, '2025-02-21 21:06:00', -2, 1, 0, NULL, NULL, 'final'),
(16, 'Cyber Security', NULL, 15, '2025-02-21 15:52:44', 0, '2025-02-21 21:22:00', 1, 1, 0, NULL, NULL, 'final');

-- --------------------------------------------------------

--
-- Table structure for table `exam_attempts`
--

CREATE TABLE `exam_attempts` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `attempt_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('completed','in_progress') NOT NULL DEFAULT 'completed',
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_attempts`
--

INSERT INTO `exam_attempts` (`id`, `student_id`, `exam_id`, `score`, `attempt_date`, `status`, `is_correct`) VALUES
(1, 14, 5, 0.00, '2025-02-20 15:55:21', 'completed', NULL),
(2, 14, 5, 0.00, '2025-02-20 15:56:33', 'completed', NULL),
(4, 14, 4, 0.00, '2025-02-20 15:57:06', 'completed', NULL),
(5, 14, 4, 0.00, '2025-02-20 15:57:31', 'completed', NULL),
(6, 14, 4, 0.00, '2025-02-20 15:57:46', 'completed', NULL),
(7, 14, 3, 0.00, '2025-02-20 16:03:59', 'completed', NULL),
(8, 14, 2, 0.00, '2025-02-20 16:04:41', 'completed', NULL),
(9, 14, 3, 0.00, '2025-02-20 16:05:18', 'completed', NULL),
(10, 14, 6, 0.00, '2025-02-20 16:08:31', 'completed', NULL),
(11, 14, 7, 0.00, '2025-02-20 16:37:00', 'completed', NULL),
(12, 14, 8, 0.00, '2025-02-20 16:39:15', 'completed', NULL),
(13, 4, 8, 0.00, '2025-02-21 02:58:42', 'completed', NULL),
(14, 4, 7, 0.00, '2025-02-21 12:13:41', 'completed', NULL),
(15, 4, 6, 0.00, '2025-02-21 12:18:45', 'completed', NULL),
(16, 4, 5, 0.00, '2025-02-21 12:20:52', 'completed', NULL),
(17, 4, 4, 0.00, '2025-02-21 12:45:14', 'completed', NULL),
(18, 4, 3, 0.00, '2025-02-21 13:08:02', 'completed', NULL),
(19, 4, 2, 0.00, '2025-02-21 13:57:54', 'completed', NULL),
(20, 4, 9, 0.00, '2025-02-21 14:29:15', 'completed', NULL),
(21, 4, 10, 0.00, '2025-02-21 14:37:55', 'completed', NULL),
(22, 4, 11, 0.00, '2025-02-21 14:40:10', 'completed', NULL),
(23, 4, 12, 0.00, '2025-02-21 14:45:05', 'completed', NULL),
(24, 4, 13, 0.00, '2025-02-21 14:47:19', 'completed', NULL),
(25, 4, 14, 0.00, '2025-02-21 15:27:26', 'completed', NULL),
(26, 4, 15, 0.00, '2025-02-21 15:38:08', 'completed', NULL),
(27, 4, 16, 0.00, '2025-02-21 15:55:23', 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exam_questions`
--

CREATE TABLE `exam_questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `marks` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_questions`
--

INSERT INTO `exam_questions` (`id`, `exam_id`, `question_id`, `marks`, `created_at`, `modified_text`) VALUES
(1, 2, 2, 1, '2025-02-18 17:39:39', 'iaas'),
(2, 2, 3, 1, '2025-02-18 17:39:39', 'saas'),
(3, 2, 4, 1, '2025-02-18 17:39:39', 'saas'),
(4, 2, 5, 1, '2025-02-18 17:39:39', 'saas'),
(5, 2, 6, 1, '2025-02-18 17:39:39', '2+3'),
(6, 2, 7, 1, '2025-02-18 17:39:39', '5+1'),
(7, 2, 8, 1, '2025-02-18 17:39:39', '2+3'),
(8, 3, 2, 1, '2025-02-18 17:40:10', 'iaas'),
(9, 3, 3, 1, '2025-02-18 17:40:10', 'saas'),
(10, 3, 4, 1, '2025-02-18 17:40:10', 'saas'),
(11, 3, 5, 1, '2025-02-18 17:40:10', 'saas'),
(12, 3, 6, 1, '2025-02-18 17:40:10', '2+3'),
(13, 3, 7, 1, '2025-02-18 17:40:10', '5+1'),
(14, 3, 8, 1, '2025-02-18 17:40:10', '2+3'),
(15, 4, 8, 1, '2025-02-20 15:28:44', '2+3'),
(16, 5, 8, 1, '2025-02-20 15:46:03', '2+3'),
(17, 5, 9, 1, '2025-02-20 15:46:03', '2+2'),
(18, 5, 10, 1, '2025-02-20 15:46:03', 'speed of light?'),
(19, 5, 11, 1, '2025-02-20 15:46:03', 'f=?'),
(20, 6, 8, 1, '2025-02-20 16:07:11', '2+3'),
(21, 6, 9, 1, '2025-02-20 16:07:11', '2+2'),
(22, 6, 10, 1, '2025-02-20 16:07:11', 'speed of light?'),
(23, 6, 11, 1, '2025-02-20 16:07:11', 'f=?'),
(24, 7, 8, 1, '2025-02-20 16:31:43', '2+3'),
(25, 7, 9, 1, '2025-02-20 16:31:43', '2+2'),
(26, 7, 10, 1, '2025-02-20 16:31:43', 'speed of light?'),
(27, 7, 11, 1, '2025-02-20 16:31:43', 'f=?'),
(28, 8, 8, 1, '2025-02-20 16:37:59', '2+3'),
(29, 8, 9, 1, '2025-02-20 16:37:59', '2+2'),
(30, 8, 10, 1, '2025-02-20 16:37:59', 'speed of light?'),
(31, 8, 11, 1, '2025-02-20 16:37:59', 'f=?'),
(32, 9, 2, 1, '2025-02-21 14:27:23', 'iaas'),
(33, 9, 3, 1, '2025-02-21 14:27:23', 'saas'),
(34, 9, 4, 1, '2025-02-21 14:27:23', 'saas'),
(35, 9, 5, 1, '2025-02-21 14:27:23', 'saas'),
(36, 9, 6, 1, '2025-02-21 14:27:23', '2+3'),
(37, 9, 7, 1, '2025-02-21 14:27:23', '5+1'),
(38, 9, 8, 1, '2025-02-21 14:27:23', '2+3'),
(39, 9, 9, 1, '2025-02-21 14:27:23', '2+2'),
(40, 9, 10, 1, '2025-02-21 14:27:23', 'speed of light?'),
(41, 9, 11, 1, '2025-02-21 14:27:23', 'f=?'),
(42, 10, 2, 1, '2025-02-21 14:37:01', 'iaas'),
(43, 10, 3, 1, '2025-02-21 14:37:01', 'saas'),
(44, 10, 4, 1, '2025-02-21 14:37:01', 'saas'),
(45, 10, 5, 1, '2025-02-21 14:37:01', 'saas'),
(46, 10, 6, 1, '2025-02-21 14:37:01', '2+3'),
(47, 10, 7, 1, '2025-02-21 14:37:02', '5+1'),
(48, 10, 8, 1, '2025-02-21 14:37:02', '2+3'),
(49, 10, 9, 1, '2025-02-21 14:37:02', '2+2'),
(50, 10, 10, 1, '2025-02-21 14:37:02', 'speed of light?'),
(51, 10, 11, 1, '2025-02-21 14:37:02', 'f=?'),
(52, 11, 2, 1, '2025-02-21 14:39:30', 'iaas'),
(53, 11, 3, 1, '2025-02-21 14:39:30', 'saas'),
(54, 11, 4, 1, '2025-02-21 14:39:30', 'saas'),
(55, 11, 5, 1, '2025-02-21 14:39:30', 'saas'),
(56, 11, 6, 1, '2025-02-21 14:39:30', '2+3'),
(57, 11, 7, 1, '2025-02-21 14:39:30', '5+1'),
(58, 11, 8, 1, '2025-02-21 14:39:30', '2+3'),
(59, 11, 9, 1, '2025-02-21 14:39:30', '2+2'),
(60, 11, 10, 1, '2025-02-21 14:39:30', 'speed of light?'),
(61, 11, 11, 1, '2025-02-21 14:39:30', 'f=?'),
(62, 12, 2, 1, '2025-02-21 14:44:01', 'iaas'),
(63, 12, 3, 1, '2025-02-21 14:44:01', 'saas'),
(64, 12, 4, 1, '2025-02-21 14:44:01', 'saas'),
(65, 12, 5, 1, '2025-02-21 14:44:01', 'saas'),
(66, 12, 6, 1, '2025-02-21 14:44:01', '2+3'),
(67, 12, 7, 1, '2025-02-21 14:44:01', '5+1'),
(68, 12, 8, 1, '2025-02-21 14:44:01', '2+3'),
(69, 12, 9, 1, '2025-02-21 14:44:01', '2+2'),
(70, 12, 10, 1, '2025-02-21 14:44:01', 'speed of light?'),
(71, 12, 11, 1, '2025-02-21 14:44:01', 'f=?'),
(72, 13, 2, 1, '2025-02-21 14:45:55', 'iaas'),
(73, 13, 3, 1, '2025-02-21 14:45:55', 'saas'),
(74, 13, 4, 1, '2025-02-21 14:45:55', 'saas'),
(75, 13, 5, 1, '2025-02-21 14:45:55', 'saas'),
(76, 13, 6, 1, '2025-02-21 14:45:55', '2+3'),
(77, 13, 7, 1, '2025-02-21 14:45:55', '5+1'),
(78, 13, 8, 1, '2025-02-21 14:45:55', '2+3'),
(79, 13, 9, 1, '2025-02-21 14:45:55', '2+2'),
(80, 13, 10, 1, '2025-02-21 14:45:55', 'speed of light?'),
(81, 13, 11, 1, '2025-02-21 14:45:55', 'f=?'),
(82, 14, 2, 1, '2025-02-21 15:24:27', 'iaas'),
(83, 14, 3, 1, '2025-02-21 15:24:27', 'saas'),
(84, 14, 4, 1, '2025-02-21 15:24:27', 'saas'),
(85, 14, 5, 1, '2025-02-21 15:24:27', 'saas'),
(86, 14, 6, 1, '2025-02-21 15:24:27', '2+3'),
(87, 14, 7, 1, '2025-02-21 15:24:27', '5+1'),
(88, 14, 8, 1, '2025-02-21 15:24:27', '2+3'),
(89, 14, 9, 1, '2025-02-21 15:24:27', '2+2'),
(90, 14, 10, 1, '2025-02-21 15:24:27', 'speed of light?'),
(91, 14, 11, 1, '2025-02-21 15:24:27', 'f=?'),
(92, 14, 12, 1, '2025-02-21 15:24:27', 'what is kernel'),
(93, 14, 13, 1, '2025-02-21 15:24:27', 'AWS S3'),
(94, 14, 14, 1, '2025-02-21 15:24:27', 'Azure'),
(95, 15, 2, 1, '2025-02-21 15:37:13', 'iaas'),
(96, 15, 3, 1, '2025-02-21 15:37:13', 'saas'),
(97, 15, 4, 1, '2025-02-21 15:37:13', 'saas'),
(98, 15, 5, 1, '2025-02-21 15:37:13', 'saas'),
(99, 15, 6, 1, '2025-02-21 15:37:13', '2+3'),
(100, 15, 7, 1, '2025-02-21 15:37:13', '5+1'),
(101, 15, 8, 1, '2025-02-21 15:37:13', '2+3'),
(102, 15, 9, 1, '2025-02-21 15:37:13', '2+2'),
(103, 15, 10, 1, '2025-02-21 15:37:13', 'speed of light?'),
(104, 15, 11, 1, '2025-02-21 15:37:13', 'f=?'),
(105, 15, 12, 1, '2025-02-21 15:37:13', 'what is kernel'),
(106, 15, 13, 1, '2025-02-21 15:37:13', 'AWS S3'),
(107, 15, 14, 1, '2025-02-21 15:37:13', 'Azure'),
(108, 15, 15, 1, '2025-02-21 15:37:13', 'yml'),
(109, 16, 2, 1, '2025-02-21 15:52:44', 'iaas'),
(110, 16, 3, 1, '2025-02-21 15:52:44', 'saas'),
(111, 16, 4, 1, '2025-02-21 15:52:44', 'saas'),
(112, 16, 5, 1, '2025-02-21 15:52:44', 'saas'),
(113, 16, 6, 1, '2025-02-21 15:52:44', '2+3'),
(114, 16, 7, 1, '2025-02-21 15:52:44', '5+1'),
(115, 16, 8, 1, '2025-02-21 15:52:44', '2+3'),
(116, 16, 9, 1, '2025-02-21 15:52:44', '2+2'),
(117, 16, 10, 1, '2025-02-21 15:52:44', 'speed of light?'),
(118, 16, 11, 1, '2025-02-21 15:52:44', 'f=?'),
(119, 16, 12, 1, '2025-02-21 15:52:44', 'what is kernel'),
(120, 16, 13, 1, '2025-02-21 15:52:44', 'AWS S3'),
(121, 16, 14, 1, '2025-02-21 15:52:44', 'Azure'),
(122, 16, 15, 1, '2025-02-21 15:52:44', 'yml'),
(123, 16, 16, 1, '2025-02-21 15:52:44', 'ITAct2000');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `exam_attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` varchar(255) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `marks_obtained` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `chapter_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question` text NOT NULL,
  `option1` text NOT NULL,
  `option2` text NOT NULL,
  `option3` text NOT NULL,
  `option4` text NOT NULL,
  `correct_answer` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `solution_pdf` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_multiple_answer` tinyint(1) DEFAULT 0,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `question_image` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `default_marks` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `chapter_id`, `question_text`, `question`, `option1`, `option2`, `option3`, `option4`, `correct_answer`, `image`, `solution_pdf`, `created_by`, `created_at`, `is_multiple_answer`, `options`, `question_image`, `deleted_at`, `updated_at`, `default_marks`) VALUES
(2, 0, 1, '', 'iaas', '', '', '', '', '', NULL, 'solution_pdfs/67b347411ad0e_PERSONAL STATEMENT.pdf', 4, '2025-02-16 16:54:40', 0, '[{\"text\":\"infra\",\"image\":\"question_images\\/67b21850ed49e_Picture.jpeg\",\"is_correct\":1},{\"text\":\"paas\",\"image\":null,\"is_correct\":0}]', NULL, NULL, NULL, 1),
(3, 0, 1, '', 'saas', '', '', '', '', '', NULL, NULL, 4, '2025-02-17 04:24:04', 0, '[{\"text\":\"sv\",\"image\":\"question_images\\/67b2b9e4dbf5d_Picture.jpeg\",\"is_correct\":0},{\"text\":\"gd\",\"image\":null,\"is_correct\":0}]', 'question_images/67b2b9e4dbd15_Signature_11zon.jpeg', NULL, NULL, 1),
(4, 0, 1, '', 'saas', '', '', '', '', '', NULL, NULL, 4, '2025-02-17 04:34:38', 0, '[{\"text\":\"sv\",\"image\":\"question_images\\/67b2bc5e77dfe_Picture.jpeg\",\"is_correct\":0},{\"text\":\"gd\",\"image\":null,\"is_correct\":0}]', 'question_images/67b2bc5e77af9_Signature_11zon.jpeg', NULL, NULL, 1),
(5, 0, 1, '', 'saas', '', '', '', '', '', NULL, NULL, 4, '2025-02-17 04:38:07', 0, '[{\"text\":\"sv\",\"image\":\"question_images\\/67b2bd2fb55b5_Picture.jpeg\",\"is_correct\":0},{\"text\":\"gd\",\"image\":null,\"is_correct\":0}]', 'question_images/67b2bd2fb5208_Signature_11zon.jpeg', NULL, NULL, 1),
(6, 0, 3, '', '2+3', '', '', '', '', '', NULL, NULL, 4, '2025-02-17 14:04:28', 0, '[{\"text\":\"2\",\"image\":null,\"is_correct\":0},{\"text\":\"5\",\"image\":null,\"is_correct\":1}]', 'question_images/67b341eca23cf_IMG_9107.JPG', NULL, NULL, 1),
(7, 0, 4, '', '5+1', '', '', '', '', '', NULL, NULL, 4, '2025-02-17 14:13:16', 0, '[{\"text\":\"6\",\"image\":null,\"is_correct\":1},{\"text\":\"4\",\"image\":\"question_images\\/67b343fcb0888_SRK_9528.JPG\",\"is_correct\":0}]', 'question_images/67b343fcb045d_IMG_9107.JPG', NULL, NULL, 1),
(8, 0, 5, '', '2+3', '', '', '', '', '', NULL, NULL, 6, '2025-02-18 15:00:31', 0, '[{\"text\":\"5\",\"image\":null,\"is_correct\":1},{\"text\":\"10\",\"image\":null,\"is_correct\":0}]', NULL, NULL, NULL, 1),
(9, 0, 6, '', '2+2', '', '', '', '', '', NULL, NULL, 7, '2025-02-20 15:34:13', 0, '[{\"text\":\"4\",\"image\":null,\"is_correct\":1},{\"text\":\"5\",\"image\":null,\"is_correct\":0},{\"text\":\"6\",\"image\":null,\"is_correct\":0},{\"text\":\"7\",\"image\":null,\"is_correct\":0}]', NULL, NULL, NULL, 1),
(10, 0, 7, '', 'speed of light?', '', '', '', '', '', NULL, NULL, 7, '2025-02-20 15:39:36', 0, '[{\"text\":\"3*10^8\",\"image\":null,\"is_correct\":1},{\"text\":\"5\",\"image\":null,\"is_correct\":0},{\"text\":\"6\",\"image\":null,\"is_correct\":0},{\"text\":\"9\",\"image\":null,\"is_correct\":0}]', NULL, NULL, NULL, 1),
(11, 0, 8, '', 'f=?', '', '', '', '', '', NULL, NULL, 6, '2025-02-20 15:44:55', 0, '[{\"text\":\"f=ma\",\"image\":null,\"is_correct\":1},{\"text\":\"f=sa\",\"image\":null,\"is_correct\":0}]', NULL, NULL, NULL, 1),
(12, 0, 9, '', 'what is kernel', '', '', '', '', '', NULL, NULL, 15, '2025-02-21 15:00:59', 0, '[{\"text\":\"ok\",\"image\":null,\"is_correct\":0},{\"text\":\"no\",\"image\":null,\"is_correct\":1}]', 'question_images/67b8952beaa18_SRK_9526.JPG', NULL, NULL, 1),
(13, 0, 10, '', 'AWS S3', '', '', '', '', '', NULL, NULL, 15, '2025-02-21 15:14:15', 0, '[{\"text\":\"storage\",\"image\":null,\"is_correct\":1},{\"text\":\"Instance\",\"image\":null,\"is_correct\":0},{\"text\":\"Compute\",\"image\":null,\"is_correct\":0}]', 'question_images/67b89847d64bc_SRK_9526.JPG', NULL, NULL, 1),
(14, 0, 10, '', 'Azure', '', '', '', '', '', NULL, NULL, 15, '2025-02-21 15:19:16', 0, '[{\"text\":\"Cloud\",\"image\":\"question_images\\/67b899747aef9_WhatsApp Image 2025-02-20 at 10.16.54 PM.jpeg\",\"is_correct\":1},{\"text\":\"At on premise\",\"image\":\"question_images\\/67b899747c3f8_Signature_11zon.jpeg\",\"is_correct\":0}]', 'question_images/67b899747abf1_SRK_9531.JPG', NULL, NULL, 1),
(15, 0, 11, '', 'yml', '', '', '', '', '', NULL, NULL, 19, '2025-02-21 15:36:04', 0, '[{\"text\":\"fks\",\"image\":\"question_images\\/67b89d647cee5_IMG_9621.JPG\",\"is_correct\":1},{\"text\":\"trc\",\"image\":null,\"is_correct\":0}]', 'question_images/67b89d647c9e2_SRK_9531.JPG', NULL, NULL, 1),
(16, 0, 12, '', 'ITAct2000', '', '', '', '', '', NULL, NULL, 19, '2025-02-21 15:51:28', 0, '[{\"text\":\"ggd\",\"image\":\"question_images\\/67b8a10075b8b_SRK_9423.JPG\",\"is_correct\":1},{\"text\":\"ggf\",\"image\":null,\"is_correct\":0}]', 'question_images/67b8a100758f7_IMG_9660.JPG', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `question_setter_details`
--

CREATE TABLE `question_setter_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_setter_details`
--

INSERT INTO `question_setter_details` (`id`, `user_id`, `full_name`, `email`, `phone`, `created_at`) VALUES
(2, 7, 'somu69', 'somu.jana.9404@gmail.com', '88888565', '2025-02-19 15:04:41'),
(3, 17, 'sayan', 'sayan94043@gamail.com', '7427957898', '2025-02-20 15:43:26'),
(4, 18, 'Tony stark', 'tony@gmail.com', '9898856098', '2025-02-21 11:52:57'),
(6, 19, 'alfa', 'alfa@gmail.com', '9990909090', '2025-02-21 15:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_details`
--

CREATE TABLE `student_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_details`
--

INSERT INTO `student_details` (`id`, `user_id`, `full_name`, `email`, `phone`, `created_at`) VALUES
(4, 14, 'Sayan Jana', 'sayan.jana.9404@gmail.com', '7427957898', '2025-02-19 12:52:51'),
(6, 4, 'somu29', 'somu@gmail.com', '777777767', '2025-02-20 06:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_details`
--

CREATE TABLE `teacher_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_details`
--

INSERT INTO `teacher_details` (`id`, `user_id`, `full_name`, `email`, `phone`, `created_at`) VALUES
(4, 16, 'sayan25', 'sayan@gmail.com', '1234567891', '2025-02-20 15:26:55'),
(6, 15, 'jak', 'jak@123', '1232343450', '2025-02-21 15:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','question_setter','teacher','student') NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `created_at`, `deleted_at`, `updated_at`, `phone`) VALUES
(2, 'souvik25', '$2y$10$MLX1E5wtjEQL6WFWlApaouJhqzAe81XesnTuLFp8ON04iXgI49P0S', 'admin', 'souvik@gmail.com', '2025-02-15 15:45:06', NULL, NULL, NULL),
(4, 'somu29', '$2y$10$7iGB9Jx1ekzoopi55ZZvCO19Kb.sPa0tLtnZEvAn/QK/YO4v5jxXe', 'student', 'somu@gmail.com', '2025-02-16 15:37:51', NULL, '2025-02-20 06:49:57', '777777767'),
(6, 'sayan', '$2y$10$mIQCN.Ytl3SBrKb6flXYR.X1QBuVX.LBW4wwnivlMovBcBnug8b6a', 'question_setter', 'somu.jana.9404@gmail.com', '2025-02-18 14:58:43', NULL, NULL, NULL),
(7, 'somu69', '$2y$10$orBC8zcSaPb6IA7zDjzN4.SIZjv945HDteGDYiPo7Pp0t/1i7vI0q', 'question_setter', 'somu.jana.9404@gmail.com', '2025-02-18 15:02:13', NULL, '2025-02-19 15:04:41', '88888565'),
(14, 'Sayan Jana', '$2y$10$41No1sMsaUGS51brwF3BAeIqs.qPRULJ9yZZZpkd0u18YFK8gYEnW', 'student', 'sayan.jana.9404@gmail.com', '2025-02-19 12:52:51', NULL, NULL, '7427957898'),
(15, 'jak', '$2y$10$XgUtPGWkDrh07k8s12n4g.a2cBtgm3lr9xI91TUZSVLlb1lsmOb0y', 'teacher', 'jak@123', '2025-02-20 13:59:50', NULL, '2025-02-21 15:23:29', '1232343450'),
(16, 'sayan25', '$2y$10$XghL3rbgVYRn.VtJCw9Ybuusk3MI2uEXyy8CYz6Tw0vhkJtzvht..', 'teacher', 'sayan@gmail.com', '2025-02-20 15:26:55', NULL, NULL, '1234567891'),
(17, 'sayan', '$2y$10$ASkoY9VHW/GkBpFrbQ5mmOEfnnBCh5lrkpi/P4hySjyCEhIr82yqu', 'question_setter', 'sayan94043@gamail.com', '2025-02-20 15:43:26', NULL, NULL, '7427957898'),
(18, 'Tony stark', '$2y$10$bg.lezZqJlun/.TOL9oTYutep.1LDRrrqVdmBYW3U9cnEGprmBGpC', 'question_setter', 'tony@gmail.com', '2025-02-21 11:52:57', NULL, NULL, '9898856098'),
(19, 'alfa', '$2y$10$gm.3Zf0NnfWHNcKKnZkV5.wPtQYkRIScREn5ruwY.ygq5TwfuBRgq', 'question_setter', 'alfa@gmail.com', '2025-02-21 15:34:07', NULL, NULL, '9990909090');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `delete_question_settert_details_on_role_change` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.role = 'question_setter' AND NEW.role != 'question_setter' THEN
        DELETE FROM question_setter_details WHERE user_id = OLD.id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_student_details_on_role_change` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.role = 'student' AND NEW.role != 'student' THEN
        DELETE FROM student_details WHERE user_id = OLD.id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_teacher_details_on_role_change` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF OLD.role = 'teacher' AND NEW.role != 'teacher' THEN
        DELETE FROM teacher_details WHERE user_id = OLD.id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_question_setter` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'question_setter' THEN
        INSERT INTO question_setter_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_student` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'student' THEN
        INSERT INTO student_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_teacher` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'teacher' THEN
        INSERT INTO teacher_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_question_setter` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'question_setter' THEN
        INSERT INTO question_setter_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone); -- Ensure this matches the column name
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_student` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'student' THEN
        INSERT INTO student_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_teacher` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.role = 'teacher' THEN
        INSERT INTO teacher_details (user_id, full_name, email, phone)
        VALUES (NEW.id, NEW.username, NEW.email, NEW.phone); -- Ensure this matches the column name
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_exam_created_by` (`created_by`);

--
-- Indexes for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_exam_attempt` (`student_id`,`exam_id`,`attempt_date`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_exam_question` (`exam_id`,`question_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_attempt_id` (`exam_attempt_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapter_id` (`chapter_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_question_created_by` (`created_by`);

--
-- Indexes for table `question_setter_details`
--
ALTER TABLE `question_setter_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `idx_results_student_id` (`student_id`),
  ADD KEY `idx_results_exam_id` (`exam_id`);

--
-- Indexes for table `student_details`
--
ALTER TABLE `student_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `teacher_details`
--
ALTER TABLE `teacher_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `question_setter_details`
--
ALTER TABLE `question_setter_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_details`
--
ALTER TABLE `student_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `teacher_details`
--
ALTER TABLE `teacher_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`),
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `question_setter_details`
--
ALTER TABLE `question_setter_details`
  ADD CONSTRAINT `question_setter_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);

--
-- Constraints for table `student_details`
--
ALTER TABLE `student_details`
  ADD CONSTRAINT `student_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_details`
--
ALTER TABLE `teacher_details`
  ADD CONSTRAINT `teacher_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

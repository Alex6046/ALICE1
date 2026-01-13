-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost:8889
-- 生成日期： 2026-01-01 06:35:50
-- 服务器版本： 8.0.40
-- PHP 版本： 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `alice`
--

-- --------------------------------------------------------

--
-- 表的结构 `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `venue` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category` enum('upcoming','current','past') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Upcoming','Current','Passed') COLLATE utf8mb4_general_ci DEFAULT 'Upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date`, `time`, `venue`, `category`, `image`, `created_at`, `status`) VALUES
(1, 'AI Workshop', 'Hands-on session introducing AI concepts and programming techniques', '2025-11-21', NULL, 'UTM Innovation Lab', NULL, NULL, '2025-11-20 19:45:24', 'Current'),
(2, 'Annual Club Meetup', 'Annual gathering for all members to discuss achievements and plans', '2026-01-09', NULL, 'UTM Student Center Hall', NULL, NULL, '2025-11-20 20:01:47', 'Upcoming'),
(3, 'Guest Lecture: Cybersecurity Trends', 'Lecture by industry expert on emerging cybersecurity threats and defenses', '2026-01-04', NULL, 'UTM Lecture Hall A', NULL, NULL, '2025-11-20 21:58:17', 'Upcoming'),
(4, 'Hackathon Challenge', '24-hour coding competition for students to develop innovative solutions', '2025-10-17', NULL, 'Computer Lab 3, N28', NULL, NULL, '2025-11-20 21:59:02', 'Passed'),
(5, 'TEST Reminder 30min', 'Testing 30-minute reminder email', '2026-01-01', '13:23:00', 'Lab / Online', NULL, NULL, '2026-01-01 04:56:51', 'Upcoming'),
(6, 'TEST Reminder 30min', 'Testing 30-minute reminder email', '2026-01-01', '13:25:21', 'Lab / Online', NULL, NULL, '2026-01-01 05:15:21', 'Upcoming'),
(7, 'TEST Reminder 30min (New)', 'Testing 30-minute reminder email (new event)', '2026-01-01', '13:36:33', 'Lab / Online', NULL, NULL, '2026-01-01 05:26:33', 'Upcoming'),
(8, 'TEST - Confirm + 30min Reminder', 'Testing registration confirmation email and 30-minute reminder email', '2026-01-01', '14:25:00', 'Lab / Online', NULL, NULL, '2026-01-01 05:46:24', 'Upcoming'),
(9, 'TEST BOTH Emails (Register + 30min)', 'Testing confirmation + 30-minute reminder', '2026-01-01', '14:20:00', 'Lab / Online', NULL, NULL, '2026-01-01 05:54:55', 'Upcoming');

-- --------------------------------------------------------

--
-- 表的结构 `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `username`, `email`, `created_at`) VALUES
(1, 1, 'az', 'azryfikri6046@gmail.com', '2025-11-20 20:43:00'),
(2, 2, 'az', 'azryfikri6046@gmail.com', '2025-11-20 20:43:32'),
(3, 3, 'az', 'azryfikri6046@gmail.com', '2025-11-21 14:52:34'),
(4, 3, 'ad', 'adrianamunirah@gmail.com', '2025-11-27 15:37:56'),
(10, 3, 'WL', 'liweim427@gmail.com', '2026-01-01 05:44:02'),
(11, 8, 'WL', 'liweim427@gmail.com', '2026-01-01 05:46:43'),
(12, 9, 'WL', 'liweim427@gmail.com', '2026-01-01 05:55:06');

-- --------------------------------------------------------

--
-- 表的结构 `event_reminders`
--

CREATE TABLE `event_reminders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `reminder_minutes` int NOT NULL DEFAULT '30',
  `send_at` datetime NOT NULL,
  `status` enum('pending','sent','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remind_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 转存表中的数据 `event_reminders`
--

INSERT INTO `event_reminders` (`id`, `user_id`, `event_id`, `reminder_minutes`, `send_at`, `status`, `created_at`, `remind_at`) VALUES
(1, 21, 9, 30, '2026-01-01 13:50:00', 'pending', '2026-01-01 05:55:06', '2026-01-01 13:50:00');

-- --------------------------------------------------------

--
-- 表的结构 `highlights`
--

CREATE TABLE `highlights` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved') COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `highlights`
--

INSERT INTO `highlights` (`id`, `title`, `image`, `created_at`, `status`) VALUES
(2, NULL, '1764079688_download.jpg', '2025-11-25 14:08:08', 'approved'),
(3, NULL, '1764080282_download.jpg', '2025-11-25 14:18:02', 'approved');

-- --------------------------------------------------------

--
-- 表的结构 `proposed_events`
--

CREATE TABLE `proposed_events` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `venue` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `capacity` int NOT NULL,
  `organizer_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `organizer_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `proposed_events`
--

INSERT INTO `proposed_events` (`id`, `title`, `date`, `time`, `venue`, `description`, `capacity`, `organizer_name`, `organizer_email`, `contact_number`, `status`, `submitted_at`, `admin_notes`) VALUES
(1, 'Hack The Box', '2025-12-06', '08:30:00', 'Hyflex Classroom, N28A', 'A beginner-friendly session to explore cybersecurity, ethical hacking & the HTB platform.', 40, 'Adriana', 'adrianamunirah@gmail.com', '', 'pending', '2025-11-28 07:11:21', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `verification_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'user',
  `last_active` timestamp NULL DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `verification_code`, `is_verified`, `created_at`, `reset_code`, `reset_expiry`, `role`, `last_active`, `last_login`) VALUES
(2, 'azry', 'azry', 'azrifikriiskandar@gmail.com', '$2y$10$guSkheTZtlKMWqF2DLDArODS8v/Et.7BqCbBySDyG.jglNBWEDm5e', '345895', 1, '2025-11-11 11:21:26', NULL, NULL, 'admin', NULL, '2025-11-21 03:59:05'),
(13, 'azry', 'az', 'azryfikri6046@gmail.com', '$2y$10$qIlvqj49mlmqJWGOKTo2fu..1IqDPbWO4fIS6TrkZDIL9sReCGraC', NULL, 1, '2025-11-20 08:30:02', NULL, NULL, 'user', NULL, NULL),
(15, 'sas', 'sas', 'azryfikriiskandar@graduate.utm.my', '$2y$10$WTWDpxWlk2eYu4vzxliZcu57kRDXAzoBQa1d92ZJ23Q.qb1dntkLW', '977073', 0, '2025-11-20 21:46:25', NULL, NULL, 'user', NULL, NULL),
(16, 'adriana', 'ad', 'adrianamunirah@gmail.com', '$2y$10$yvkWCc7z/PmUjhs9r5i3Venao.Uo8bePDUpPrHpRbdCg5UaXry0nm', NULL, 1, '2025-11-27 15:25:28', NULL, NULL, 'user', NULL, NULL),
(17, 'LIWEI', 'LW', 'maliwei@graduate.utm.my', '$2y$10$/wFEfIvBWESQXo82YbTjzeyU2CceJgR9yC6EBzexsW943a9R.TOti', NULL, 1, '2025-12-27 06:40:52', NULL, NULL, 'admin', NULL, NULL),
(21, 'MARY', 'WL', 'liweim427@gmail.com', '$2y$10$EVONfbY2eykZj7KbLbTTfuBIjA51ZxV16cU4apRiZwRTdNiStaBvu', NULL, 1, '2025-12-27 08:37:04', NULL, NULL, 'user', NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `user_profile`
--

CREATE TABLE `user_profile` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `matric_number` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year_of_study` int DEFAULT NULL,
  `course` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user_profile`
--

INSERT INTO `user_profile` (`id`, `user_id`, `full_name`, `matric_number`, `gender`, `year_of_study`, `course`) VALUES
(2, 13, 'azry fikri', 'b24cs0009', 'Male', 3, 'secrh');

--
-- 转储表的索引
--

--
-- 表的索引 `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_event` (`user_id`,`event_id`),
  ADD UNIQUE KEY `uniq_event_user_remind` (`event_id`,`user_id`,`remind_at`),
  ADD KEY `idx_send_at` (`send_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_event` (`event_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- 表的索引 `highlights`
--
ALTER TABLE `highlights`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `proposed_events`
--
ALTER TABLE `proposed_events`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `event_reminders`
--
ALTER TABLE `event_reminders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `highlights`
--
ALTER TABLE `highlights`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `proposed_events`
--
ALTER TABLE `proposed_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用表AUTO_INCREMENT `user_profile`
--
ALTER TABLE `user_profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 限制导出的表
--

--
-- 限制表 `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

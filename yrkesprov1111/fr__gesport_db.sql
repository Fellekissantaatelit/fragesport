-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 12:49 PM
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
-- Database: `frågesport db`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `Achv_Id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_name`) VALUES
(1, 'Test2'),
(3, 'test2');

-- --------------------------------------------------------

--
-- Table structure for table `class_exercises`
--

CREATE TABLE `class_exercises` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_exercises`
--

INSERT INTO `class_exercises` (`id`, `class_id`, `exercise_id`, `assigned_at`) VALUES
(46, 1, 58, '2025-12-05 10:32:48'),
(47, 1, 57, '2025-12-05 10:32:54'),
(48, 1, 55, '2025-12-05 10:33:04'),
(53, 1, 63, '2025-12-05 11:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `Exercise_Id` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Type` enum('true_false','mcq','match','ordering','fill_blank') NOT NULL,
  `Max_XP` int(11) NOT NULL,
  `Is_Template` tinyint(1) DEFAULT 0,
  `Created_By` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`Exercise_Id`, `Title`, `Description`, `Type`, `Max_XP`, `Is_Template`, `Created_By`) VALUES
(54, 'Djurquiz 1', 'Sant/Falskt om djur.', 'true_false', 25, 1, NULL),
(55, 'Väderfakta', 'Testa dina väderkunskaper!', 'true_false', 25, 1, NULL),
(56, 'Historiska fakta', 'Hur mycket historia kan du?', 'true_false', 25, 1, NULL),
(57, 'Geografi Quiz 1', 'Testa din kunskap om världen.', 'mcq', 30, 1, NULL),
(58, 'Mat & Dryck Test', 'Vad vet du om mat och dryck?', 'mcq', 30, 1, NULL),
(59, 'Djurarter Test', 'Vilket djur är vad?', 'mcq', 30, 1, NULL),
(60, 'Morgondagen', 'Ordna stegen i rätt ordning.', 'ordering', 25, 1, NULL),
(63, 'Recept: Pannkakor', 'Ordna receptet steg för steg.', 'ordering', 25, 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `exercise_questions`
--

CREATE TABLE `exercise_questions` (
  `Question_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Statement` text NOT NULL,
  `Correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise_questions`
--

INSERT INTO `exercise_questions` (`Question_Id`, `Exercise_Id`, `Statement`, `Correct`) VALUES
(130, 54, 'En elefant kan väga över 5000 kg.', 1),
(131, 54, 'En snigel kan springa 20 km/h.', 0),
(132, 54, 'Delfiner är däggdjur.', 1),
(133, 55, 'Tornados kan förekomma i Sverige.', 1),
(134, 55, 'Regn bildas när luften blir varmare.', 0),
(135, 55, 'Snö kan falla vid temperaturer över 0°C.', 0),
(136, 56, 'Vikingar bar horn på sina hjälmar.', 0),
(137, 56, 'Pyramiderna i Egypten är över 4000 år gamla.', 1),
(138, 56, 'Den första mobiltelefonen kom på 1990-talet.', 0),
(139, 57, 'Vilket är världens största hav?', 0),
(140, 57, 'Vilket land har flest invånare?', 0),
(141, 57, 'Vilken är Europas längsta flod?', 0),
(142, 58, 'Vilken av dessa är en italiensk maträtt?', 0),
(143, 58, 'Vilken dryck innehåller koffein?', 0),
(144, 59, 'Vilket djur är ett däggdjur?', NULL),
(145, 59, 'Vilket djur kan flyga?', NULL),
(146, 60, 'Steg i morgonrutinen', 1),
(158, 63, 'Först ägg', 1),
(159, 63, 'sedan mjölk', 2),
(160, 63, 'till sist mjöl', 3);

-- --------------------------------------------------------

--
-- Table structure for table `experience_levels`
--

CREATE TABLE `experience_levels` (
  `Level_Id` int(11) NOT NULL,
  `Level_Name` varchar(50) DEFAULT NULL,
  `XP_Required` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experience_levels`
--

INSERT INTO `experience_levels` (`Level_Id`, `Level_Name`, `XP_Required`) VALUES
(1, 'Beginner', 0),
(2, 'Intermediate', 100),
(3, 'Advanced', 300),
(4, 'Master', 600);

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `Option_Id` int(11) NOT NULL,
  `Question_Id` int(11) NOT NULL,
  `Option_Text` varchar(255) NOT NULL,
  `Is_Correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`Option_Id`, `Question_Id`, `Option_Text`, `Is_Correct`) VALUES
(91, 139, 'Stilla havet', 1),
(92, 139, 'Atlanten', 0),
(93, 139, 'Indiska oceanen', 0),
(94, 140, 'Kina', 1),
(95, 140, 'Indien', 0),
(96, 140, 'USA', 0),
(97, 141, 'Volga', 1),
(98, 141, 'Donau', 0),
(99, 141, 'Rhen', 0),
(100, 142, 'Lasagne', 1),
(101, 142, 'Sushi', 0),
(102, 142, 'Tacos', 0),
(103, 143, 'Kaffe', 1),
(104, 143, 'Mjölk', 0),
(105, 143, 'Apelsinjuice', 0),
(106, 144, 'Haj', 0),
(107, 144, 'Hund', 1),
(108, 144, 'Groda', 0),
(109, 145, 'Häst', 0),
(110, 145, 'Örn', 1),
(111, 145, 'Pingvin', 0),
(112, 146, 'Vakna', 1),
(113, 146, 'Äta frukost', 2),
(114, 146, 'Gå till skolan', 3);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'student'),
(2, 'teacher'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_classes`
--

CREATE TABLE `teacher_classes` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_classes`
--

INSERT INTO `teacher_classes` (`id`, `teacher_id`, `class_id`) VALUES
(1, 4, 1),
(3, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `xp` int(11) DEFAULT 0,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `email`, `password`, `role_id`, `xp`, `class_id`, `created_at`) VALUES
(2, 'Admin', 'admin@g.fi', '$2y$10$9O/r042xv477DkWp2twlz.wH5ovc5/0p1PaHYF3l1IbJV.Mt7.t9u', 3, 0, NULL, '2025-11-11 10:10:21'),
(3, 'TestElev', 'TestElev@test.fi', '$2y$10$Xyp7Cc8BBLuFKe9ObZy1v.mGzDZqG2vrKwjcvqbeYI9pHiHhgrD0G', 1, 1280, 1, '2025-11-14 11:44:03'),
(4, 'TestTeacher', 'Testteacher@test.fi', '$2a$12$KasIO3TzmDi6rvq01GT.4unnJbJ4rIQ4HlRLzGRDNk4O663Zha7Wa', 2, 0, 1, '2025-11-20 09:03:27'),
(5, 'TestTeacher2', 'testteacher@2.fi', '$2a$12$X7KUnC3UcfSQIwWVtgqiQuyjKjnvqsayX/YYLlzGg2Rha9tdcRFH6', 2, 0, 2, '2025-11-20 09:51:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `User_Id` int(11) NOT NULL,
  `Achv_Id` int(11) NOT NULL,
  `Earned_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_results`
--

CREATE TABLE `user_results` (
  `Result_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Exercise_Id` int(11) NOT NULL,
  `Score` int(11) DEFAULT 0,
  `Completed` tinyint(1) DEFAULT 0,
  `Completed_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_results`
--

INSERT INTO `user_results` (`Result_Id`, `User_Id`, `Exercise_Id`, `Score`, `Completed`, `Completed_At`) VALUES
(68, 3, 55, 25, 1, '2025-12-05 11:11:16'),
(69, 3, 55, 0, 0, '2025-12-05 11:14:26'),
(70, 3, 57, 0, 0, '2025-12-05 11:17:04'),
(71, 3, 57, 0, 0, '2025-12-05 11:17:14'),
(72, 3, 57, 0, 0, '2025-12-05 11:17:31'),
(73, 3, 57, 0, 0, '2025-12-05 11:17:41'),
(74, 3, 57, 0, 0, '2025-12-05 11:17:50'),
(75, 3, 57, 30, 1, '2025-12-05 11:17:57'),
(76, 3, 55, 0, 0, '2025-12-05 11:18:07'),
(77, 3, 55, 0, 0, '2025-12-05 11:37:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`Achv_Id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `class_exercises`
--
ALTER TABLE `class_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`Exercise_Id`),
  ADD KEY `Created_By` (`Created_By`);

--
-- Indexes for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  ADD PRIMARY KEY (`Question_Id`),
  ADD KEY `Exercise_Id` (`Exercise_Id`);

--
-- Indexes for table `experience_levels`
--
ALTER TABLE `experience_levels`
  ADD PRIMARY KEY (`Level_Id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`Option_Id`),
  ADD KEY `Question_Id` (`Question_Id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`User_Id`,`Achv_Id`),
  ADD KEY `Achv_Id` (`Achv_Id`);

--
-- Indexes for table `user_results`
--
ALTER TABLE `user_results`
  ADD PRIMARY KEY (`Result_Id`),
  ADD KEY `User_Id` (`User_Id`),
  ADD KEY `Exercise_Id` (`Exercise_Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `Achv_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `class_exercises`
--
ALTER TABLE `class_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `Exercise_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  MODIFY `Question_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `experience_levels`
--
ALTER TABLE `experience_levels`
  MODIFY `Level_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `Option_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_results`
--
ALTER TABLE `user_results`
  MODIFY `Result_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_exercises`
--
ALTER TABLE `class_exercises`
  ADD CONSTRAINT `class_exercises_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_exercises_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;

--
-- Constraints for table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `exercises_ibfk_1` FOREIGN KEY (`Created_By`) REFERENCES `users` (`u_id`) ON DELETE SET NULL;

--
-- Constraints for table `exercise_questions`
--
ALTER TABLE `exercise_questions`
  ADD CONSTRAINT `exercise_questions_ibfk_1` FOREIGN KEY (`Exercise_Id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`Question_Id`) REFERENCES `exercise_questions` (`Question_Id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD CONSTRAINT `teacher_classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`Achv_Id`) REFERENCES `achievements` (`Achv_Id`) ON DELETE CASCADE;

--
-- Constraints for table `user_results`
--
ALTER TABLE `user_results`
  ADD CONSTRAINT `user_results_ibfk_1` FOREIGN KEY (`User_Id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_results_ibfk_2` FOREIGN KEY (`Exercise_Id`) REFERENCES `exercises` (`Exercise_Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

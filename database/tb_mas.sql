-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 09:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tb_mas`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `contact_code` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('M','F','Unknown') DEFAULT 'Unknown',
  `relationship` varchar(100) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `screening_result` enum('pending','negative','positive') DEFAULT 'pending',
  `status` enum('monitoring','converted_patient','cleared') DEFAULT 'monitoring',
  `converted_patient_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `import_logs`
--

CREATE TABLE `import_logs` (
  `import_id` int(11) NOT NULL,
  `filename` varchar(200) DEFAULT NULL,
  `rows_imported` int(11) DEFAULT NULL,
  `imported_by` int(11) DEFAULT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `drugs` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `is_sent` tinyint(4) DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `patient_code` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('M','F','Unknown') DEFAULT 'Unknown',
  `barangay` varchar(100) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `tb_case_number` varchar(80) DEFAULT NULL,
  `bacteriological_status` enum('BC','CD','Unknown') DEFAULT 'Unknown',
  `anatomical_site` enum('P','EP','Unknown') DEFAULT 'Unknown',
  `drug_susceptibility` enum('DS','DR','Unknown') DEFAULT 'Unknown',
  `treatment_history` enum('New','Retreatment','Unknown') DEFAULT 'Unknown',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `patient_code`, `age`, `sex`, `barangay`, `contact_number`, `tb_case_number`, `bacteriological_status`, `anatomical_site`, `drug_susceptibility`, `treatment_history`, `created_by`, `created_at`) VALUES
(7, NULL, 'TB-2025-0002', 46, 'F', 'Camp 7', '09179876543', 'TB20250002', 'Unknown', 'EP', 'Unknown', 'Retreatment', NULL, '2025-11-16 20:44:08'),
(8, 9, 'TB-2025-0003', 29, 'M', 'BGH Compound', '09175559999', 'TB20250003', 'CD', 'P', 'DR', 'New', NULL, '2025-11-16 20:44:11'),
(9, NULL, 'TB-2025-0004', 52, 'F', 'Pacdal', '09981234568', 'TB20250004', 'BC', 'EP', 'DS', 'Unknown', NULL, '2025-11-16 20:44:12'),
(10, 10, 'TB-2025-0005', 42, 'M', 'Loakan Proper', '09181231234', 'TB20250005', 'Unknown', 'P', 'Unknown', 'Retreatment', NULL, '2025-11-16 20:44:14'),
(35, 31, 'TB-2025-0001', 34, 'M', 'Camp 7', '09171234567', 'TB20250001', 'BC', 'P', 'DS', 'New', NULL, '2025-11-23 19:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `referral_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `referral_date` date DEFAULT NULL,
  `referring_unit` varchar(150) DEFAULT NULL,
  `referring_tel` varchar(50) DEFAULT NULL,
  `referring_email` varchar(150) DEFAULT NULL,
  `referring_address` text DEFAULT NULL,
  `reason_for_referral` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `receiving_unit` varchar(150) DEFAULT NULL,
  `receiving_officer` varchar(150) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receiving_barangay` varchar(255) DEFAULT NULL,
  `referral_status` enum('pending','received','completed') DEFAULT 'pending',
  `received_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `referral_code` varchar(50) DEFAULT NULL,
  `tb_case_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','health_worker','patient') NOT NULL,
  `barangay_assigned` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `verification_token` varchar(150) DEFAULT NULL,
  `password_reset_required` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `barangay_assigned`, `is_verified`, `verification_token`, `password_reset_required`, `created_at`) VALUES
(1, 'admin@tbmas.local', '$2y$10$48UlobAD8rDeDLkp7/TYVOFq6nreLLNcMhyzKInyYTLmpZoAct6hu', 'super_admin', NULL, 1, NULL, 0, '2025-11-16 09:24:13'),
(9, 'testpatient3@example.com', '$2y$10$YLMaQoxy.M.TlglQ3QIinuHJLfwOhjFRCANC8EyO5muCoVdS38qN2', 'patient', NULL, 0, 'df0a3ce39041b2b601de6fd90db60b9a', 1, '2025-11-16 20:44:08'),
(10, 'testpatient5@example.com', '$2y$10$txt5oI43DS1mi4HkeMcjbubr6NCgOs2yXH8USUkE561g5HLYMbgzW', 'patient', NULL, 0, 'c643bc0c0fc333c18b67e484a2227421', 1, '2025-11-16 20:44:12'),
(28, 'tysalango@gmail.com', '$2y$10$qGLcKVSImt10/oejlIsi4el7LnAgvbpjjoKs4AFsNdFmHbSNM63C6', 'health_worker', 'Pacdal', 1, NULL, 0, '2025-11-23 11:41:07'),
(29, 'tyronepaladin@gmail.com', '$2y$10$chY8Bj2Kzt/0rZF1ewt5C.42eGbyZtEk/Soa8LrvPaT0xZ8jSGile', 'health_worker', 'Camp 7', 1, NULL, 0, '2025-11-23 11:46:06'),
(31, '20162396@s.ubaguio.edu', '$2y$10$0eWJR7OT33juyxfCw2Z2DOcN6kybyk3N/KzKS.lp2YKpHAJaE7pnu', 'patient', NULL, 1, NULL, 0, '2025-11-23 13:10:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD UNIQUE KEY `contact_code` (`contact_code`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `converted_patient_id` (`converted_patient_id`);

--
-- Indexes for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`import_id`),
  ADD KEY `imported_by` (`imported_by`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_patient` (`patient_id`),
  ADD KEY `idx_notifications_schedule` (`is_sent`,`scheduled_at`),
  ADD KEY `idx_notifications_type` (`type`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `patient_code` (`patient_code`),
  ADD KEY `idx_patients_user` (`user_id`),
  ADD KEY `idx_patients_code` (`patient_code`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`referral_id`),
  ADD KEY `idx_referrals_status` (`referral_status`),
  ADD KEY `idx_referrals_patient` (`patient_id`),
  ADD KEY `idx_referrals_receiver` (`receiving_barangay`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `import_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `referral_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`converted_patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE SET NULL;

--
-- Constraints for table `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_ibfk_1` FOREIGN KEY (`imported_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 04:15 AM
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
-- Database: `medis`
--

-- --------------------------------------------------------

--
-- Table structure for table `biological_monitoring`
--

CREATE TABLE `biological_monitoring` (
  `bioMonitor_id` int(11) NOT NULL,
  `biological_exposure` enum('Yes','No') DEFAULT NULL,
  `baseline_results` text DEFAULT NULL,
  `baseline_annual` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chemical_information`
--

CREATE TABLE `chemical_information` (
  `surveillance_id` int(11) NOT NULL,
  `chemicals` text DEFAULT NULL,
  `examination_type` enum('Pre-Placement','Periodic','Return to Work','Exit') DEFAULT NULL,
  `examination_date` date DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinic`
--

CREATE TABLE `clinic` (
  `clinic_id` int(11) NOT NULL,
  `clinic_name` varchar(150) NOT NULL,
  `clinic_address` varchar(255) DEFAULT NULL,
  `clinic_postcode` varchar(10) DEFAULT NULL,
  `clinic_district` varchar(100) DEFAULT NULL,
  `clinic_state` varchar(100) DEFAULT NULL,
  `clinic_telephone` varchar(30) DEFAULT NULL,
  `clinic_fax` varchar(30) DEFAULT NULL,
  `clinic_email` varchar(150) DEFAULT NULL,
  `clinic_username` varchar(100) DEFAULT NULL,
  `clinic_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinical_findings`
--

CREATE TABLE `clinical_findings` (
  `chHistory_id` int(11) NOT NULL,
  `result_clinical_findings` enum('Yes','No') DEFAULT NULL,
  `elaboration` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `mykpp_registration_no` varchar(100) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_postcode` varchar(10) DEFAULT NULL,
  `company_district` varchar(100) DEFAULT NULL,
  `company_state` varchar(100) DEFAULT NULL,
  `company_telephone` varchar(30) DEFAULT NULL,
  `company_email` varchar(150) DEFAULT NULL,
  `company_fax` varchar(30) DEFAULT NULL,
  `total_workers` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `declaration`
--

CREATE TABLE `declaration` (
  `declaration_id` int(11) NOT NULL,
  `employee_signature` text DEFAULT NULL,
  `employee_date` date DEFAULT NULL,
  `doctor_signature` text DEFAULT NULL,
  `doctor_date` date DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `employee_firstName` varchar(100) DEFAULT NULL,
  `employee_lastName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_id` int(11) NOT NULL,
  `doctor_firstName` varchar(100) NOT NULL,
  `doctor_lastName` varchar(100) NOT NULL,
  `doctor_NRIC` varchar(20) DEFAULT NULL,
  `doctor_passportNo` varchar(30) DEFAULT NULL,
  `doctor_DOB` date DEFAULT NULL,
  `doctor_gender` varchar(20) DEFAULT NULL,
  `doctor_address` varchar(255) DEFAULT NULL,
  `doctor_postcode` varchar(10) DEFAULT NULL,
  `doctor_district` varchar(100) DEFAULT NULL,
  `doctor_state` varchar(100) DEFAULT NULL,
  `doctor_telephone` varchar(30) DEFAULT NULL,
  `doctor_fax` varchar(30) DEFAULT NULL,
  `doctor_email` varchar(150) DEFAULT NULL,
  `doctor_ethnicity` enum('Malay','Chinese','Indian','Orang Asli','Others') DEFAULT NULL,
  `doctor_citizenship` enum('Malaysian Citizen','Others') DEFAULT NULL,
  `doctor_martialStatus` enum('Single','Married') DEFAULT NULL,
  `MMC_no` varchar(50) DEFAULT NULL,
  `OHD_registrationNo` varchar(50) DEFAULT NULL,
  `doctor_username` varchar(100) DEFAULT NULL,
  `doctor_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `employee_firstName` varchar(100) NOT NULL,
  `employee_lastName` varchar(100) NOT NULL,
  `employee_NRIC` varchar(20) DEFAULT NULL,
  `employee_passportNo` varchar(30) DEFAULT NULL,
  `employee_DOB` date DEFAULT NULL,
  `employee_gender` enum('Male','Female') DEFAULT NULL,
  `employee_address` varchar(255) DEFAULT NULL,
  `employee_postcode` varchar(10) DEFAULT NULL,
  `employee_district` varchar(100) DEFAULT NULL,
  `employee_state` varchar(100) DEFAULT NULL,
  `employee_telephone` varchar(30) DEFAULT NULL,
  `employee_email` varchar(150) DEFAULT NULL,
  `employee_ethnicity` enum('Malay','Chinese','Indian','Orang Asli','Others') DEFAULT NULL,
  `employee_citizenship` enum('Malaysian Citizen','Others') DEFAULT NULL,
  `employee_martialStatus` enum('Single','Married','Others') DEFAULT NULL,
  `no_of_children` int(11) DEFAULT 0,
  `years_married` int(11) DEFAULT 0,
  `employee_sign` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fitness_respirator`
--

CREATE TABLE `fitness_respirator` (
  `fitness_id` int(11) NOT NULL,
  `fitness_result` enum('Fit','Not Fit') DEFAULT NULL,
  `fitness_justification` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_of_health`
--

CREATE TABLE `history_of_health` (
  `hoh_id` int(11) NOT NULL,
  `breathing_difficulty` enum('Yes','No') NOT NULL DEFAULT 'No',
  `cough` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sore_throat` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sneezing` enum('Yes','No') NOT NULL DEFAULT 'No',
  `chest_pain` enum('Yes','No') NOT NULL DEFAULT 'No',
  `palpitation` enum('Yes','No') NOT NULL DEFAULT 'No',
  `limb_oedema` enum('Yes','No') NOT NULL DEFAULT 'No',
  `drowsiness` enum('Yes','No') NOT NULL DEFAULT 'No',
  `dizziness` enum('Yes','No') NOT NULL DEFAULT 'No',
  `headache` enum('Yes','No') NOT NULL DEFAULT 'No',
  `confusion` enum('Yes','No') NOT NULL DEFAULT 'No',
  `lethargy` enum('Yes','No') NOT NULL DEFAULT 'No',
  `nausea` enum('Yes','No') NOT NULL DEFAULT 'No',
  `vomiting` enum('Yes','No') NOT NULL DEFAULT 'No',
  `eye_irritations` enum('Yes','No') NOT NULL DEFAULT 'No',
  `blurred_vision` enum('Yes','No') NOT NULL DEFAULT 'No',
  `blisters` enum('Yes','No') NOT NULL DEFAULT 'No',
  `burns` enum('Yes','No') NOT NULL DEFAULT 'No',
  `itching` enum('Yes','No') NOT NULL DEFAULT 'No',
  `rash` enum('Yes','No') NOT NULL DEFAULT 'No',
  `redness` enum('Yes','No') NOT NULL DEFAULT 'No',
  `abdominal_pain` enum('Yes','No') NOT NULL DEFAULT 'No',
  `abdominal_mass` enum('Yes','No') NOT NULL DEFAULT 'No',
  `jaundice` enum('Yes','No') NOT NULL DEFAULT 'No',
  `diarrhoea` enum('Yes','No') NOT NULL DEFAULT 'No',
  `loss_of_weight` enum('Yes','No') NOT NULL DEFAULT 'No',
  `loss_of_appetite` enum('Yes','No') NOT NULL DEFAULT 'No',
  `dysuria` enum('Yes','No') NOT NULL DEFAULT 'No',
  `haematuria` enum('Yes','No') NOT NULL DEFAULT 'No',
  `others_symptoms` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `medHistory_id` int(11) NOT NULL,
  `diagnosed_history` text DEFAULT NULL,
  `medication_history` text DEFAULT NULL,
  `admitted_history` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `others_history` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ms_findings`
--

CREATE TABLE `ms_findings` (
  `msFindings_id` int(11) NOT NULL,
  `history_of_health` enum('Yes','No') DEFAULT NULL,
  `clinical_findings` enum('Yes','No') DEFAULT NULL,
  `CF_work_related` enum('Yes','No') DEFAULT NULL,
  `target_organ` enum('Yes','No') DEFAULT NULL,
  `TO_work_related` enum('Yes','No') DEFAULT NULL,
  `biological_monitoring` enum('Yes','No') DEFAULT NULL,
  `BM_work_related` enum('Yes','No') DEFAULT NULL,
  `pregnancy_breastFeding` enum('Yes','No') DEFAULT NULL,
  `conclusion_fitness` enum('Fit','Not Fit') DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `occupational_history`
--

CREATE TABLE `occupational_history` (
  `occupHistory_id` int(11) NOT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `employment_duration` varchar(100) DEFAULT NULL,
  `chemical_exposure_duration` varchar(100) DEFAULT NULL,
  `chemical_exposure_incidents` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_social_history`
--

CREATE TABLE `personal_social_history` (
  `perSocHistory_id` int(11) NOT NULL,
  `smoking_history` enum('Current','Ex-smoker','Non-smoker') DEFAULT NULL,
  `years_of_smoking` int(11) DEFAULT 0,
  `no_of_cigarettes` int(11) DEFAULT 0,
  `vaping_history` enum('Yes','No') DEFAULT NULL,
  `years_of_vaping` int(11) DEFAULT 0,
  `hobby` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `physical_examination`
--

CREATE TABLE `physical_examination` (
  `pexamHistory_id` int(11) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `BMI` decimal(5,2) DEFAULT NULL,
  `bp_systolic` int(11) DEFAULT NULL,
  `bp_distolic` int(11) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `general_appearances` text DEFAULT NULL,
  `s1_s2` enum('Yes','No') DEFAULT NULL,
  `murmur` enum('Yes','No') DEFAULT NULL,
  `ear_nose_throat` enum('Normal','Abnormal') DEFAULT NULL,
  `visual_acuity_right` varchar(50) DEFAULT NULL,
  `visual_acuity_left` varchar(50) DEFAULT NULL,
  `colour_blindness` enum('Yes','No') DEFAULT NULL,
  `gas_tenderness` enum('Yes','No') DEFAULT NULL,
  `abdominal_mass` enum('Yes','No') DEFAULT NULL,
  `lymph_nodes` enum('Palpable','Non-palpable') DEFAULT NULL,
  `splenomegaly` enum('Yes','No') DEFAULT NULL,
  `kidney_tenderness` enum('Yes','No') DEFAULT NULL,
  `ballotable` enum('Yes','No') DEFAULT NULL,
  `jaundice` enum('Yes','No') DEFAULT NULL,
  `hepatomegaly` enum('Yes','No') DEFAULT NULL,
  `muscle_tone` enum('1','2','3','4','5') DEFAULT NULL,
  `muscle_tenderness` enum('Yes','No') DEFAULT NULL,
  `power` enum('1','2','3','4','5') DEFAULT NULL,
  `sensation` enum('Normal','Abnormal') DEFAULT NULL,
  `sound` enum('Clear','Rhonchi','Crepitus') DEFAULT NULL,
  `air_entry` enum('Normal','Abnormal') DEFAULT NULL,
  `reproductive` enum('Normal','Abnormal') DEFAULT NULL,
  `skin` enum('Normal','Abnormal') DEFAULT NULL,
  `others` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendation`
--

CREATE TABLE `recommendation` (
  `recommendation_id` int(11) NOT NULL,
  `recommencation_type` varchar(100) DEFAULT NULL,
  `MRPdate_start` date DEFAULT NULL,
  `MRPdate_end` date DEFAULT NULL,
  `nextReview_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `summary_report`
--

CREATE TABLE `summary_report` (
  `summaryReport_id` int(11) NOT NULL,
  `totalNo_workplace` int(11) DEFAULT 0,
  `name_of_workUnit` varchar(150) DEFAULT NULL,
  `no_exposedWorkers` int(11) DEFAULT 0,
  `totalNo_examined` int(11) DEFAULT 0,
  `chemical_name` varchar(150) DEFAULT NULL,
  `CHRA_reportNo` varchar(100) DEFAULT NULL,
  `indication_CHRAreport` text DEFAULT NULL,
  `no_ofWorkersNormal_H` int(11) DEFAULT 0,
  `no_ofWorkersNormal_I` int(11) DEFAULT 0,
  `no_ofWorkersNormal_J` int(11) DEFAULT 0,
  `no_ofWorkersNormal_K` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_OccupationalH` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_OccupationalI` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_nonOccupationalI` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_OccupationalJ` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_nonOccupationalJ` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_OccupationalK` int(11) DEFAULT 0,
  `no_ofWorkersAbormal_nonOccupationalK` int(11) DEFAULT 0,
  `no_ofWorkersRecommended_I` int(11) DEFAULT 0,
  `no_ofWorkersRecommended_J` int(11) DEFAULT 0,
  `no_ofWorkersRecommended_K` int(11) DEFAULT 0,
  `specify_J` text DEFAULT NULL,
  `specify_K` text DEFAULT NULL,
  `totalNo_MRP` int(11) DEFAULT 0,
  `name_of_laboratoy` varchar(150) DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `decision` enum('Continue MS','Stop MS') DEFAULT NULL,
  `justification_decision` text DEFAULT NULL,
  `date_of_implementation` date DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `target_organ`
--

CREATE TABLE `target_organ` (
  `target_id` int(11) NOT NULL,
  `blood_count` enum('Normal','Abnormal') DEFAULT NULL,
  `blood_comments` text DEFAULT NULL,
  `renal_function` enum('Normal','Abnormal') DEFAULT NULL,
  `renal_comments` text DEFAULT NULL,
  `liver_function` enum('Normal','Abnormal') DEFAULT NULL,
  `liver_comments` text DEFAULT NULL,
  `chest_xray` enum('Normal','Abnormal') DEFAULT NULL,
  `chest_comments` text DEFAULT NULL,
  `spirometry_FEV1` decimal(8,2) DEFAULT NULL,
  `spirometry_FVC` decimal(8,2) DEFAULT NULL,
  `spirometry_FEV_FVC` decimal(8,2) DEFAULT NULL,
  `spirometry_comments` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_history`
--

CREATE TABLE `training_history` (
  `trainingHistory_id` int(11) NOT NULL,
  `handling_of_chemical` enum('Yes','No') DEFAULT NULL,
  `chemical_comments` text DEFAULT NULL,
  `sign_symptoms` enum('Yes','No') DEFAULT NULL,
  `sign_comments` text DEFAULT NULL,
  `chemical_poisoning` enum('Yes','No') DEFAULT NULL,
  `poisoning_comments` text DEFAULT NULL,
  `proper_PPE` enum('Yes','No') DEFAULT NULL,
  `proper_comments` text DEFAULT NULL,
  `PPE_usage` enum('Yes','No') DEFAULT NULL,
  `usage_comments` text DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Clinic') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `biological_monitoring`
--
ALTER TABLE `biological_monitoring`
  ADD PRIMARY KEY (`bioMonitor_id`),
  ADD KEY `fk_biomonitor_employee` (`employee_id`),
  ADD KEY `fk_biomonitor_surveillance` (`surveillance_id`);

--
-- Indexes for table `chemical_information`
--
ALTER TABLE `chemical_information`
  ADD PRIMARY KEY (`surveillance_id`),
  ADD KEY `fk_chemical_employee` (`employee_id`),
  ADD KEY `fk_chemical_doctor` (`doctor_id`);

--
-- Indexes for table `clinic`
--
ALTER TABLE `clinic`
  ADD PRIMARY KEY (`clinic_id`),
  ADD UNIQUE KEY `clinic_username` (`clinic_username`);

--
-- Indexes for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  ADD PRIMARY KEY (`chHistory_id`),
  ADD KEY `fk_clinical_employee` (`employee_id`),
  ADD KEY `fk_clinical_surveillance` (`surveillance_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `declaration`
--
ALTER TABLE `declaration`
  ADD PRIMARY KEY (`declaration_id`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `doctor_username` (`doctor_username`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `fitness_respirator`
--
ALTER TABLE `fitness_respirator`
  ADD PRIMARY KEY (`fitness_id`),
  ADD KEY `fk_fitness_employee` (`employee_id`),
  ADD KEY `fk_fitness_surveillance` (`surveillance_id`);

--
-- Indexes for table `history_of_health`
--
ALTER TABLE `history_of_health`
  ADD PRIMARY KEY (`hoh_id`),
  ADD KEY `fk_hoh_employee` (`employee_id`),
  ADD KEY `fk_hoh_surveillance` (`surveillance_id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`medHistory_id`),
  ADD KEY `fk_medhist_employee` (`employee_id`),
  ADD KEY `fk_medhist_surveillance` (`surveillance_id`);

--
-- Indexes for table `ms_findings`
--
ALTER TABLE `ms_findings`
  ADD PRIMARY KEY (`msFindings_id`),
  ADD KEY `fk_ms_employee` (`employee_id`),
  ADD KEY `fk_ms_surveillance` (`surveillance_id`);

--
-- Indexes for table `occupational_history`
--
ALTER TABLE `occupational_history`
  ADD PRIMARY KEY (`occupHistory_id`),
  ADD KEY `fk_occup_employee` (`employee_id`),
  ADD KEY `fk_occup_surveillance` (`surveillance_id`);

--
-- Indexes for table `personal_social_history`
--
ALTER TABLE `personal_social_history`
  ADD PRIMARY KEY (`perSocHistory_id`),
  ADD KEY `fk_persoc_employee` (`employee_id`),
  ADD KEY `fk_persoc_surveillance` (`surveillance_id`);

--
-- Indexes for table `physical_examination`
--
ALTER TABLE `physical_examination`
  ADD PRIMARY KEY (`pexamHistory_id`),
  ADD KEY `fk_pexam_employee` (`employee_id`),
  ADD KEY `fk_pexam_surveillance` (`surveillance_id`);

--
-- Indexes for table `recommendation`
--
ALTER TABLE `recommendation`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `fk_recommend_employee` (`employee_id`),
  ADD KEY `fk_recommend_surveillance` (`surveillance_id`);

--
-- Indexes for table `summary_report`
--
ALTER TABLE `summary_report`
  ADD PRIMARY KEY (`summaryReport_id`),
  ADD KEY `fk_summary_employee` (`employee_id`),
  ADD KEY `fk_summary_company` (`company_id`),
  ADD KEY `fk_summary_doctor` (`doctor_id`);

--
-- Indexes for table `target_organ`
--
ALTER TABLE `target_organ`
  ADD PRIMARY KEY (`target_id`),
  ADD KEY `fk_target_employee` (`employee_id`),
  ADD KEY `fk_target_surveillance` (`surveillance_id`);

--
-- Indexes for table `training_history`
--
ALTER TABLE `training_history`
  ADD PRIMARY KEY (`trainingHistory_id`),
  ADD KEY `fk_training_employee` (`employee_id`),
  ADD KEY `fk_training_surveillance` (`surveillance_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `biological_monitoring`
--
ALTER TABLE `biological_monitoring`
  MODIFY `bioMonitor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chemical_information`
--
ALTER TABLE `chemical_information`
  MODIFY `surveillance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clinic`
--
ALTER TABLE `clinic`
  MODIFY `clinic_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  MODIFY `chHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `declaration`
--
ALTER TABLE `declaration`
  MODIFY `declaration_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fitness_respirator`
--
ALTER TABLE `fitness_respirator`
  MODIFY `fitness_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_of_health`
--
ALTER TABLE `history_of_health`
  MODIFY `hoh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `medHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ms_findings`
--
ALTER TABLE `ms_findings`
  MODIFY `msFindings_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `occupational_history`
--
ALTER TABLE `occupational_history`
  MODIFY `occupHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_social_history`
--
ALTER TABLE `personal_social_history`
  MODIFY `perSocHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `physical_examination`
--
ALTER TABLE `physical_examination`
  MODIFY `pexamHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recommendation`
--
ALTER TABLE `recommendation`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `summary_report`
--
ALTER TABLE `summary_report`
  MODIFY `summaryReport_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `target_organ`
--
ALTER TABLE `target_organ`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_history`
--
ALTER TABLE `training_history`
  MODIFY `trainingHistory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `biological_monitoring`
--
ALTER TABLE `biological_monitoring`
  ADD CONSTRAINT `fk_biomonitor_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_biomonitor_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chemical_information`
--
ALTER TABLE `chemical_information`
  ADD CONSTRAINT `fk_chemical_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chemical_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `clinical_findings`
--
ALTER TABLE `clinical_findings`
  ADD CONSTRAINT `fk_clinical_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clinical_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fitness_respirator`
--
ALTER TABLE `fitness_respirator`
  ADD CONSTRAINT `fk_fitness_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fitness_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `history_of_health`
--
ALTER TABLE `history_of_health`
  ADD CONSTRAINT `fk_hoh_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoh_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `fk_medhist_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medhist_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ms_findings`
--
ALTER TABLE `ms_findings`
  ADD CONSTRAINT `fk_ms_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ms_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `occupational_history`
--
ALTER TABLE `occupational_history`
  ADD CONSTRAINT `fk_occup_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_occup_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `personal_social_history`
--
ALTER TABLE `personal_social_history`
  ADD CONSTRAINT `fk_persoc_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_persoc_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `physical_examination`
--
ALTER TABLE `physical_examination`
  ADD CONSTRAINT `fk_pexam_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pexam_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recommendation`
--
ALTER TABLE `recommendation`
  ADD CONSTRAINT `fk_recommend_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recommend_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `summary_report`
--
ALTER TABLE `summary_report`
  ADD CONSTRAINT `fk_summary_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_summary_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_summary_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `target_organ`
--
ALTER TABLE `target_organ`
  ADD CONSTRAINT `fk_target_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_target_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `training_history`
--
ALTER TABLE `training_history`
  ADD CONSTRAINT `fk_training_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_training_surveillance` FOREIGN KEY (`surveillance_id`) REFERENCES `chemical_information` (`surveillance_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

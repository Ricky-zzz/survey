-- ========================================
-- Survey Creator Database Schema
-- ========================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Create Database
DROP DATABASE IF EXISTS `survey`;
CREATE DATABASE IF NOT EXISTS `survey` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `survey`;

-- ========================================
-- Table: ADMINS
-- Purpose: Store admin users for survey management
-- ========================================
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create default admin (username: admin, password: admin123 - bcrypt hashed)
-- Note: Hash generated using PHP password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admins` (`username`, `password_hash`, `email`) VALUES 
('admin', '$2y$10$6I6e3wPfmLuBm6rFfLnBRe.p6sJFqMdWgZZz32H7lFB.Zz8pKPDvm', 'admin@survey.local');

-- ========================================
-- Table: SURVEYS
-- Purpose: Store survey metadata
-- ========================================
CREATE TABLE `surveys` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `description` text,
  `is_public` boolean DEFAULT true,
  `passkey` varchar(100) DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: SECTIONS
-- Purpose: Group questions within a survey
-- is_respondent_info: true = this is the default respondent info section
-- ========================================
CREATE TABLE `sections` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `survey_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `is_respondent_info` boolean DEFAULT false,
  `order_sequence` int NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: QUESTIONS
-- Purpose: Store survey questions
-- type: text | yesno | scale | multiple_choice | file_upload
-- required: true = respondent must answer
-- allow_multiple_files: true = can upload multiple files (only for file_upload type)
-- matrix_group_id: optional identifier to group scale questions into a matrix table
-- ========================================
CREATE TABLE `questions` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `section_id` int NOT NULL,
  `question_text` text NOT NULL,
  `type` enum('text', 'yesno', 'scale', 'multiple_choice', 'file_upload') NOT NULL,
  `required` boolean DEFAULT true,
  `allow_multiple_files` boolean DEFAULT false,
  `matrix_group_id` varchar(100) DEFAULT NULL,
  `order_sequence` int NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`section_id`) REFERENCES `sections`(`id`) ON DELETE CASCADE,
  INDEX `idx_matrix_group` (`matrix_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: QUESTION_OPTIONS
-- Purpose: Store options for multiple_choice and scale questions
-- For scale/likert: value = 1-5 (or any numeric value)
-- For multiple_choice: value can be sequential or descriptive
-- ========================================
CREATE TABLE `question_options` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `value` varchar(100) NOT NULL,
  `order_sequence` int NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: RESPONDENTS
-- Purpose: Track each survey submission
-- Each row = one person's survey submission (allows duplicate emails)
-- ========================================
CREATE TABLE `respondents` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `survey_id` int NOT NULL,
  `submitted_at` timestamp NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`id`) ON DELETE CASCADE,
  INDEX `idx_survey_submitted` (`survey_id`, `submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: RESPONSES
-- Purpose: Store individual question answers
-- answer_value: stores text, yes/no, scale value, or option_id
-- ========================================
CREATE TABLE `responses` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `respondent_id` int NOT NULL,
  `question_id` int NOT NULL,
  `answer_value` longtext,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`respondent_id`) REFERENCES `respondents`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_respondent_question` (`respondent_id`, `question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- Table: FILES
-- Purpose: Store file upload data
-- Tracks uploaded files for file_upload questions
-- allow_multiple_files determines if multiple rows per respondent_id+question_id are allowed
-- file_path: sanitized path like 'uploads/survey_1/respondent_5/hash_originalname.pdf'
-- original_filename: original filename for download display
-- ========================================
CREATE TABLE `files` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `respondent_id` int NOT NULL,
  `question_id` int NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `file_type` varchar(50) DEFAULT 'pdf',
  `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`respondent_id`) REFERENCES `respondents`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
  INDEX `idx_respondent_question` (`respondent_id`, `question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ========================================
-- SAMPLE DATA (Optional - for testing)
-- Uncomment to load sample survey
-- ========================================

-- Create a sample survey
INSERT INTO `surveys` (`name`, `description`, `is_public`, `created_by`) VALUES 
('Customer Feedback Survey', 'Help us improve our service', true, 1);

-- Create respondent info section (is_respondent_info = true)
INSERT INTO `sections` (`survey_id`, `title`, `description`, `is_respondent_info`, `order_sequence`) VALUES 
(1, 'Your Information', 'Please provide your details', true, 0);

-- Add respondent info questions (first_name, last_name, middle_name, email, sex, age)
INSERT INTO `questions` (`section_id`, `question_text`, `type`, `required`, `order_sequence`) VALUES 
(1, 'First Name', 'text', true, 1),
(1, 'Last Name', 'text', true, 2),
(1, 'Middle Name', 'text', false, 3),
(1, 'Email', 'text', true, 4),
(1, 'Sex', 'multiple_choice', true, 5),
(1, 'Age', 'text', false, 6);

-- Add sex options
INSERT INTO `question_options` (`question_id`, `option_text`, `value`, `order_sequence`) VALUES 
(5, 'Male', 'M', 1),
(5, 'Female', 'F', 2),
(5, 'Other', 'O', 3);

-- Create survey questions section
INSERT INTO `sections` (`survey_id`, `title`, `description`, `is_respondent_info`, `order_sequence`) VALUES 
(1, 'Service Feedback', 'Tell us about your experience', false, 1),
(1, 'Service Quality Matrix', 'Rate the following aspects of our service', false, 2);

-- Add survey questions (text, yesno, scale, multiple_choice, file_upload)
INSERT INTO `questions` (`section_id`, `question_text`, `type`, `required`, `order_sequence`) VALUES 
(2, 'How satisfied are you with our service?', 'scale', true, 1),
(2, 'Would you recommend us to others?', 'yesno', true, 2),
(2, 'What is your primary feedback?', 'text', false, 3),
(2, 'Which department helped you most?', 'multiple_choice', true, 4),
(2, 'Please upload your feedback document (PDF only)', 'file_upload', false, 5),
(3, 'Product Quality', 'scale', true, 1),
(3, 'Customer Service', 'scale', true, 2),
(3, 'Communication', 'scale', true, 3),
(3, 'Price Value', 'scale', true, 4),
(3, 'Timeliness of Delivery', 'scale', true, 5);

-- Update matrix questions with group ID (Product Quality through Timeliness of Delivery in section 3)
UPDATE `questions` SET `matrix_group_id` = 'service-quality' 
WHERE `section_id` = 3 AND `type` = 'scale';

-- Add scale options for single satisfaction question
INSERT INTO `question_options` (`question_id`, `option_text`, `value`, `order_sequence`) VALUES 
(7, 'Very Unsatisfied', '1', 1),
(7, 'Unsatisfied', '2', 2),
(7, 'Neutral', '3', 3),
(7, 'Satisfied', '4', 4),
(7, 'Very Satisfied', '5', 5);

-- Add scale options for matrix questions (12-16: Product Quality, Customer Service, Communication, Price Value, Timeliness)
INSERT INTO `question_options` (`question_id`, `option_text`, `value`, `order_sequence`) VALUES 
(12, 'Strongly Disagree', '1', 1),
(12, 'Disagree', '2', 2),
(12, 'Neutral', '3', 3),
(12, 'Agree', '4', 4),
(12, 'Strongly Agree', '5', 5),
(13, 'Strongly Disagree', '1', 1),
(13, 'Disagree', '2', 2),
(13, 'Neutral', '3', 3),
(13, 'Agree', '4', 4),
(13, 'Strongly Agree', '5', 5),
(14, 'Strongly Disagree', '1', 1),
(14, 'Disagree', '2', 2),
(14, 'Neutral', '3', 3),
(14, 'Agree', '4', 4),
(14, 'Strongly Agree', '5', 5),
(15, 'Strongly Disagree', '1', 1),
(15, 'Disagree', '2', 2),
(15, 'Neutral', '3', 3),
(15, 'Agree', '4', 4),
(15, 'Strongly Agree', '5', 5),
(16, 'Strongly Disagree', '1', 1),
(16, 'Disagree', '2', 2),
(16, 'Neutral', '3', 3),
(16, 'Agree', '4', 4),
(16, 'Strongly Agree', '5', 5);

-- Add department options for multiple choice question
INSERT INTO `question_options` (`question_id`, `option_text`, `value`, `order_sequence`) VALUES 
(10, 'Sales', '1', 1),
(10, 'Support', '2', 2),
(10, 'Technical', '3', 3),
(10, 'Other', '4', 4);

-- ========================================
-- End of Schema
-- ========================================

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(191) DEFAULT NULL,
  `event` varchar(191) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(191) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'processing',
  `total_records` int(11) NOT NULL DEFAULT 0,
  `processed_records` int(11) NOT NULL DEFAULT 0,
  `failed_records` int(11) NOT NULL DEFAULT 0,
  `initiated_by` bigint(20) unsigned NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_batches_initiated_by_foreign` (`initiated_by`),
  KEY `attendance_batches_month_year_index` (`month`,`year`),
  KEY `attendance_batches_status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_overrides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint(20) unsigned NOT NULL,
  `employee_payroll_id` varchar(191) NOT NULL,
  `date` date NOT NULL,
  `original_check_in_time` time DEFAULT NULL,
  `original_check_out_time` time DEFAULT NULL,
  `original_total_hours` decimal(5,2) DEFAULT NULL,
  `original_status` varchar(191) DEFAULT NULL,
  `overridden_check_in_time` time DEFAULT NULL,
  `overridden_check_out_time` time DEFAULT NULL,
  `overridden_total_hours` decimal(5,2) DEFAULT NULL,
  `overridden_status` varchar(191) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `overridden_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_overrides_attendance_id_foreign` (`attendance_id`),
  CONSTRAINT `attendance_overrides_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `status` varchar(191) NOT NULL,
  `leave_type_id` bigint(20) unsigned DEFAULT NULL,
  `leave_application_id` bigint(20) unsigned DEFAULT NULL,
  `public_holiday_id` bigint(20) unsigned DEFAULT NULL,
  `is_override` tinyint(1) NOT NULL DEFAULT 0,
  `original_status` varchar(191) DEFAULT NULL,
  `original_leave_type_id` bigint(20) unsigned DEFAULT NULL,
  `modified_by` bigint(20) unsigned DEFAULT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `batch_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_payroll_id_date_unique` (`payroll_id`,`date`),
  KEY `attendance_records_leave_type_id_foreign` (`leave_type_id`),
  KEY `attendance_records_leave_application_id_foreign` (`leave_application_id`),
  KEY `attendance_records_public_holiday_id_foreign` (`public_holiday_id`),
  KEY `attendance_records_user_id_date_index` (`user_id`,`date`),
  KEY `attendance_records_month_year_index` (`month`,`year`),
  KEY `attendance_records_is_locked_index` (`is_locked`),
  KEY `attendance_records_modified_by_foreign` (`modified_by`),
  KEY `attendance_records_locked_by_foreign` (`locked_by`),
  KEY `attendance_records_original_leave_type_id_foreign` (`original_leave_type_id`),
  KEY `attendance_records_batch_id_foreign` (`batch_id`),
  KEY `idx_attendance_records_employee_email` (`employee_email`(250)),
  KEY `idx_attendance_records_employee_id` (`employee_id`),
  KEY `idx_payroll_id` (`payroll_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_payroll_id` varchar(191) NOT NULL,
  `date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `status` enum('present','absent','late','early_departure','half_day','overtime') NOT NULL DEFAULT 'present',
  `is_late_arrival` tinyint(1) NOT NULL DEFAULT 0,
  `is_early_arrival` tinyint(1) NOT NULL DEFAULT 0,
  `is_late_departure` tinyint(1) NOT NULL DEFAULT 0,
  `is_early_departure` tinyint(1) NOT NULL DEFAULT 0,
  `is_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `late_arrival_minutes` int(11) NOT NULL DEFAULT 0,
  `early_departure_minutes` int(11) NOT NULL DEFAULT 0,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `undertime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `scheduled_start_time` time DEFAULT NULL,
  `scheduled_end_time` time DEFAULT NULL,
  `attendance_category` varchar(191) NOT NULL DEFAULT 'regular',
  `shift_id` bigint(20) unsigned DEFAULT NULL,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`)),
  `processed_at` timestamp NULL DEFAULT NULL,
  `source` varchar(191) NOT NULL DEFAULT 'biometric_excel',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_shift_id_foreign` (`shift_id`),
  KEY `attendances_employee_payroll_id_date_index` (`employee_payroll_id`,`date`),
  CONSTRAINT `attendances_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bulk_attendance_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bulk_attendance_summaries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_days` int(11) NOT NULL,
  `present_days` int(11) DEFAULT 0,
  `leave_days` int(11) DEFAULT 0,
  `fixed_holiday_days` int(11) DEFAULT 0,
  `flexible_holiday_days` int(11) DEFAULT 0,
  `weekend_days` int(11) DEFAULT 0,
  `lop_days` int(11) DEFAULT 0,
  `salary_days` int(11) NOT NULL,
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_month_year` (`employee_id`,`month`,`year`),
  KEY `idx_month_year` (`month`,`year`),
  KEY `idx_employee_month_year` (`employee_id`,`month`,`year`),
  KEY `idx_is_locked` (`is_locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `department_holiday_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_holiday_configs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `payroll_department_id` int(11) DEFAULT NULL,
  `financial_year` varchar(191) NOT NULL,
  `allowed_holidays` int(11) NOT NULL DEFAULT 0,
  `used_holidays` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fixed_public_holidays` int(11) NOT NULL DEFAULT 0,
  `flexible_public_holidays` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_holiday_configs_department_id_financial_year_unique` (`department_id`,`financial_year`),
  KEY `department_holiday_configs_created_by_foreign` (`created_by`),
  KEY `department_holiday_configs_updated_by_foreign` (`updated_by`),
  KEY `department_holiday_configs_financial_year_index` (`financial_year`),
  KEY `department_holiday_configs_is_active_index` (`is_active`),
  KEY `idx_payroll_department_id` (`payroll_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `department_leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_leave_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `payroll_department_id` varchar(255) DEFAULT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_leave_types_department_id_leave_type_id_unique` (`department_id`,`leave_type_id`),
  KEY `department_leave_types_department_id_index` (`department_id`),
  KEY `department_leave_types_leave_type_id_index` (`leave_type_id`),
  KEY `idx_payroll_department_id_dept_leave` (`payroll_department_id`(250))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `department_public_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department_public_holidays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `payroll_department_id` int(11) DEFAULT NULL,
  `public_holiday_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_holiday_unique` (`department_id`,`public_holiday_id`),
  KEY `department_public_holidays_department_id_index` (`department_id`),
  KEY `department_public_holidays_public_holiday_id_index` (`public_holiday_id`),
  KEY `idx_payroll_department_id` (`payroll_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `api_department_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_is_active_index` (`is_active`),
  KEY `departments_api_id_index` (`api_department_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `duty_rosters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `duty_rosters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_payroll_id` varchar(191) NOT NULL,
  `shift_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `duty_rosters_employee_payroll_id_date_unique` (`employee_payroll_id`,`date`),
  KEY `duty_rosters_shift_id_foreign` (`shift_id`),
  CONSTRAINT `duty_rosters_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emails_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `leave_application_emails` tinyint(1) NOT NULL DEFAULT 1,
  `leave_approval_emails` tinyint(1) NOT NULL DEFAULT 1,
  `leave_rejection_emails` tinyint(1) NOT NULL DEFAULT 1,
  `public_holiday_application_emails` tinyint(1) NOT NULL DEFAULT 1,
  `public_holiday_approval_emails` tinyint(1) NOT NULL DEFAULT 1,
  `public_holiday_rejection_emails` tinyint(1) NOT NULL DEFAULT 1,
  `attendance_notification_emails` tinyint(1) NOT NULL DEFAULT 1,
  `system_notification_emails` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(191) NOT NULL,
  `payroll_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `designation` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'Active',
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `payroll_department_id` varchar(255) DEFAULT NULL,
  `financial_year` varchar(191) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_resignation` date DEFAULT NULL,
  `reporting_manager_payroll_id` int(10) unsigned DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `exclude_from_payroll` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `employees_email_unique_non_null` (`email`),
  KEY `idx_employee_payroll` (`employee_id`,`payroll_id`),
  KEY `idx_department` (`department_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payroll_department_id` (`payroll_department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_application_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_application_days` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `leave_application_id` bigint(20) unsigned NOT NULL,
  `leave_date` date NOT NULL,
  `day_type` enum('full_day','first_half','second_half') NOT NULL DEFAULT 'full_day',
  `days_count` decimal(3,1) NOT NULL DEFAULT 1.0,
  `is_public_holiday` tinyint(1) NOT NULL DEFAULT 0,
  `is_week_off` tinyint(1) NOT NULL DEFAULT 0,
  `exclude_from_calculation` tinyint(1) NOT NULL DEFAULT 0,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_leave_application_date` (`leave_application_id`,`leave_date`),
  KEY `idx_leave_application_date` (`leave_application_id`,`leave_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `email_id` varchar(191) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_half_day` enum('none','first_half','second_half') NOT NULL DEFAULT 'none',
  `end_half_day` enum('none','first_half','second_half') NOT NULL DEFAULT 'none',
  `total_days` decimal(5,2) NOT NULL,
  `lop_days` decimal(8,1) DEFAULT 0.0 COMMENT 'Number of Loss of Pay days',
  `paid_days` decimal(8,1) DEFAULT 0.0 COMMENT 'Number of paid leave days',
  `has_lop` tinyint(1) DEFAULT 0 COMMENT 'Whether this leave has LOP days',
  `lop_acknowledged` tinyint(1) DEFAULT 0 COMMENT 'Whether user acknowledged LOP warning',
  `reason` varchar(191) NOT NULL,
  `leave_type` varchar(191) NOT NULL DEFAULT 'casual',
  `leave_type_id` bigint(20) unsigned DEFAULT NULL,
  `financial_year` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','forwarded_to_manager','approved_by_manager','approved','rejected','cancelled') DEFAULT 'pending',
  `manager_approved_by` bigint(20) unsigned DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `hr_approved_by` bigint(20) unsigned DEFAULT NULL,
  `hr_approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `forwarded_by` bigint(20) unsigned DEFAULT NULL,
  `forwarded_at` timestamp NULL DEFAULT NULL,
  `forwarding_note` text DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_applications_user_id_foreign` (`user_id`),
  KEY `leave_applications_leave_type_id_index` (`leave_type_id`),
  KEY `leave_applications_manager_approved_by_foreign` (`manager_approved_by`),
  KEY `leave_applications_hr_approved_by_foreign` (`hr_approved_by`),
  KEY `leave_applications_rejected_by_foreign` (`rejected_by`),
  KEY `fk_leave_applications_forwarded_by` (`forwarded_by`),
  KEY `idx_email_id` (`email_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `days_count` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `financial_year` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_types_code_unique` (`code`),
  KEY `leave_types_financial_year_is_active_index` (`financial_year`,`is_active`),
  KEY `leave_types_code_index` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `overtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `overtime` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_payroll_id` varchar(191) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `overtime_employee_payroll_id_month_year_unique` (`employee_payroll_id`,`month`,`year`),
  KEY `overtime_month_year_is_locked_index` (`month`,`year`,`is_locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `overtimes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `overtimes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_payroll_id` varchar(64) NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `overtimes_employee_month_year` (`employee_payroll_id`,`month`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `route_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_names`)),
  `route_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `public_holiday_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `public_holiday_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `public_holiday_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `financial_year` varchar(191) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_holiday_year_unique` (`user_id`,`public_holiday_id`,`financial_year`),
  KEY `public_holiday_applications_public_holiday_id_foreign` (`public_holiday_id`),
  KEY `public_holiday_applications_approved_by_foreign` (`approved_by`),
  KEY `public_holiday_applications_rejected_by_foreign` (`rejected_by`),
  KEY `public_holiday_applications_user_id_financial_year_index` (`user_id`,`financial_year`),
  KEY `public_holiday_applications_department_id_financial_year_index` (`department_id`,`financial_year`),
  KEY `public_holiday_applications_status_index` (`status`),
  KEY `idx_email` (`email`(250)),
  KEY `idx_payroll_id` (`payroll_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `public_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `public_holidays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `financial_year` varchar(191) NOT NULL,
  `type` enum('fixed','flexible') NOT NULL DEFAULT 'fixed',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_national` tinyint(1) NOT NULL DEFAULT 1,
  `color` varchar(7) NOT NULL DEFAULT '#1f2937',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_holidays_created_by_foreign` (`created_by`),
  KEY `public_holidays_updated_by_foreign` (`updated_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shifts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(191) DEFAULT NULL,
  `payroll_id` int(10) unsigned DEFAULT NULL COMMENT 'ID from payroll API for reference',
  `payroll_user_id` int(11) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `designation` varchar(191) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `permissions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions_json`)),
  `password` varchar(191) DEFAULT NULL,
  `google_id` varchar(191) DEFAULT NULL,
  `role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `financial_year` varchar(191) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_resignation` date DEFAULT NULL,
  `reporting_manager_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_reporting_manager_id_foreign` (`reporting_manager_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_backup_2025_08_25_11_03_58`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_backup_2025_08_25_11_03_58` (
  `id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `employee_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payroll_id` int(10) unsigned DEFAULT NULL COMMENT 'ID from payroll API for reference',
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('super_admin','admin','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `financial_year` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_resignation` date DEFAULT NULL,
  `reporting_manager_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_backup_2025_08_25_11_04_41`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_backup_2025_08_25_11_04_41` (
  `id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `employee_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payroll_id` int(10) unsigned DEFAULT NULL COMMENT 'ID from payroll API for reference',
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('super_admin','admin','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `financial_year` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_resignation` date DEFAULT NULL,
  `reporting_manager_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

/*M!999999\- enable the sandbox mode */ 
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_07_03_053757_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_07_03_053851_create_public_holidays_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_07_03_053907_create_leave_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_07_03_065114_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_07_03_091650_add_leave_type_to_leave_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_07_03_121936_add_type_and_status_to_public_holidays_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_07_04_063649_create_departments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_07_04_063657_create_department_holiday_configs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_07_04_063706_create_department_public_holidays_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_07_04_071140_add_department_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_07_05_053557_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_07_05_053558_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_07_05_053559_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_07_05_055000_add_indexes_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_07_07_112701_add_fixed_and_flexible_to_department_holiday_configs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_07_08_062509_create_public_holiday_applications_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_07_28_101110_create_leave_types_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_07_28_101157_create_department_leave_types_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_07_28_101743_add_leave_type_id_to_leave_applications_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_04_103048_add_employee_fields_to_users_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_04_112310_rename_api_id_to_api_department_id_in_departments_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_04_112748_make_code_nullable_in_departments_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_04_113019_remove_unique_constraint_on_api_id_in_departments_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_04_113156_remove_unique_constraint_on_code_in_departments_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_04_125000_add_approval_columns_to_leave_applications_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_08_05_061400_add_payroll_id_to_users_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_08_05_000001_create_attendance_records_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_08_05_000002_create_attendance_batches_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_08_26_065545_create_employees_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_03_100000_add_forwarding_to_leave_applications_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_06_000001_add_email_id_to_leave_applications_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_09_18_112100_make_email_nullable_in_employees_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_03_120000_create_leave_application_days_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_10_06_000001_add_is_week_off_to_leave_application_days_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_08_000001_add_employee_email_to_attendance_records_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_14_000001_add_permissions_json_to_users_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_10_09_044902_fix_attendance_records_unique_constraints',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_10_09_055500_add_payroll_department_id_to_employees_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_10_09_055501_add_payroll_department_id_to_department_leave_types_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_10_09_062100_add_payroll_department_id_to_department_public_holidays_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_10_09_143000_add_payroll_department_id_to_department_holiday_configs_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_10_13_101707_create_permissions_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_10_13_120000_add_payroll_user_id_to_users_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_10_13_160000_create_permissions_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_10_15_065727_add_enable_payroll_to_employees_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_10_15_071637_rename_enable_payroll_to_exclude_from_payroll_in_employees_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_10_16_063541_create_email_settings_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_10_16_085027_add_emails_enabled_to_email_settings_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_11_06_094802_create_shifts_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_11_06_094816_create_duty_rosters_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_11_07_084905_create_attendances_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_11_07_095827_add_detailed_attendance_statuses_to_attendances_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_11_07_101230_add_undertime_hours_to_attendances_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_11_07_111300_create_overtime_table',35);

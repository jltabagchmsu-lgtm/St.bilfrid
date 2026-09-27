-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: new_construction_firm
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `new_construction_firm`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `new_construction_firm` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `new_construction_firm`;

--
-- Table structure for table `daily_material_usages`
--

DROP TABLE IF EXISTS `daily_material_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_material_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `project_material_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `usage_date` date NOT NULL,
  `quantity_used` decimal(12,2) NOT NULL DEFAULT 0.00,
  `activity_description` varchar(255) NOT NULL,
  `logged_by` varchar(255) NOT NULL DEFAULT 'Site Project Engineer',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_material_usages_project_id_foreign` (`project_id`),
  KEY `daily_material_usages_project_material_id_foreign` (`project_material_id`),
  KEY `daily_material_usages_material_id_foreign` (`material_id`),
  CONSTRAINT `daily_material_usages_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_material_usages_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_material_usages_project_material_id_foreign` FOREIGN KEY (`project_material_id`) REFERENCES `project_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_material_usages`
--

LOCK TABLES `daily_material_usages` WRITE;
/*!40000 ALTER TABLE `daily_material_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `daily_material_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_logs`
--

DROP TABLE IF EXISTS `inventory_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` varchar(255) NOT NULL DEFAULT 'usage',
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_logs_material_id_foreign` (`material_id`),
  KEY `inventory_logs_project_id_foreign` (`project_id`),
  CONSTRAINT `inventory_logs_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_logs`
--

LOCK TABLES `inventory_logs` WRITE;
/*!40000 ALTER TABLE `inventory_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL COMMENT 'Structural, Electrical, Piping/Plumbing, Finishing, General',
  `unit` varchar(255) NOT NULL COMMENT 'bags, pcs, tons, meters, kg, sheets',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_new_product` tinyint(1) NOT NULL DEFAULT 0,
  `last_purchased_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `materials_material_code_unique` (`material_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materials`
--

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2026_01_01_000000_create_users_table',1),(3,'2026_01_01_000001_create_construction_system_tables',1),(4,'2026_01_01_000002_create_project_costs_table',1),(5,'2026_01_01_000003_add_workforce_deployment_to_projects_table',1),(6,'2026_01_01_000004_create_project_photos_table',1),(7,'2026_01_01_000005_add_receipts_and_excess_fields',1),(8,'2026_01_01_000006_add_loan_and_payment_first_financing_fields',1),(9,'2026_01_01_000007_create_project_scope_items_and_lines_table',1),(10,'2026_01_01_000008_create_daily_material_usages_and_transfers_table',1),(11,'2026_01_01_000009_enhance_project_tasks_table',1),(12,'2026_01_01_000010_add_timeline_fields_to_project_tasks_table',1),(13,'2026_01_01_000011_create_project_task_materials_table',1),(14,'2026_01_01_000012_add_role_to_users_table',1),(15,'2026_01_01_000013_create_supplier_management_tables',1),(16,'2026_09_05_083016_add_client_budget_and_estimated_cost_to_projects_table',1),(17,'2026_09_06_190000_create_supplier_order_messages_and_inquiries_table',1),(18,'2026_09_06_194000_add_is_synced_to_inventory_to_supplier_orders_table',1),(19,'2026_09_06_195500_add_new_product_flags_to_materials_table',1),(20,'2026_09_12_000001_add_unique_index_to_payments_official_receipt_no',1),(21,'2026_09_12_000002_add_license_expiry_fields_to_personnel_table',1),(22,'2026_09_21_000001_add_photo_proof_to_project_tasks_table',1),(23,'2026_09_23_000001_unify_material_category_names',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `official_receipt_no` varchar(255) DEFAULT NULL COMMENT 'Official Receipt (OR) Number',
  `payer_name` varchar(255) DEFAULT NULL COMMENT 'Client / Payer Entity Name',
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_stage` varchar(255) NOT NULL COMMENT 'Downpayment, 30% Milestone, 60% Structural, Final Turnover',
  `payment_method` varchar(255) NOT NULL DEFAULT 'Bank Transfer',
  `financing_type` varchar(255) NOT NULL DEFAULT 'bank_loan' COMMENT 'bank_loan, pagibig_loan, client_equity, cash_progress',
  `financing_institution` varchar(255) DEFAULT NULL COMMENT 'Disbursing Bank or Pag-IBIG Fund',
  `loan_reference_no` varchar(255) DEFAULT NULL COMMENT 'Bank/Pag-IBIG LOG, NOA or Cheque No.',
  `disbursing_entity` varchar(255) DEFAULT NULL COMMENT 'Bank Loan Disbursement Unit / HDMF / Client',
  `drawdown_tranche` varchar(255) DEFAULT NULL COMMENT 'Tranche 1, Tranche 2, Equity Downpayment, etc.',
  `payment_first_cleared` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Payment cleared before construction starts',
  `construction_clearance_status` varchar(255) NOT NULL DEFAULT 'cleared_to_construct' COMMENT 'cleared_to_construct, pending_bank_release, inspection_scheduled',
  `bank_reference` varchar(255) DEFAULT NULL COMMENT 'Cheque / Wire / Deposit Ref',
  `received_by` varchar(255) DEFAULT NULL COMMENT 'Authorized Financial Officer',
  `status` enum('paid','pending','overdue') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded payment receipt/proof',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_invoice_no_unique` (`invoice_no`),
  UNIQUE KEY `payments_official_receipt_no_unique` (`official_receipt_no`),
  KEY `payments_project_id_foreign` (`project_id`),
  CONSTRAINT `payments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personnel`
--

DROP TABLE IF EXISTS `personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personnel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Lead Architect, Site Engineer, Electrical Engineer, Plumbing Engineer, etc.',
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `license_no` varchar(255) DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL COMMENT 'PRC / Professional license validity date',
  `license_status` varchar(255) NOT NULL DEFAULT 'active' COMMENT 'active, expired, inactive, suspended',
  `specialization` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personnel_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personnel`
--

LOCK TABLES `personnel` WRITE;
/*!40000 ALTER TABLE `personnel` DISABLE KEYS */;
/*!40000 ALTER TABLE `personnel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_costs`
--

DROP TABLE IF EXISTS `project_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `cost_code` varchar(255) NOT NULL,
  `cost_category` varchar(255) NOT NULL COMMENT 'Materials & Consumables, Labor & Engineering, Equipment & Heavy Machinery, Subcontractor & Trade, Permits & Regulatory, Site Overhead & Utilities, Contingency & Testing',
  `item_name` varchar(255) NOT NULL,
  `cost_type` varchar(255) NOT NULL DEFAULT 'Direct' COMMENT 'Direct, Indirect, Subcontract, Overhead, Contingency',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(255) NOT NULL DEFAULT 'lot' COMMENT 'lot, sq.m, hours, days, units, trips, months, cu.m, tons, bags',
  `unit_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estimated_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('budgeted','committed','incurred','settled') NOT NULL DEFAULT 'incurred',
  `cost_date` date NOT NULL,
  `vendor_payee` varchar(255) DEFAULT NULL COMMENT 'Supplier, contractor, agency or workforce payee',
  `reference_no` varchar(255) DEFAULT NULL COMMENT 'PO / Invoice / Voucher / OR ref',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_costs_cost_code_unique` (`cost_code`),
  KEY `project_costs_project_id_foreign` (`project_id`),
  CONSTRAINT `project_costs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_costs`
--

LOCK TABLES `project_costs` WRITE;
/*!40000 ALTER TABLE `project_costs` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_material_transfers`
--

DROP TABLE IF EXISTS `project_material_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_material_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_project_id` bigint(20) unsigned NOT NULL,
  `destination_project_id` bigint(20) unsigned DEFAULT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `quantity_transferred` decimal(12,2) NOT NULL DEFAULT 0.00,
  `transfer_date` date NOT NULL,
  `transfer_reference_no` varchar(255) NOT NULL,
  `transfer_type` varchar(255) NOT NULL DEFAULT 'inter_project',
  `reason` varchar(255) DEFAULT NULL,
  `authorized_by` varchar(255) NOT NULL DEFAULT 'Engr. Sophia Martinez, PMP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_material_transfers_source_project_id_foreign` (`source_project_id`),
  KEY `project_material_transfers_destination_project_id_foreign` (`destination_project_id`),
  KEY `project_material_transfers_material_id_foreign` (`material_id`),
  CONSTRAINT `project_material_transfers_destination_project_id_foreign` FOREIGN KEY (`destination_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_material_transfers_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_material_transfers_source_project_id_foreign` FOREIGN KEY (`source_project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_material_transfers`
--

LOCK TABLES `project_material_transfers` WRITE;
/*!40000 ALTER TABLE `project_material_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_material_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_materials`
--

DROP TABLE IF EXISTS `project_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `allocated_qty` int(11) NOT NULL DEFAULT 0,
  `used_qty` int(11) NOT NULL DEFAULT 0,
  `excess_returned_qty` int(11) NOT NULL DEFAULT 0 COMMENT 'Unused excess materials returned to central inventory',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_materials_project_id_foreign` (`project_id`),
  KEY `project_materials_material_id_foreign` (`material_id`),
  CONSTRAINT `project_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_materials_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_materials`
--

LOCK TABLES `project_materials` WRITE;
/*!40000 ALTER TABLE `project_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_personnel`
--

DROP TABLE IF EXISTS `project_personnel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_personnel` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `personnel_id` bigint(20) unsigned NOT NULL,
  `assignment_role` varchar(255) DEFAULT NULL COMMENT 'Project Lead, Lead Architect, Site Manager, etc.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_personnel_project_id_foreign` (`project_id`),
  KEY `project_personnel_personnel_id_foreign` (`personnel_id`),
  CONSTRAINT `project_personnel_personnel_id_foreign` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_personnel_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_personnel`
--

LOCK TABLES `project_personnel` WRITE;
/*!40000 ALTER TABLE `project_personnel` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_personnel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_photos`
--

DROP TABLE IF EXISTS `project_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `photo_type` enum('blueprint','3d_render','actual_site','client_want','structural','finishing') NOT NULL DEFAULT 'actual_site',
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `taken_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_photos_project_id_foreign` (`project_id`),
  CONSTRAINT `project_photos_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_photos`
--

LOCK TABLES `project_photos` WRITE;
/*!40000 ALTER TABLE `project_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_scope_items`
--

DROP TABLE IF EXISTS `project_scope_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_scope_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `item_number` int(11) NOT NULL DEFAULT 1,
  `item_name` varchar(255) NOT NULL,
  `volume_or_area` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `materials_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `labor_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `equipment_subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `direct_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `contingency_percent` decimal(5,2) NOT NULL DEFAULT 15.00,
  `contingency_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `taxes_percent` decimal(5,2) NOT NULL DEFAULT 6.00,
  `taxes_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `profit_percent` decimal(5,2) NOT NULL DEFAULT 10.00,
  `profit_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_item_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_scope_items_project_id_foreign` (`project_id`),
  CONSTRAINT `project_scope_items_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_scope_items`
--

LOCK TABLES `project_scope_items` WRITE;
/*!40000 ALTER TABLE `project_scope_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_scope_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_scope_lines`
--

DROP TABLE IF EXISTS `project_scope_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_scope_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_scope_item_id` bigint(20) unsigned NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'material',
  `description` varchar(255) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `material_id` bigint(20) unsigned DEFAULT NULL,
  `used_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `excess_returned_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_scope_lines_project_scope_item_id_foreign` (`project_scope_item_id`),
  KEY `project_scope_lines_material_id_foreign` (`material_id`),
  CONSTRAINT `project_scope_lines_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_scope_lines_project_scope_item_id_foreign` FOREIGN KEY (`project_scope_item_id`) REFERENCES `project_scope_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_scope_lines`
--

LOCK TABLES `project_scope_lines` WRITE;
/*!40000 ALTER TABLE `project_scope_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_scope_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_task_materials`
--

DROP TABLE IF EXISTS `project_task_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_task_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_task_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned DEFAULT NULL,
  `material_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'Structural',
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_task_materials_project_task_id_foreign` (`project_task_id`),
  KEY `project_task_materials_material_id_foreign` (`material_id`),
  CONSTRAINT `project_task_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_task_materials_project_task_id_foreign` FOREIGN KEY (`project_task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_task_materials`
--

LOCK TABLES `project_task_materials` WRITE;
/*!40000 ALTER TABLE `project_task_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_task_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tasks`
--

DROP TABLE IF EXISTS `project_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'General' COMMENT 'Structural, Electrical, Piping, General',
  `assigned_personnel_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `allocated_budget` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `progress` int(11) NOT NULL DEFAULT 0 COMMENT '0 to 100%',
  `status` enum('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `timeline_phase` varchar(255) DEFAULT NULL,
  `timeline_month` varchar(255) DEFAULT NULL,
  `photo_path` varchar(1000) DEFAULT NULL,
  `photo_caption` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_tasks_project_id_foreign` (`project_id`),
  KEY `project_tasks_assigned_personnel_id_foreign` (`assigned_personnel_id`),
  CONSTRAINT `project_tasks_assigned_personnel_id_foreign` FOREIGN KEY (`assigned_personnel_id`) REFERENCES `personnel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tasks`
--

LOCK TABLES `project_tasks` WRITE;
/*!40000 ALTER TABLE `project_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_code` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `project_type` enum('Commercial Construction','Residential Build','Industrial Complex','Renovation & Overhaul') NOT NULL DEFAULT 'Commercial Construction',
  `finish_tier` varchar(255) NOT NULL DEFAULT 'standard' COMMENT 'standard, executive, luxury',
  `land_area_sqm` decimal(10,2) NOT NULL COMMENT 'Land area in square meters',
  `floor_area_sqm` decimal(10,2) NOT NULL COMMENT 'Floor area in square meters',
  `status` enum('pending_approval','approved','in_progress','completed','on_hold') NOT NULL DEFAULT 'in_progress',
  `contract_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `client_budget` decimal(14,2) DEFAULT NULL COMMENT 'Stated Client Budget / Investment Target Cap',
  `estimated_cost` decimal(14,2) DEFAULT NULL COMMENT 'Benchmark Automated Calculated Construction Cost',
  `financing_type` varchar(255) NOT NULL DEFAULT 'bank_loan' COMMENT 'bank_loan, pagibig_loan, cash_equity, combined',
  `financing_institution` varchar(255) DEFAULT NULL COMMENT 'Bank Name (BDO, BPI, Metrobank, etc.) or Pag-IBIG Fund',
  `loan_account_no` varchar(255) DEFAULT NULL COMMENT 'Loan NOA / LOG / Account Reference Number',
  `approved_loan_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Approved Loan Portion from Bank/Pag-IBIG',
  `client_equity_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Client Direct Equity Portion',
  `payment_first_policy` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Payment First Before Construct Rule',
  `spent_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `structural_progress` int(11) NOT NULL DEFAULT 0,
  `electrical_progress` int(11) NOT NULL DEFAULT 0,
  `piping_progress` int(11) NOT NULL DEFAULT 0,
  `finishing_progress` int(11) NOT NULL DEFAULT 0 COMMENT 'Architectural & Turnkey Finishing %',
  `structural_weight` int(11) NOT NULL DEFAULT 40 COMMENT 'Weight % for Structural Works',
  `electrical_weight` int(11) NOT NULL DEFAULT 25 COMMENT 'Weight % for Electrical Works',
  `piping_weight` int(11) NOT NULL DEFAULT 20 COMMENT 'Weight % for Piping/Plumbing Works',
  `finishing_weight` int(11) NOT NULL DEFAULT 15 COMMENT 'Weight % for Finishing Works',
  `overall_progress` int(11) NOT NULL DEFAULT 0,
  `current_phase` varchar(255) NOT NULL DEFAULT 'Phase 1: Mobilization & Site Prep',
  `description` text DEFAULT NULL,
  `schedule_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deployed_workers` int(11) NOT NULL DEFAULT 0 COMMENT 'General Construction Workers / Laborers',
  `deployed_skilled_workers` int(11) NOT NULL DEFAULT 0 COMMENT 'Skilled Trades: Masons, Carpenters, Welders, Steelmen',
  `deployed_engineers` int(11) NOT NULL DEFAULT 0 COMMENT 'Site, Structural, Electrical & Piping Engineers',
  `deployed_architects` int(11) NOT NULL DEFAULT 0 COMMENT 'Architects & Design Planners',
  `deployed_foremen` int(11) NOT NULL DEFAULT 0 COMMENT 'Site Foremen & Trade Supervisors',
  `deployed_operators` int(11) NOT NULL DEFAULT 0 COMMENT 'Heavy Equipment, Crane & Excavator Operators',
  `deployed_safety_officers` int(11) NOT NULL DEFAULT 0 COMMENT 'Safety, Environmental & QA/QC Officers',
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_project_code_unique` (`project_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_code` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_phone` varchar(255) DEFAULT NULL,
  `service_type` varchar(255) NOT NULL,
  `land_area_sqm` decimal(10,2) NOT NULL,
  `floor_area_sqm` decimal(10,2) NOT NULL,
  `estimated_cost` decimal(12,2) NOT NULL,
  `requested_start_date` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_requests_request_code_unique` (`request_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_inquiries`
--

DROP TABLE IF EXISTS `supplier_inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_inquiries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `supplier_material_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `requested_quantity` int(11) DEFAULT NULL,
  `status` enum('open','quoted','closed') NOT NULL DEFAULT 'open',
  `supplier_response` text DEFAULT NULL,
  `quoted_unit_price` decimal(12,2) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_inquiries_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_inquiries_supplier_material_id_foreign` (`supplier_material_id`),
  KEY `supplier_inquiries_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_inquiries_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_inquiries_supplier_material_id_foreign` FOREIGN KEY (`supplier_material_id`) REFERENCES `supplier_materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_inquiries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_inquiries`
--

LOCK TABLES `supplier_inquiries` WRITE;
/*!40000 ALTER TABLE `supplier_inquiries` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_inquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_materials`
--

DROP TABLE IF EXISTS `supplier_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `material_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `min_order_qty` int(11) NOT NULL DEFAULT 1,
  `availability_status` enum('available','low_stock','out_of_stock','unavailable') NOT NULL DEFAULT 'available',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_materials_material_code_unique` (`material_code`),
  KEY `supplier_materials_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `supplier_materials_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_materials`
--

LOCK TABLES `supplier_materials` WRITE;
/*!40000 ALTER TABLE `supplier_materials` DISABLE KEYS */;
INSERT INTO `supplier_materials` VALUES (1,1,'MAT-WNDR-001-SLD120','1.20m x 1.20m Sliding Window 1/4\" Clear Glass Heavy Aluminum Frame','Windows & Doors','Windows','Two-panel horizontal sliding glass window with heavy duty extruded aluminum framing and weatherstripping.','1/4\" (6mm) Clear Tempered Glass, Powder-Coated Aluminum 38mm Section, Heavy-Duty Stainless Bearing Rollers','units',85,6100.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(2,1,'MAT-WNDR-002-SLD200','1.20m x 2.00m Sliding Window Heavy Aluminum Frame','Windows & Doors','Windows','Large format picture sliding window designed for living rooms and premium residential facades.','6mm Clear Tempered Glass, Heavy-Duty Analok/Powder-Coated Aluminum, Integrated Flyscreen Mesh','units',42,10200.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(3,1,'MAT-WNDR-003-AWN60','0.60m x 0.90m Bathroom Frosted Awning Window','Windows & Doors','Windows','Top-hinged awning casement window with obscured frosted glass for bathrooms and powder rooms.','Frosted Privacy Tempered Glass 6mm, Stainless Steel Friction Hinges, Aluminum Frame','units',95,2250.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(4,1,'MAT-WNDR-004-AWN180','1.80m x 0.45m Transom Awning Casement Window','Windows & Doors','Windows','High-level clerestory transom awning window providing natural daylighting and cross ventilation.','Clear Float Glass 6mm, Multi-Point Locking Casement Handle, Extruded Aluminum','units',60,3400.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(5,1,'MAT-WNDR-005-PNL90','Main Solid Kiln-Dried Mahogany Panel Door 0.90m x 2.10m','Windows & Doors','Doors','Premium solid hardwood front entrance door featuring raised decorative panels.','100% Solid Kiln-Dried Mahogany Hardwood, 44mm Door Thickness, Precision Sanded Finish','sets',75,4350.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(6,1,'MAT-WNDR-006-FLSH80','Solid Core Interior Flush Door 0.80m x 2.10m','Windows & Doors','Doors','High-durability acoustic flush door engineered for bedrooms and private interior offices.','Solid Particleboard Core, Premium Marine Plywood Facing, Factory Sanded 40mm Thick','sets',110,3950.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(7,1,'MAT-WNDR-007-FLSH70','Service Flush Door 0.70m x 2.10m Moisture Resistant','Windows & Doors','Doors','Moisture resistant flush door designed for kitchen exits, utility rooms, and balcony access.','Waterproof Marine Core, Anti-Warp Solid Wood Stiles, 38mm Thickness','sets',90,3600.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(8,1,'MAT-WNDR-008-PVC60','Heavy-Duty Waterproof PVC Door w/ Louver & Jamb 0.60m x 2.10m','Windows & Doors','Doors','Complete PVC door unit with bottom louver and matching PVC jamb for toilet & bath.','High-Impact Resistant Virgin PVC, Reinforced Core, Includes Complete PVC Jamb & Hinges','sets',140,1650.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(9,1,'MAT-WNDR-009-SLD150','1.50m x 2.10m Sliding Patio Double Glass Door Aluminum Frame','Windows & Doors','Doors','Heavy duty sliding patio door offering wide garden views and smooth floor-level threshold.','6mm Tempered Safety Glass, Heavy Extruded Powder-Coated Aluminum Track, Stainless Mortise Lock','sets',35,19500.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(10,1,'MAT-WNDR-010-JAMB2X4','Treated Solid Hardwood Door Jamb 2\" x 4\" Double Rabbeted','Windows & Doors','Frames','Precision milled hardwood door frames with pressure treated anti-termite protection.','Kiln-Dried Philippine Hardwood, 2\" x 4\" Cross-Section, Double Rabbet Profile','sets',180,1250.00,2,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(11,1,'MAT-WNDR-011-LCKMAIN','Heavy-Duty Stainless Steel Lever Entrance Lockset (Main Door)','Windows & Doors','Locks','Commercial grade lever entrance lockset with solid brass mortise cylinder and anti-drill pins.','SUS304 Stainless Steel Construction, Grade 2 Commercial Standard, 3 Computer-Cut Keys','sets',130,2850.00,1,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(12,1,'MAT-WNDR-012-LCKBED','Cylindrical Stainless Steel Bedroom Door Knob Lockset','Windows & Doors','Handles','Residential tubular cylindrical door knob lockset for interior privacy and passage doors.','SUS304 Stainless Steel Finish, Heavy Brass Core Latch Mechanism, Push-Button Lock','sets',220,1350.00,2,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(13,1,'MAT-WNDR-013-HNGE','Stainless Steel Ball Bearing Loosepin Hinges 3.5\" x 3.5\" (Pair)','Windows & Doors','Hardware','Non-corrosive door hinges with four internal ball bearing rings for silent and smooth swing.','SUS304 Stainless Steel 2.5mm Thick, 4 Ball Bearing Rings per Leaf, Matching Screws','pairs',550,195.00,6,'available',NULL,1,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(14,2,'MAT-ROOF-001-RIB40','Rib-Type Pre-Painted Long Span Roofing Sheet (0.40mm TCT)','Roofing','Roofing sheets','High-profile rib-type roofing engineered with deep water channels for maximum rainfall discharge.','0.40mm Total Coated Thickness (TCT), AZ150 Zinc-Aluminum Anti-Rust Coating, Spanish Red / Evergreen','ln.m.',4800,395.00,10,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(15,2,'MAT-ROOF-002-CORR40','Corrugated Pre-Painted Galvanized Iron (PGI) Sheet 0.40mm','Roofing','Roofing sheets','Traditional wave corrugated metal sheet with multi-layer baked polyester color finish.','Standard Wave Profile, UV-Resistant Polyester Top Coat, 0.40mm Base Metal Thickness','ln.m.',3200,380.00,10,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(16,2,'MAT-ROOF-003-PUPNL50','High-Density Polyurethane (PU) Sandwich Roof Panel 50mm','Roofing','Roof panels','Insulated composite roof panel with injected high-density rigid polyurethane core.','50mm Injected Rigid PU Foam (40kg/m┬│), Dual 0.40mm Pre-Painted Steel Skin, K-Value 0.024 W/mK','sq.m.',650,1150.00,20,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(17,2,'MAT-ROOF-004-PUR2X4','Structural C-Purlins 2\" x 4\" x 1.20mm Heavy Gauge Galvanized','Roofing','Roofing accessories','Cold-formed structural roof purlins with high tensile strength and anti-corrosion galvanized zinc coating.','Galvanized High-Yield Steel Grade 275, 6.00m Standard Length, 1.20mm Actual Thickness','pcs',1100,465.00,5,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(18,2,'MAT-ROOF-005-PUR2X3','Structural C-Purlins 2\" x 3\" x 1.00mm Commercial Grade','Roofing','Roofing accessories','Standard roof purlin sections suitable for residential rafters and secondary roof bracing.','6.00m Standard Length, High Tensile Cold-Formed Section, 1.00mm Nominal Thickness','pcs',1400,375.00,5,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(19,2,'MAT-ROOF-006-RDGCAP','Pre-Painted Ridge Cap Roll-Top 8-Foot Section','Roofing','Flashing','Crest flashing cover that seals the roof apex against wind-driven torrential rain.','0.40mm Pre-Painted Steel, 2.44m (8ft) Girth 18\", Color-Matched with Rib-Type Sheets','pcs',750,320.00,2,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(20,2,'MAT-ROOF-007-WALLFLSH','Wall Flashing & Counter Flashing Sheet 8-Foot (0.40mm)','Roofing','Flashing','Precision bent flashing with hemmed drip edge to seal roof-to-masonry wall junctions.','0.40mm Anti-Rust Coated Sheet, 2.44m Length, Hemmed Water Drip Edge','pcs',820,290.00,2,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(21,2,'MAT-ROOF-008-GUTTERSS','Stainless Steel 304 Spanish Box Gutter 8-Foot Section','Roofing','Gutters','Heavy-duty commercial rain gutter made from architectural grade stainless steel.','Grade 304 Stainless Steel 0.50mm Thick, Box Profile with Stiffened Lip, 2.44m Length','pcs',580,560.00,2,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(22,2,'MAT-ROOF-009-TEKSCREW','Self-Drilling 2-1/2\" Tekscrew for Metal Roofing (Box of 500)','Roofing','Roofing accessories','Hex-head self-drilling fastener with weatherproofing EPDM bonded sealing washer.','Ruspert Multi-Layer Anti-Corrosion Coating (1,000 hrs Salt Spray), High-Grade EPDM Washer','boxes',280,620.00,1,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(23,2,'MAT-ROOF-010-SEAL1GAL','Elastomeric Weatherproof Polyurethane Roof Sealant (1 Gallon)','Roofing','Roofing accessories','Flexible rubberized waterproofing compound for sealing lap joints, tek fasteners, and flashing.','100% Elastomeric Waterproof Polyurethane, UV & Thermal Shock Resistant, Non-Sag','cans',230,890.00,1,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(24,2,'MAT-ROOF-011-INSU50M','Double-Sided Aluminum Foil Thermal Roof Insulation (1.2m x 50m)','Roofing','Roofing accessories','High-efficiency radiant heat barrier with fiberglass reinforced scrim.','97% Radiant Heat Reflectance, High Tear Resistance Reinforced Scrim, Roll Coverage 60 sq.m','rolls',115,2750.00,1,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(25,3,'MAT-STRC-001-CEM40','Portland Cement (Type I) 40kg Premium High-Strength','Structural & Masonry','Cement','General purpose hydraulic cement formulated for structural concrete columns, beams, and suspended slabs.','ASTM C150 Type I Standard, 28-day Compressive Strength >= 40.0 MPa (5,800 psi)','bags',14500,220.00,50,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(26,3,'MAT-STRC-002-POZZ40','Pozzolan Cement 40kg (Type IP) Blended Masonry Cement','Structural & Masonry','Cement','Blended hydraulic pozzolan cement engineered for masonry block laying, plastering, and floor screeds.','PNS 63 / ASTM C595 Blended Hydraulic Cement, High Sulfate Resistance, Reduced Heat of Hydration','bags',8000,205.00,50,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(27,3,'MAT-STRC-003-ST16G60','16mm Deformed Steel Rebar (Grade 60) High Tensile (6.0m)','Structural & Masonry','Structural Steel','High-yield deformed steel reinforcing bars for heavy structural column main bars and foundation footings.','PNS 49 / ASTM A615 Grade 60 (Yield Strength >= 415 MPa), 6.00m Standard Length, Micro-Alloyed','pcs',8200,440.00,20,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(28,3,'MAT-STRC-004-ST12G40','12mm Deformed Steel Rebar (Grade 40) (6.0m)','Structural & Masonry','Structural Steel','Standard structural grade rebar for floor slab reinforcement grids and retaining wall cages.','PNS 49 / ASTM A615 Grade 40 (Yield Strength >= 275 MPa), 6.00m Length, Hot-Rolled High Ductility','pcs',11500,300.00,20,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(29,3,'MAT-STRC-005-ST10G40','10mm Deformed Steel Rebar (Grade 40) (6.0m)','Structural & Masonry','Structural Steel','Structural rebar widely used for beam lateral ties, column stirrup rings, and CHB wall dowels.','Standard 6.00m Length, PNS 49 Certified, Grade 40','pcs',17500,215.00,25,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(30,3,'MAT-STRC-006-ST08','8mm Plain Round Steel Bar / Rebar (6.0m)','Structural & Masonry','Structural Steel','Round structural carbon steel used for temperature reinforcement and concrete crack control mesh.','Structural Mild Steel, 6.00m Length, Smooth Round Profile','pcs',13500,115.00,30,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(31,3,'MAT-STRC-007-SNDRIV','Mixing Sand (Coarse / Fine River Sand)','Structural & Masonry','Aggregates','Clean washed river aggregate sand free of silt and organic contaminants for concrete and mortar.','Washed River Sand, Fineness Modulus 2.6 - 2.9, Specific Gravity >= 2.60','cu.m',2400,820.00,5,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(32,3,'MAT-STRC-008-GRV34','3/4\" Crushed Basalt Gravel Aggregate','Structural & Masonry','Aggregates','100% crushed hard basalt quarry rock for high-strength structural concrete ready-mix.','19mm (3/4\") Nominal Sieve Size, 100% Angular Crushed Basalt, Abrasion Loss < 25%','cu.m',1900,1380.00,5,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(33,3,'MAT-STRC-009-CHB04','4\" Concrete Hollow Block (CHB) Load-Bearing Machine-Pressed','Structural & Masonry','Masonry Blocks','Machine vibrated concrete blocks for exterior non-loadbearing partitions and interior divider walls.','100mm x 200mm x 400mm (4\" x 8\" x 16\"), Compressive Strength >= 4.5 MPa (650 psi)','pcs',34000,12.50,500,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(34,3,'MAT-STRC-010-CHB06','6\" Concrete Hollow Block (CHB) Heavy-Duty Load-Bearing','Structural & Masonry','Masonry Blocks','High-strength structural hollow blocks designed for perimeter firewall enclosures and structural core walls.','150mm x 200mm x 400mm (6\" x 8\" x 16\"), Compressive Strength >= 5.5 MPa (800 psi)','pcs',14500,15.50,300,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(35,3,'MAT-STRC-011-PHEN12','Phenolic Plywood 1/2\" x 4\' x 8\' Film-Faced Formworks','Structural & Masonry','Formworks','Waterproof film faced plywood providing fair-faced smooth architectural concrete finishes.','12mm WBP Phenolic Glue, Double-Sided Dynea Film, Reusable 8-10 Concrete Pours','sheets',450,1150.00,10,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(36,3,'MAT-STRC-012-TIEW16','#16 G.I. Tie Wire (35kg Roll)','Structural & Masonry','Structural Steel','Soft annealed galvanized iron wire for securing steel rebar intersections and stirrups.','16-Gauge Annealed Galvanized Iron, High Pliability & Tensile Strength, 35kg Gross Weight','rolls',120,2100.00,2,'available',NULL,1,'2026-09-27 06:48:07','2026-09-27 06:48:07');
/*!40000 ALTER TABLE `supplier_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_notifications`
--

DROP TABLE IF EXISTS `supplier_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_notifications_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_notifications_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_notifications`
--

LOCK TABLES `supplier_notifications` WRITE;
/*!40000 ALTER TABLE `supplier_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_order_items`
--

DROP TABLE IF EXISTS `supplier_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_order_id` bigint(20) unsigned NOT NULL,
  `supplier_material_id` bigint(20) unsigned DEFAULT NULL,
  `material_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_order_items_supplier_order_id_foreign` (`supplier_order_id`),
  KEY `supplier_order_items_supplier_material_id_foreign` (`supplier_material_id`),
  CONSTRAINT `supplier_order_items_supplier_material_id_foreign` FOREIGN KEY (`supplier_material_id`) REFERENCES `supplier_materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_order_items_supplier_order_id_foreign` FOREIGN KEY (`supplier_order_id`) REFERENCES `supplier_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_order_items`
--

LOCK TABLES `supplier_order_items` WRITE;
/*!40000 ALTER TABLE `supplier_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_order_logs`
--

DROP TABLE IF EXISTS `supplier_order_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_order_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(255) NOT NULL,
  `to_status` varchar(255) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_order_logs_supplier_order_id_foreign` (`supplier_order_id`),
  KEY `supplier_order_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_order_logs_supplier_order_id_foreign` FOREIGN KEY (`supplier_order_id`) REFERENCES `supplier_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_order_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_order_logs`
--

LOCK TABLES `supplier_order_logs` WRITE;
/*!40000 ALTER TABLE `supplier_order_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_order_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_order_messages`
--

DROP TABLE IF EXISTS `supplier_order_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_order_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `sender_role` enum('admin','supplier') NOT NULL DEFAULT 'admin',
  `message` text NOT NULL,
  `attachment_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_order_messages_supplier_order_id_foreign` (`supplier_order_id`),
  KEY `supplier_order_messages_user_id_foreign` (`user_id`),
  CONSTRAINT `supplier_order_messages_supplier_order_id_foreign` FOREIGN KEY (`supplier_order_id`) REFERENCES `supplier_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_order_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_order_messages`
--

LOCK TABLES `supplier_order_messages` WRITE;
/*!40000 ALTER TABLE `supplier_order_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_order_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_orders`
--

DROP TABLE IF EXISTS `supplier_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_code` varchar(255) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `ordered_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `delivery_location` varchar(255) NOT NULL,
  `requested_delivery_date` date NOT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','processing','ready_for_delivery','delivered','completed','cancelled') NOT NULL DEFAULT 'pending',
  `is_synced_to_inventory` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_orders_order_code_unique` (`order_code`),
  KEY `supplier_orders_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_orders_ordered_by_user_id_foreign` (`ordered_by_user_id`),
  KEY `supplier_orders_project_id_foreign` (`project_id`),
  CONSTRAINT `supplier_orders_ordered_by_user_id_foreign` FOREIGN KEY (`ordered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_orders_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_orders`
--

LOCK TABLES `supplier_orders` WRITE;
/*!40000 ALTER TABLE `supplier_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_code_unique` (`code`),
  UNIQUE KEY `suppliers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Mils Glass and Aluminum Works','SUP-WNDR-01','Windows & Doors','Engr. Roberto M. Santos','windows.doors.supplier@stbilfrid.com','+63 (34) 495-8821','Zone 4 Industrial Park, Silay City, Negros Occidental',4.95,'active','2026-09-27 06:48:06','2026-09-27 06:48:06'),(2,'Colorsteel','SUP-ROOF-01','Roofing','Engr. Danilo V. Tan','roofing.supplier@stbilfrid.com','+63 (34) 495-7744','Km. 14 National Highway, Talisay - Silay Coastal Rd, Negros Occidental',4.90,'active','2026-09-27 06:48:06','2026-09-27 06:48:06'),(3,'Titan Structural & Steel Supplies Corp.','SUP-STRC-01','Structural & Masonry','Engr. Ferdinand G. Tan','structural.supplier@stbilfrid.com','+63 (34) 495-9910','Bacolod Port Area Logistics Hub, Reclamation District, Bacolod City',4.98,'active','2026-09-27 06:48:07','2026-09-27 06:48:07');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin' COMMENT 'admin, roofing_transfer, windows_doors_transfer',
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `users_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Mils Glass and Aluminum Works Portal','windows.doors.supplier@stbilfrid.com','supplier',1,NULL,'$2y$12$0o9mq0UCw0hAEE28kyVr4OTj90aYbTC5mMRmev/5sExAmryWC.FGa',NULL,'2026-09-27 06:48:06','2026-09-27 06:48:06'),(3,'Colorsteel Supplier Portal','roofing.supplier@stbilfrid.com','supplier',2,NULL,'$2y$12$W25P8SxIOg3GRggm6/t.0eZ83tYyohHhrTstdbzzyLJoXOXVUkVLO',NULL,'2026-09-27 06:48:07','2026-09-27 06:48:07'),(4,'Titan Structural Portal','structural.supplier@stbilfrid.com','supplier',3,NULL,'$2y$12$kTf.a8mdmyTv5E4pxRQ6IOxo3VK3swIb5X3mDtegufSbv27sFQQ7S',NULL,'2026-09-27 06:48:07','2026-09-27 06:48:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'new_construction_firm'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-27 22:48:53

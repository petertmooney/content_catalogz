-- Content Catalogz Database Export
-- Export Date: 2026-02-08 04:28:04

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Table structure for table `activities`
DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `type` enum('call','email','meeting','note','task','quote_sent','invoice_sent','payment_received','other') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text,
  `activity_date` datetime NOT NULL,
  `duration_minutes` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_date` (`activity_date`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `activities`
INSERT INTO `activities` VALUES ('3', '3', 'meeting', 'On-Site SEO Review', 'Visited their office to review current website and SEO issues. Identified major opportunities.', '2026-01-28 11:00:00', '120', '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('4', '3', 'email', 'SEO Audit Report Delivered', 'Sent comprehensive SEO audit report with recommendations and pricing', '2026-02-03 09:30:00', NULL, '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('5', '4', 'email', 'Initial Inquiry', 'Received inquiry via contact form about social media management', '2026-02-07 16:45:00', NULL, '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('6', '6', 'call', 'Weekly Check-in', 'Reviewed newsletter analytics - 42% open rate, 12% click rate. Very pleased with results.', '2026-02-06 15:00:00', '20', '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('7', '6', 'meeting', 'Menu Photography Session', 'Met at cafe to photograph new menu items for February newsletter', '2026-01-30 13:00:00', '90', '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('8', '5', 'email', 'Final Deliverables', 'Delivered final blog posts and 3-month content calendar. Project completed.', '2026-01-25 11:20:00', NULL, '2026-02-08 01:25:38', NULL);
INSERT INTO `activities` VALUES ('9', '3', 'note', 'Client created from quote', 'Quote converted to active client. Status changed to In Progress.', '2026-02-08 01:31:23', NULL, '2026-02-08 01:31:23', '1');


-- Table structure for table `client_notes`
DROP TABLE IF EXISTS `client_notes`;
CREATE TABLE `client_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `note_text` text NOT NULL,
  `is_important` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_important` (`is_important`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `client_notes`
INSERT INTO `client_notes` VALUES ('3', '3', 'James mentioned they are talking to 2 other agencies. Need to follow up quickly.', '1', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('4', '3', 'Website built on WordPress. Current rankings: page 3-4 for main keywords.', '0', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('5', '4', 'Very active on Instagram (5k followers). Looking to increase Facebook presence.', '0', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('6', '6', 'Super friendly and easy to work with. Always responds within 24 hours.', '0', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('7', '6', 'Newsletter goes out first Monday of each month. Deadline is Friday before.', '1', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('8', '5', 'Michael was very happy with results. Asked to keep in touch for future projects.', '0', '2026-02-08 01:26:25', '2026-02-08 01:26:25', NULL);
INSERT INTO `client_notes` VALUES ('13', '4', 'test', '1', '2026-02-08 01:38:19', '2026-02-08 01:38:19', '1');


-- Table structure for table `client_tags`
DROP TABLE IF EXISTS `client_tags`;
CREATE TABLE `client_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_client_tag` (`client_id`,`tag_name`),
  KEY `idx_client` (`client_id`),
  KEY `idx_tag` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Table structure for table `email_settings`
DROP TABLE IF EXISTS `email_settings`;
CREATE TABLE `email_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_password` varchar(255) DEFAULT NULL,
  `smtp_encryption` varchar(10) DEFAULT 'tls',
  `smtp_from_email` varchar(255) DEFAULT NULL,
  `smtp_from_name` varchar(255) DEFAULT NULL,
  `enable_notifications` tinyint(1) DEFAULT '0',
  `enable_auto_reply` tinyint(1) DEFAULT '0',
  `notification_email` varchar(255) DEFAULT NULL,
  `auto_reply_template` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Table structure for table `invoices`
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT '0.00',
  `total_paid` decimal(10,2) DEFAULT '0.00',
  `total_remaining` decimal(10,2) DEFAULT '0.00',
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_client` (`client_id`),
  KEY `idx_status` (`status`),
  KEY `idx_invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `invoices`
INSERT INTO `invoices` VALUES ('2', '6', '2026-01-31', '600.00', '600.00', '0.00', 'INV-2026-002', '600.00', 'paid', '2026-01-31', '2026-02-28', '2026-02-01', 'Email Newsletter - January', '2026-02-08 01:27:09', '2026-02-08 01:27:09');
INSERT INTO `invoices` VALUES ('3', '5', '2026-01-25', '2400.00', '2400.00', '0.00', 'INV-2026-003', '2400.00', 'paid', '2026-01-25', '2026-02-25', '2026-01-26', 'Blog Posts (6 articles) + 3-Month Content Calendar', '2026-02-08 01:27:09', '2026-02-08 01:27:09');


-- Table structure for table `pages`
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `page_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `pages`
INSERT INTO `pages` VALUES ('1', 'Welcome to Content Catalogz', 'welcome', '<h1>Welcome to Content Catalogz</h1><p>This is a sample page created from the database to demonstrate the CMS functionality.</p>', 'general', 'published', '2026-02-08 01:03:41', '2026-02-08 01:03:41');


-- Table structure for table `quotes`
DROP TABLE IF EXISTS `quotes`;
CREATE TABLE `quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address_street` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_county` varchar(100) DEFAULT NULL,
  `address_postcode` varchar(20) DEFAULT NULL,
  `address_country` varchar(100) DEFAULT 'United Kingdom',
  `services` json DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT '0.00',
  `total_paid` decimal(10,2) DEFAULT '0.00',
  `total_remaining` decimal(10,2) DEFAULT '0.00',
  `service` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'new',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `quotes`
INSERT INTO `quotes` VALUES ('3', 'James O\'Brien', NULL, NULL, 'james@digitalmarkets.ie', 'Digital Markets Ireland', '+353 21 456 7890', '12 Patrick Street', NULL, 'Cork', 'Cork', 'T12 X456', 'Ireland', '[]', '0.00', '0.00', '3200.00', 'SEO Optimization', 'Need to improve our search rankings for key industry terms. Currently on page 3-4 for most searches.', 'in_progress', 'Converting to active client - TEST', '2026-02-08 01:23:24', '2026-02-08 01:31:23');
INSERT INTO `quotes` VALUES ('4', 'Emma Kelly', NULL, NULL, 'ekelly@greenleaf.ie', 'GreenLeaf Organics', '+353 91 234 567', '78 Shop Street', NULL, 'Galway', 'Galway', 'H91 F2X3', 'Ireland', '[{\"cost\": 200, \"name\": \"post management\"}]', '200.00', '0.00', '1800.00', 'Social Media Management', 'We need help managing our Instagram and Facebook. Looking for 3 posts per week plus stories.', 'in_progress', '', '2026-02-08 01:23:24', '2026-02-08 01:32:03');
INSERT INTO `quotes` VALUES ('5', 'Michael Walsh', NULL, NULL, 'm.walsh@coastaldesigns.ie', 'Coastal Designs Studio', '+353 66 123 4567', '23 Main Street', NULL, 'Killarney', 'Kerry', 'V93 X2P5', 'Ireland', NULL, '2400.00', '2400.00', '0.00', 'Content Strategy', 'Starting a new blog and need help with content planning, keyword research, and initial articles.', 'completed', 'Project completed successfully. May return for ongoing work.', '2026-02-08 01:23:24', '2026-02-08 01:23:24');
INSERT INTO `quotes` VALUES ('6', 'Lisa Brennan', NULL, NULL, 'lisa@westcoastcafe.ie', 'West Coast Cafe & Bistro', '+353 86 234 5678', '56 Quay Street', NULL, 'Galway', 'Galway', 'H91 E9K7', 'Ireland', NULL, '1200.00', '600.00', '600.00', 'Email Marketing', 'Want to start a monthly newsletter for our loyal customers. Need help with design and content.', 'in_progress', 'First newsletter sent last week. Customer very happy with results.', '2026-02-08 01:23:24', '2026-02-08 01:23:24');
INSERT INTO `quotes` VALUES ('7', 'Peter Mooney', NULL, NULL, 'petertmooney@outlook.com', 'Moonweaver Designs', '+447930069190', '18 mountain view', 'Castlewellan', 'Newcastle', 'Down', 'BT31 9SG', 'United Kingdom', NULL, '0.00', '0.00', '0.00', 'starter-pack', 'this is a test', 'new', 'this is a test note', '2026-02-08 03:30:43', '2026-02-08 03:30:43');


-- Table structure for table `tasks`
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `client_id` int DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `tasks`
INSERT INTO `tasks` VALUES ('3', 'Follow up on SEO audit', 'Check if they received the report and answer questions', '3', NULL, 'high', 'pending', '2026-02-10', NULL, '2026-02-08 01:24:58', '2026-02-08 01:24:58', NULL);
INSERT INTO `tasks` VALUES ('5', 'Prepare February newsletter', 'Focus on Valentine\'s Day specials', '6', NULL, 'medium', 'in_progress', '2026-02-14', NULL, '2026-02-08 01:24:58', '2026-02-08 01:24:58', NULL);
INSERT INTO `tasks` VALUES ('6', 'Send feedback survey', 'Get testimonial for portfolio', '5', NULL, 'low', 'pending', '2026-02-20', NULL, '2026-02-08 01:24:58', '2026-02-08 01:24:58', NULL);
INSERT INTO `tasks` VALUES ('7', 'follow up', 'test', NULL, NULL, 'high', 'pending', '2026-02-15', NULL, '2026-02-08 01:49:09', '2026-02-08 01:49:09', '1');


-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `users`
INSERT INTO `users` VALUES ('2', 'petertmooney', 'Peter Mooney', 'Peter', 'Mooney', '$2y$10$F2nOeZkNMXm1.JNZHRmNc.ovd2EW7uz4CpMhUBOVCTyIX4n/3aAl2', 'petertmooney@outlook.com', 'superadmin', '2026-02-08 02:11:34');
INSERT INTO `users` VALUES ('3', 'admin', 'System Admin', 'System', 'Admin', '$2y$10$6L/AgcYABix87BN.JxEogekX6W1IvuddEo6R8/71r8jbXuMgAYbbi', 'admin@contentcatalogz.com', 'admin', '2026-02-08 03:39:18');

SET FOREIGN_KEY_CHECKS=1;

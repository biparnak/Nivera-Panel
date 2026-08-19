-- =====================================================================
-- NiveraCloud - Database Schema (MySQL / MariaDB)
-- Full-featured hosting panel with Pterodactyl/PufferPanel/Pelican support
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `niveracloud` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `niveracloud`;

-- Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(32) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `balance` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('active','suspended','banned') NOT NULL DEFAULT 'active',
  `pterodactyl_user_id` INT UNSIGNED DEFAULT NULL,
  `pufferpanel_user_id` INT UNSIGNED DEFAULT NULL,
  `pelican_user_id` INT UNSIGNED DEFAULT NULL,
  `remember_token` VARCHAR(64) DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `username` VARCHAR(64) DEFAULT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT '0',
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reset_token` (`token`),
  KEY `idx_reset_user` (`user_id`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `description` TEXT,
  `icon` VARCHAR(8) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
('Game Servers', 'game-servers', 'Minecraft, Rust, ARK and more', '🎮', 1),
('Web Hosting', 'web-hosting', 'PHP, Node.js, Python apps', '🌐', 2),
('VPS', 'vps', 'Virtual Private Servers', '🖥', 3),
('Databases', 'databases', 'MySQL, PostgreSQL, MongoDB', '💾', 4);

-- Products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) DEFAULT NULL,
  `description` TEXT,
  `icon` VARCHAR(8) DEFAULT '🖥',
  `price_monthly` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `price_yearly` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `setup_fee` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `memory_mb` INT UNSIGNED NOT NULL DEFAULT '1024',
  `disk_mb` INT UNSIGNED NOT NULL DEFAULT '5120',
  `cpu_percent` INT UNSIGNED NOT NULL DEFAULT '100',
  `swap_mb` INT UNSIGNED NOT NULL DEFAULT '512',
  `io_percent` INT UNSIGNED NOT NULL DEFAULT '500',
  `databases` INT UNSIGNED NOT NULL DEFAULT '1',
  `backups` INT UNSIGNED NOT NULL DEFAULT '1',
  `allocations` INT UNSIGNED NOT NULL DEFAULT '1',
  `slots` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Player slots for game servers',
  `node_id` INT UNSIGNED DEFAULT NULL,
  `egg_id` INT UNSIGNED DEFAULT NULL,
  `nest_id` INT UNSIGNED DEFAULT NULL,
  `egg_name` VARCHAR(100) DEFAULT NULL,
  `docker_image` VARCHAR(190) DEFAULT NULL,
  `startup` TEXT,
  `default_variables` TEXT COMMENT 'JSON map',
  `sort_order` INT NOT NULL DEFAULT 0,
  `featured` TINYINT(1) NOT NULL DEFAULT '0',
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_active` (`active`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(32) NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `billing_cycle` ENUM('monthly','yearly','quarterly','semi_annually') NOT NULL DEFAULT 'monthly',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('pending','paid','cancelled','suspended','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(40) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `due_at` DATETIME DEFAULT NULL,
  `next_billing_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_product` (`product_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments (transaction log)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `gateway` VARCHAR(40) NOT NULL DEFAULT 'balance',
  `transaction_id` VARCHAR(128) DEFAULT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `status` ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `metadata` TEXT COMMENT 'JSON',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `idx_payments_user` (`user_id`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Servers
CREATE TABLE IF NOT EXISTS `servers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `order_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(64) NOT NULL,
  `external_id` INT UNSIGNED DEFAULT NULL,
  `external_identifier` VARCHAR(64) DEFAULT NULL,
  `external_uuid` VARCHAR(64) DEFAULT NULL,
  `node_id` INT UNSIGNED DEFAULT NULL,
  `egg_id` INT UNSIGNED DEFAULT NULL,
  `memory_mb` INT UNSIGNED NOT NULL DEFAULT '1024',
  `disk_mb` INT UNSIGNED NOT NULL DEFAULT '5120',
  `cpu_percent` INT UNSIGNED NOT NULL DEFAULT '100',
  `swap_mb` INT UNSIGNED NOT NULL DEFAULT '512',
  `io_percent` INT UNSIGNED NOT NULL DEFAULT '500',
  `databases` INT UNSIGNED NOT NULL DEFAULT '1',
  `backups` INT UNSIGNED NOT NULL DEFAULT '1',
  `allocations` INT UNSIGNED NOT NULL DEFAULT '1',
  `state` ENUM('pending','deploying','running','starting','stopping','stopped','suspended','error') NOT NULL DEFAULT 'pending',
  `panel_type` ENUM('pterodactyl','pufferpanel','pelican') NOT NULL DEFAULT 'pterodactyl',
  `expires_at` DATETIME DEFAULT NULL,
  `suspended_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_servers_user` (`user_id`),
  KEY `idx_servers_panel` (`panel_type`, `external_id`),
  CONSTRAINT `fk_servers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_servers_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `server_id` INT UNSIGNED DEFAULT NULL,
  `ticket_number` VARCHAR(16) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `status` ENUM('open','replied','closed') NOT NULL DEFAULT 'open',
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `department` VARCHAR(40) DEFAULT 'general',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_number` (`ticket_number`),
  KEY `idx_tickets_user` (`user_id`),
  KEY `idx_tickets_status` (`status`),
  CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket messages
CREATE TABLE IF NOT EXISTS `ticket_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `is_staff` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tmsg_ticket` (`ticket_id`),
  CONSTRAINT `fk_tmsg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tmsg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Announcements
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `type` ENUM('info','warning','danger','success') NOT NULL DEFAULT 'info',
  `pinned` TINYINT(1) NOT NULL DEFAULT '0',
  `author_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_announcements_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NOT NULL,
  `type` ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `value` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `max_uses` INT UNSIGNED DEFAULT NULL,
  `used_count` INT UNSIGNED NOT NULL DEFAULT '0',
  `min_amount` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `product_id` INT UNSIGNED DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coupons_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(60) NOT NULL,
  `description` TEXT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_user` (`user_id`),
  KEY `idx_activity_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`key`, `value`) VALUES
('site_name', 'NiveraCloud'),
('site_tagline', 'Premium Game & Application Hosting'),
('site_description', 'Deploy your game servers and applications instantly with NiveraCloud.'),
('currency_symbol', '$'),
('currency_code', 'USD'),
('accent_color', '#7c3aed'),
('logo_url', ''),
('favicon_url', ''),
('theme_mode', 'dark'),
('custom_css', ''),
('custom_js', ''),
('hero_title', 'Premium Game & Application Hosting'),
('hero_subtitle', 'Deploy from 100+ templates in seconds. Full control panel, live console, and one-click power management.'),
('footer_text', 'Powered by NiveraCloud'),
('footer_links', ''),
('registration_enabled', '1'),
('maintenance_mode', '0'),
('maintenance_message', 'We are performing scheduled maintenance. Please check back shortly.'),
('panel_type', 'pterodactyl'),
('pterodactyl_enabled', '0'),
('pterodactyl_url', ''),
('pterodactyl_api_key', ''),
('pterodactyl_node_id', '1'),
('pterodactyl_nest_id', '1'),
('pterodactyl_default_docker_image', ''),
('pterodactyl_startup', ''),
('pufferpanel_enabled', '0'),
('pufferpanel_url', ''),
('pufferpanel_client_token', ''),
('pufferpanel_client_secret', ''),
('pelican_enabled', '0'),
('pelican_url', ''),
('pelican_api_key', ''),
('auto_deploy', '1'),
('auto_create_user', '1'),
('mail_enabled', '0'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from', 'noreply@example.com'),
('adsense_enabled', '0'),
('adsense_client', ''),
('adsense_slot_header', ''),
('adsense_slot_footer', ''),
('enable_coupons', '1'),
('enable_tickets', '1'),
('enable_announcements', '1'),
('enable_balance_system', '1');

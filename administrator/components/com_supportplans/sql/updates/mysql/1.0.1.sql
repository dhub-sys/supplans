CREATE TABLE IF NOT EXISTS `#__supportplans_plans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `plan_type` varchar(20) NOT NULL,
  `purchase_date` date NOT NULL,
  `initial_minutes` int unsigned NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `initial_amount` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__supportplans_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` int unsigned NOT NULL,
  `request_date` date NOT NULL,
  `work_type` varchar(190) NOT NULL,
  `description` text NULL,
  `actual_minutes` int unsigned NOT NULL,
  `billable_minutes` int unsigned NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_request_date` (`request_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

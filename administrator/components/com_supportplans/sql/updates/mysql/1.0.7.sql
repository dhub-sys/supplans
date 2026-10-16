CREATE TABLE IF NOT EXISTS `#__supportplans_serviceplans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `total_minutes` int unsigned NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `min_minutes` int unsigned NOT NULL DEFAULT 30,
  `step_minutes` int unsigned NOT NULL DEFAULT 5,
  `description` text NULL,
  `published` tinyint NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__supportplans_plans` ADD COLUMN `service_plan_id` int unsigned NOT NULL DEFAULT 0 AFTER `form_number`;
ALTER TABLE `#__supportplans_plans` ADD COLUMN `min_minutes` int unsigned NOT NULL DEFAULT 30 AFTER `initial_amount`;
ALTER TABLE `#__supportplans_plans` ADD COLUMN `step_minutes` int unsigned NOT NULL DEFAULT 5 AFTER `min_minutes`;
ALTER TABLE `#__supportplans_plans` ADD COLUMN `plan_description` text NULL AFTER `step_minutes`;

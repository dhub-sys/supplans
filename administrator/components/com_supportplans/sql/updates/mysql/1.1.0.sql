-- v1.1.0: Offers (Προσφορές) module. script.php also creates these tables on install/update.
CREATE TABLE IF NOT EXISTS `#__supportplans_offer_services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(190) NOT NULL,
  `section_title` varchar(255) NOT NULL DEFAULT '',
  `table_label` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `show_description` tinyint NOT NULL DEFAULT 1,
  `in_table` tinyint NOT NULL DEFAULT 1,
  `published` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `modified` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__supportplans_offer_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(190) NOT NULL,
  `doc_title` text NULL,
  `doc_subtitle` varchar(255) NOT NULL DEFAULT '',
  `footer_title` varchar(255) NOT NULL DEFAULT '',
  `pricing_title` varchar(255) NOT NULL DEFAULT '',
  `payment_text` mediumtext NULL,
  `terms_title` varchar(255) NOT NULL DEFAULT '',
  `terms_body` mediumtext NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,
  `show_banks` tinyint NOT NULL DEFAULT 1,
  `items` mediumtext NULL,
  `published` tinyint NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__supportplans_offers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `offer_number` varchar(40) NOT NULL DEFAULT '',
  `offer_date` date NOT NULL,
  `customer_name` varchar(190) NOT NULL DEFAULT '',
  `project_name` varchar(190) NOT NULL DEFAULT '',
  `template_id` int unsigned NOT NULL DEFAULT 0,
  `doc_title` text NULL,
  `doc_subtitle` varchar(255) NOT NULL DEFAULT '',
  `footer_title` varchar(255) NOT NULL DEFAULT '',
  `pricing_title` varchar(255) NOT NULL DEFAULT '',
  `payment_text` mediumtext NULL,
  `terms_title` varchar(255) NOT NULL DEFAULT '',
  `terms_body` mediumtext NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,
  `show_banks` tinyint NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text NULL,
  `created` datetime NOT NULL,
  `modified` datetime NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__supportplans_offer_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int unsigned NOT NULL,
  `service_id` int unsigned NOT NULL DEFAULT 0,
  `ordering` int NOT NULL DEFAULT 0,
  `show_description` tinyint NOT NULL DEFAULT 1,
  `in_table` tinyint NOT NULL DEFAULT 1,
  `section_title` varchar(255) NOT NULL DEFAULT '',
  `table_label` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_offer_id` (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

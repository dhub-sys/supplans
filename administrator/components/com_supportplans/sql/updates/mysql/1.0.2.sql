ALTER TABLE `#__supportplans_plans` ADD COLUMN `customer_name` varchar(190) NOT NULL DEFAULT '' AFTER `user_id`;
ALTER TABLE `#__supportplans_plans` ADD COLUMN `supported_websites` varchar(500) NOT NULL DEFAULT '' AFTER `customer_name`;
ALTER TABLE `#__supportplans_plans` ADD COLUMN `form_number` varchar(80) NOT NULL DEFAULT '' AFTER `supported_websites`;
ALTER TABLE `#__supportplans_entries` MODIFY `request_date` varchar(100) NOT NULL;

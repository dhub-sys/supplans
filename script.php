<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
class Com_SupportplansInstallerScript
{
 public function install($parent):bool{return $this->ensureSchema();}
 public function update($parent):bool{return $this->ensureSchema();}
 public function postflight($type,$parent):bool
 {
  $ok=$this->ensureSchema();
  // Files removed in later versions (Joomla upgrades do not delete them).
  foreach(['/components/com_supportplans/tmpl/offers/default_variables.php'] as $obsolete){ if(is_file(JPATH_SITE.$obsolete)) @unlink(JPATH_SITE.$obsolete); }
  // Bundled system plugins: the launcher opens the Service Hub in a new tab from the
  // Joomla administrator; nocpnb hides the Web357 cookie banner inside the Service Hub.
  $this->installSystemPlugin('supportplanslauncher','System - Support Plans Launcher','Visible top-level Support Plans launcher');
  $this->installSystemPlugin('supportplansnocpnb','System - Support Plans No CPNB','Hides Web357 CPNB inside the Support Plans Service Hub');
  return $ok;
 }
 private function ensureSchema():bool
 {
  $db=Factory::getContainer()->get('DatabaseDriver');

  // Full standalone schema: a fresh Joomla installation does not need any previous version.
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_serviceplans` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL,`billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid',`hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,`subscription_months` int unsigned NOT NULL DEFAULT 1,`total_minutes` int unsigned NOT NULL,`total_cost` decimal(10,2) NOT NULL,`min_minutes` int unsigned NOT NULL DEFAULT 30,`step_minutes` int unsigned NOT NULL DEFAULT 5,`description` text NULL,`pdf_title` varchar(255) NOT NULL DEFAULT '',`pdf_section_title` varchar(255) NOT NULL DEFAULT '',`pdf_work_title` varchar(255) NOT NULL DEFAULT '',`pdf_footer_title` varchar(255) NOT NULL DEFAULT '',`published` tinyint NOT NULL DEFAULT 1,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`),UNIQUE KEY `idx_name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_plans` (`id` int unsigned NOT NULL AUTO_INCREMENT,`sequence_no` int unsigned NOT NULL DEFAULT 0,`user_id` int unsigned NOT NULL DEFAULT 0,`customer_name` varchar(190) NOT NULL DEFAULT '',`supported_websites` varchar(500) NOT NULL DEFAULT '',`form_number` varchar(80) NOT NULL DEFAULT '',`service_plan_id` int unsigned NOT NULL DEFAULT 0,`plan_type` varchar(100) NOT NULL,`billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid',`subscription_months` int unsigned NOT NULL DEFAULT 1,`purchase_date` date NOT NULL,`initial_minutes` int unsigned NOT NULL,`hourly_rate` decimal(10,2) NOT NULL,`initial_amount` decimal(10,2) NOT NULL,`min_minutes` int unsigned NOT NULL DEFAULT 30,`step_minutes` int unsigned NOT NULL DEFAULT 5,`plan_description` text NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`status` varchar(20) NOT NULL DEFAULT 'active',`created` datetime NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `idx_sequence_no` (`sequence_no`),KEY `idx_service_plan_id` (`service_plan_id`),KEY `idx_status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_entries` (`id` int unsigned NOT NULL AUTO_INCREMENT,`plan_id` int unsigned NOT NULL,`request_date` varchar(100) NOT NULL,`work_type` varchar(190) NOT NULL,`description` text NULL,`actual_minutes` int unsigned NOT NULL,`billable_minutes` int unsigned NOT NULL,`cost` decimal(10,2) NOT NULL,`created` datetime NOT NULL,PRIMARY KEY (`id`),KEY `idx_plan_id` (`plan_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  // Offers module (v1.1.0).
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_services` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(190) NOT NULL,`section_title` varchar(255) NOT NULL DEFAULT '',`table_label` varchar(255) NOT NULL DEFAULT '',`body` mediumtext NULL,`price` decimal(10,2) NOT NULL DEFAULT 0.00,`show_description` tinyint NOT NULL DEFAULT 1,`in_table` tinyint NOT NULL DEFAULT 1,`page_break` tinyint NOT NULL DEFAULT 0,`space_before` smallint NOT NULL DEFAULT 5,`published` tinyint NOT NULL DEFAULT 1,`ordering` int NOT NULL DEFAULT 0,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_templates` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(190) NOT NULL,`doc_title` text NULL,`doc_subtitle` varchar(255) NOT NULL DEFAULT '',`footer_title` varchar(255) NOT NULL DEFAULT '',`pricing_title` varchar(255) NOT NULL DEFAULT '',`payment_text` mediumtext NULL,`terms_title` varchar(255) NOT NULL DEFAULT '',`terms_body` mediumtext NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`show_banks` tinyint NOT NULL DEFAULT 1,`items` mediumtext NULL,`variables` mediumtext NULL,`published` tinyint NOT NULL DEFAULT 1,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offers` (`id` int unsigned NOT NULL AUTO_INCREMENT,`offer_number` varchar(40) NOT NULL DEFAULT '',`offer_date` date NOT NULL,`customer_name` varchar(190) NOT NULL DEFAULT '',`project_name` varchar(190) NOT NULL DEFAULT '',`template_id` int unsigned NOT NULL DEFAULT 0,`doc_title` text NULL,`doc_subtitle` varchar(255) NOT NULL DEFAULT '',`footer_title` varchar(255) NOT NULL DEFAULT '',`pricing_title` varchar(255) NOT NULL DEFAULT '',`payment_text` mediumtext NULL,`terms_title` varchar(255) NOT NULL DEFAULT '',`terms_body` mediumtext NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`show_banks` tinyint NOT NULL DEFAULT 1,`status` varchar(20) NOT NULL DEFAULT 'draft',`notes` text NULL,`variables` mediumtext NULL,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`),KEY `idx_status` (`status`),KEY `idx_template_id` (`template_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_items` (`id` int unsigned NOT NULL AUTO_INCREMENT,`offer_id` int unsigned NOT NULL,`service_id` int unsigned NOT NULL DEFAULT 0,`ordering` int NOT NULL DEFAULT 0,`show_description` tinyint NOT NULL DEFAULT 1,`in_table` tinyint NOT NULL DEFAULT 1,`page_break` tinyint NOT NULL DEFAULT 0,`space_before` smallint NOT NULL DEFAULT 5,`section_title` varchar(255) NOT NULL DEFAULT '',`table_label` varchar(255) NOT NULL DEFAULT '',`body` mediumtext NULL,`price` decimal(10,2) NOT NULL DEFAULT 0.00,`discount` decimal(10,2) NOT NULL DEFAULT 0.00,PRIMARY KEY (`id`),KEY `idx_offer_id` (`offer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

  // Upgrade safety for installations coming from older releases.
  $spcols=$db->getTableColumns('#__supportplans_serviceplans',false);
  if(!isset($spcols['billing_mode']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid' AFTER `name`")->execute();
  if(!isset($spcols['hourly_rate']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `billing_mode`")->execute();
  if(!isset($spcols['subscription_months']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `subscription_months` int unsigned NOT NULL DEFAULT 1 AFTER `hourly_rate`")->execute();
  if(!isset($spcols['pdf_title']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `pdf_title` varchar(255) NOT NULL DEFAULT '' AFTER `description`")->execute();
  if(!isset($spcols['pdf_section_title']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `pdf_section_title` varchar(255) NOT NULL DEFAULT '' AFTER `pdf_title`")->execute();
  if(!isset($spcols['pdf_work_title']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `pdf_work_title` varchar(255) NOT NULL DEFAULT '' AFTER `pdf_section_title`")->execute();
  if(!isset($spcols['pdf_footer_title']))$db->setQuery("ALTER TABLE `#__supportplans_serviceplans` ADD `pdf_footer_title` varchar(255) NOT NULL DEFAULT '' AFTER `pdf_work_title`")->execute();
  $db->setQuery("UPDATE `#__supportplans_serviceplans` SET `hourly_rate`=ROUND((`total_cost`/(`total_minutes`/60)),2) WHERE `billing_mode`='prepaid' AND `hourly_rate`=0 AND `total_minutes`>0")->execute();
  $cols=$db->getTableColumns('#__supportplans_plans',false);
  $adds=['subscription_months'=>"ALTER TABLE `#__supportplans_plans` ADD `subscription_months` int unsigned NOT NULL DEFAULT 1 AFTER `billing_mode`",'billing_mode'=>"ALTER TABLE `#__supportplans_plans` ADD `billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid' AFTER `plan_type`",'service_plan_id'=>"ALTER TABLE `#__supportplans_plans` ADD `service_plan_id` int unsigned NOT NULL DEFAULT 0 AFTER `form_number`",'min_minutes'=>"ALTER TABLE `#__supportplans_plans` ADD `min_minutes` int unsigned NOT NULL DEFAULT 30 AFTER `initial_amount`",'step_minutes'=>"ALTER TABLE `#__supportplans_plans` ADD `step_minutes` int unsigned NOT NULL DEFAULT 5 AFTER `min_minutes`",'plan_description'=>"ALTER TABLE `#__supportplans_plans` ADD `plan_description` text NULL AFTER `step_minutes`"];foreach($adds as $c=>$sql)if(!isset($cols[$c]))$db->setQuery($sql)->execute();

  // v1.2.0: custom offer variables.
  foreach(['#__supportplans_offer_templates','#__supportplans_offers'] as $t){
   $c=$db->getTableColumns($t,false);
   if(!isset($c['variables']))$db->setQuery('ALTER TABLE '.$db->quoteName($t).' ADD '.$db->quoteName('variables').' mediumtext NULL')->execute();
  }

  // v1.4.0: per-section page layout (page break / space before) for offer services and offer items.
  foreach(['#__supportplans_offer_services','#__supportplans_offer_items'] as $t){
   $c=$db->getTableColumns($t,false);
   if(!isset($c['page_break']))$db->setQuery('ALTER TABLE '.$db->quoteName($t).' ADD '.$db->quoteName('page_break').' tinyint NOT NULL DEFAULT 0')->execute();
   if(!isset($c['space_before']))$db->setQuery('ALTER TABLE '.$db->quoteName($t).' ADD '.$db->quoteName('space_before').' smallint NOT NULL DEFAULT 5')->execute();
  }

  // No default service plans are created or restored.
  // Existing user-created/renamed plans remain untouched during updates.
  return true;
 }

 /**
  * Copies a bundled system plugin into /plugins/system and registers it in #__extensions.
  * Uses plain PHP file functions and only the #__extensions columns that exist, so it
  * works on Joomla 6 without the backward-compatibility plugin.
  */
 private function installSystemPlugin(string $element,string $name,string $description): void
 {
  try{
   $src=JPATH_ADMINISTRATOR.'/components/com_supportplans/plugins/system/'.$element;
   $dst=JPATH_PLUGINS.'/system/'.$element;
   if(!is_dir($src)) return;
   if(!is_dir($dst) && !@mkdir($dst,0755,true) && !is_dir($dst)) throw new \RuntimeException('Cannot create '.$dst);
   foreach(['.php','.xml'] as $ext){
    if(!@copy($src.'/'.$element.$ext,$dst.'/'.$element.$ext)) throw new \RuntimeException('Cannot copy '.$element.$ext.' to '.$dst);
    if(function_exists('opcache_invalidate')) @opcache_invalidate($dst.'/'.$element.$ext,true);
   }

   $db=Factory::getContainer()->get('DatabaseDriver');
   $q=$db->getQuery(true)->select('extension_id')->from($db->quoteName('#__extensions'))
     ->where($db->quoteName('type').'='.$db->quote('plugin'))
     ->where($db->quoteName('folder').'='.$db->quote('system'))
     ->where($db->quoteName('element').'='.$db->quote($element));
   $id=(int)$db->setQuery($q)->loadResult();
   $version='1.0.0';
   $xml=@simplexml_load_file($dst.'/'.$element.'.xml');
   if($xml && isset($xml->version)) $version=(string)$xml->version;
   $cache=json_encode(['name'=>$name,'type'=>'plugin','version'=>$version,'group'=>'system','description'=>$description],JSON_UNESCAPED_SLASHES);
   if(!$id){
    $row=['name'=>$name,'type'=>'plugin','element'=>$element,'folder'=>'system','client_id'=>0,'enabled'=>1,'access'=>1,'protected'=>0,'locked'=>0,
      'manifest_cache'=>$cache,'params'=>'{}','custom_data'=>'','system_data'=>'{}','ordering'=>0,'state'=>0,'package_id'=>0,'changelogurl'=>'','note'=>''];
    // Column sets differ between Joomla versions (e.g. system_data was removed in Joomla 6).
    $row=array_intersect_key($row,$db->getTableColumns('#__extensions',false));
    $obj=(object)$row;
    $db->insertObject('#__extensions',$obj);
   }else{
    $q=$db->getQuery(true)->update($db->quoteName('#__extensions'))
      ->set($db->quoteName('enabled').'=1')
      ->set($db->quoteName('manifest_cache').'='.$db->quote($cache))
      ->where($db->quoteName('extension_id').'='.$id);
    $db->setQuery($q)->execute();
   }
   try{Factory::getApplication()->getCache('com_plugins')->clean();}catch(\Throwable $e){}
  }catch(\Throwable $e){
   try{Factory::getApplication()->enqueueMessage('Support Plans: δεν ήταν δυνατή η εγκατάσταση του plugin '.$element.' ('.$e->getMessage().').','warning');}catch(\Throwable $ignored){}
  }
 }
}

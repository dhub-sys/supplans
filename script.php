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
  $this->syncMedia($parent);
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
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_services` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(190) NOT NULL,`section_title` varchar(255) NOT NULL DEFAULT '',`table_label` varchar(255) NOT NULL DEFAULT '',`body` mediumtext NULL,`lists` mediumtext NULL,`price` decimal(10,2) NOT NULL DEFAULT 0.00,`show_description` tinyint NOT NULL DEFAULT 1,`in_table` tinyint NOT NULL DEFAULT 1,`page_break` tinyint NOT NULL DEFAULT 0,`space_before` smallint NOT NULL DEFAULT 5,`published` tinyint NOT NULL DEFAULT 1,`ordering` int NOT NULL DEFAULT 0,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_templates` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(190) NOT NULL,`doc_title` text NULL,`doc_subtitle` varchar(255) NOT NULL DEFAULT '',`footer_title` varchar(255) NOT NULL DEFAULT '',`pricing_title` varchar(255) NOT NULL DEFAULT '',`payment_text` mediumtext NULL,`terms_title` varchar(255) NOT NULL DEFAULT '',`terms_body` mediumtext NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`show_banks` tinyint NOT NULL DEFAULT 1,`show_header` tinyint NOT NULL DEFAULT 1,`header_logo` smallint NOT NULL DEFAULT 28,`header_gap` smallint NOT NULL DEFAULT 6,`pricing_break` tinyint NOT NULL DEFAULT 0,`pricing_space` smallint NOT NULL DEFAULT 5,`terms_break` tinyint NOT NULL DEFAULT 0,`terms_space` smallint NOT NULL DEFAULT 5,`header_text` varchar(255) NOT NULL DEFAULT '',`intro_title` varchar(255) NOT NULL DEFAULT '',`intro_body` mediumtext NULL,`intro_lists` mediumtext NULL,`items` mediumtext NULL,`variables` mediumtext NULL,`published` tinyint NOT NULL DEFAULT 1,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offers` (`id` int unsigned NOT NULL AUTO_INCREMENT,`offer_number` varchar(40) NOT NULL DEFAULT '',`offer_date` date NOT NULL,`customer_name` varchar(190) NOT NULL DEFAULT '',`project_name` varchar(190) NOT NULL DEFAULT '',`template_id` int unsigned NOT NULL DEFAULT 0,`doc_title` text NULL,`doc_subtitle` varchar(255) NOT NULL DEFAULT '',`footer_title` varchar(255) NOT NULL DEFAULT '',`pricing_title` varchar(255) NOT NULL DEFAULT '',`payment_text` mediumtext NULL,`terms_title` varchar(255) NOT NULL DEFAULT '',`terms_body` mediumtext NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`show_banks` tinyint NOT NULL DEFAULT 1,`show_header` tinyint NOT NULL DEFAULT 1,`header_logo` smallint NOT NULL DEFAULT 28,`header_gap` smallint NOT NULL DEFAULT 6,`pricing_break` tinyint NOT NULL DEFAULT 0,`pricing_space` smallint NOT NULL DEFAULT 5,`terms_break` tinyint NOT NULL DEFAULT 0,`terms_space` smallint NOT NULL DEFAULT 5,`header_text` varchar(255) NOT NULL DEFAULT '',`intro_title` varchar(255) NOT NULL DEFAULT '',`intro_body` mediumtext NULL,`intro_lists` mediumtext NULL,`status` varchar(20) NOT NULL DEFAULT 'draft',`notes` text NULL,`variables` mediumtext NULL,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`),KEY `idx_status` (`status`),KEY `idx_template_id` (`template_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_offer_items` (`id` int unsigned NOT NULL AUTO_INCREMENT,`offer_id` int unsigned NOT NULL,`service_id` int unsigned NOT NULL DEFAULT 0,`ordering` int NOT NULL DEFAULT 0,`show_description` tinyint NOT NULL DEFAULT 1,`in_table` tinyint NOT NULL DEFAULT 1,`page_break` tinyint NOT NULL DEFAULT 0,`space_before` smallint NOT NULL DEFAULT 5,`section_title` varchar(255) NOT NULL DEFAULT '',`table_label` varchar(255) NOT NULL DEFAULT '',`body` mediumtext NULL,`lists` mediumtext NULL,`price` decimal(10,2) NOT NULL DEFAULT 0.00,`discount` decimal(10,2) NOT NULL DEFAULT 0.00,PRIMARY KEY (`id`),KEY `idx_offer_id` (`offer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

  // Upgrade safety for installations coming from any older release: every column the
  // current version uses is added when missing. Columns are added without AFTER, so the
  // order in which old installations lack them does not matter.
  $columns=[
   '#__supportplans_serviceplans'=>[
    'billing_mode'=>"varchar(20) NOT NULL DEFAULT 'prepaid'",'hourly_rate'=>'decimal(10,2) NOT NULL DEFAULT 0.00','subscription_months'=>'int unsigned NOT NULL DEFAULT 1',
    'total_minutes'=>'int unsigned NOT NULL DEFAULT 0','total_cost'=>'decimal(10,2) NOT NULL DEFAULT 0.00','min_minutes'=>'int unsigned NOT NULL DEFAULT 30','step_minutes'=>'int unsigned NOT NULL DEFAULT 5',
    'description'=>'text NULL','pdf_title'=>"varchar(255) NOT NULL DEFAULT ''",'pdf_section_title'=>"varchar(255) NOT NULL DEFAULT ''",'pdf_work_title'=>"varchar(255) NOT NULL DEFAULT ''",'pdf_footer_title'=>"varchar(255) NOT NULL DEFAULT ''",
    'published'=>'tinyint NOT NULL DEFAULT 1','modified'=>'datetime NULL',
   ],
   '#__supportplans_plans'=>[
    'sequence_no'=>'int unsigned NOT NULL DEFAULT 0','user_id'=>'int unsigned NOT NULL DEFAULT 0','customer_name'=>"varchar(190) NOT NULL DEFAULT ''",'supported_websites'=>"varchar(500) NOT NULL DEFAULT ''",'form_number'=>"varchar(80) NOT NULL DEFAULT ''",
    'service_plan_id'=>'int unsigned NOT NULL DEFAULT 0','billing_mode'=>"varchar(20) NOT NULL DEFAULT 'prepaid'",'subscription_months'=>'int unsigned NOT NULL DEFAULT 1',
    'min_minutes'=>'int unsigned NOT NULL DEFAULT 30','step_minutes'=>'int unsigned NOT NULL DEFAULT 5','plan_description'=>'text NULL','vat_rate'=>'decimal(5,2) NOT NULL DEFAULT 24.00','status'=>"varchar(20) NOT NULL DEFAULT 'active'",
   ],
   '#__supportplans_entries'=>[
    'description'=>'text NULL','actual_minutes'=>'int unsigned NOT NULL DEFAULT 0','billable_minutes'=>'int unsigned NOT NULL DEFAULT 0','cost'=>'decimal(10,2) NOT NULL DEFAULT 0.00',
   ],
   '#__supportplans_offer_services'=>[
    'page_break'=>'tinyint NOT NULL DEFAULT 0','space_before'=>'smallint NOT NULL DEFAULT 5','lists'=>'mediumtext NULL',
   ],
   '#__supportplans_offer_items'=>[
    'page_break'=>'tinyint NOT NULL DEFAULT 0','space_before'=>'smallint NOT NULL DEFAULT 5','lists'=>'mediumtext NULL',
   ],
   '#__supportplans_offer_templates'=>[
    'variables'=>'mediumtext NULL','show_header'=>'tinyint NOT NULL DEFAULT 1','header_text'=>"varchar(255) NOT NULL DEFAULT ''",'header_logo'=>'smallint NOT NULL DEFAULT 28','header_gap'=>'smallint NOT NULL DEFAULT 6',
    'pricing_break'=>'tinyint NOT NULL DEFAULT 0','pricing_space'=>'smallint NOT NULL DEFAULT 5','terms_break'=>'tinyint NOT NULL DEFAULT 0','terms_space'=>'smallint NOT NULL DEFAULT 5','intro_title'=>"varchar(255) NOT NULL DEFAULT ''",'intro_body'=>'mediumtext NULL','intro_lists'=>'mediumtext NULL',
   ],
   '#__supportplans_offers'=>[
    'variables'=>'mediumtext NULL','show_header'=>'tinyint NOT NULL DEFAULT 1','header_text'=>"varchar(255) NOT NULL DEFAULT ''",'header_logo'=>'smallint NOT NULL DEFAULT 28','header_gap'=>'smallint NOT NULL DEFAULT 6',
    'pricing_break'=>'tinyint NOT NULL DEFAULT 0','pricing_space'=>'smallint NOT NULL DEFAULT 5','terms_break'=>'tinyint NOT NULL DEFAULT 0','terms_space'=>'smallint NOT NULL DEFAULT 5','intro_title'=>"varchar(255) NOT NULL DEFAULT ''",'intro_body'=>'mediumtext NULL','intro_lists'=>'mediumtext NULL',
   ],
  ];
  foreach($columns as $table=>$defs){
   $existing=$db->getTableColumns($table,false);
   foreach($defs as $name=>$definition){
    if(!isset($existing[$name])){
     $db->setQuery('ALTER TABLE '.$db->quoteName($table).' ADD '.$db->quoteName($name).' '.$definition)->execute();
    }
   }
  }
  // Very old releases stored the request date as DATE; free text such as "Ιούνιος 2025" is allowed now.
  $entryCols=$db->getTableColumns('#__supportplans_entries',true); // column => type
  if(isset($entryCols['request_date']) && stripos((string)$entryCols['request_date'],'varchar')===false){
   $db->setQuery("ALTER TABLE `#__supportplans_entries` MODIFY `request_date` varchar(100) NOT NULL")->execute();
  }
  // Fill sensible values into rows that predate these columns (safe to run on every install/update).
  $db->setQuery("UPDATE `#__supportplans_plans` SET `sequence_no`=`id` WHERE `sequence_no`=0")->execute();
  $db->setQuery("UPDATE `#__supportplans_plans` p JOIN `#__supportplans_serviceplans` s ON s.`name`=p.`plan_type` SET p.`service_plan_id`=s.`id` WHERE p.`service_plan_id`=0")->execute();
  $db->setQuery("UPDATE `#__supportplans_serviceplans` SET `hourly_rate`=ROUND((`total_cost`/(`total_minutes`/60)),2) WHERE `billing_mode`='prepaid' AND `hourly_rate`=0 AND `total_minutes`>0")->execute();

  // No default service plans are created or restored.
  // Existing user-created/renamed plans remain untouched during updates.
  return true;
 }

 /**
  * Copies a bundled system plugin into /plugins/system and registers it in #__extensions.
  * Uses plain PHP file functions and only the #__extensions columns that exist, so it
  * works on Joomla 6 without the backward-compatibility plugin.
  */
 // Copies the stylesheets, scripts and images again with readable permissions. A failed
 // earlier update rolls back the media files it created, and some hosts copy them unreadable,
 // which leaves the Service Hub without any styling.
 private function syncMedia($parent): void
 {
  try{
   $src=rtrim((string)$parent->getParent()->getPath('source'),'/').'/media';
   $dst=JPATH_ROOT.'/media/com_supportplans';
   if(!is_dir($src)) return;
   $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::SELF_FIRST);
   if(!is_dir($dst)) @mkdir($dst,0755,true);
   @chmod($dst,0755);
   foreach($it as $file){
    $target=$dst.'/'.substr($file->getPathname(),strlen($src)+1);
    if($file->isDir()){ if(!is_dir($target)) @mkdir($target,0755,true); @chmod($target,0755); continue; }
    if(!is_file($target) || filesize($target)!==$file->getSize() || md5_file($target)!==md5_file($file->getPathname())) @copy($file->getPathname(),$target);
    @chmod($target,0644);
   }
  }catch(\Throwable $e){
   Factory::getApplication()->enqueueMessage('Support Plans: media files could not be refreshed ('.$e->getMessage().').','warning');
  }
 }
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

<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
class Com_SupportplansInstallerScript
{
 public function install($parent):bool{return $this->ensureSchema();}
 public function update($parent):bool{return $this->ensureSchema();}
 public function postflight($type,$parent):bool
 {
  $this->installNoCpnb();
  $ok=$this->ensureSchema();
  try{
   $src=JPATH_ADMINISTRATOR.'/components/com_supportplans/plugins/system/supportplanslauncher';
   $dst=JPATH_PLUGINS.'/system/supportplanslauncher';
   if(is_dir($src)){
    if(is_dir($dst)) \Joomla\CMS\Filesystem\Folder::delete($dst);
    \Joomla\CMS\Filesystem\Folder::create($dst);
    \Joomla\CMS\Filesystem\File::copy($src.'/supportplanslauncher.php',$dst.'/supportplanslauncher.php',null,true);
    \Joomla\CMS\Filesystem\File::copy($src.'/supportplanslauncher.xml',$dst.'/supportplanslauncher.xml',null,true);
    $db=Factory::getContainer()->get('DatabaseDriver');
    $q=$db->getQuery(true)->select('extension_id')->from($db->quoteName('#__extensions'))
      ->where($db->quoteName('type').'='.$db->quote('plugin'))
      ->where($db->quoteName('folder').'='.$db->quote('system'))
      ->where($db->quoteName('element').'='.$db->quote('supportplanslauncher'));
    $pid=(int)$db->setQuery($q)->loadResult();
    $mc=json_encode(['name'=>'System - Support Plans Launcher','type'=>'plugin','version'=>'1.0.49','group'=>'system','description'=>'Visible top-level Support Plans launcher']);
    if(!$pid){
      $o=(object)['name'=>'System - Support Plans Launcher','type'=>'plugin','element'=>'supportplanslauncher','folder'=>'system','client_id'=>0,'enabled'=>1,'access'=>1,'protected'=>0,'manifest_cache'=>$mc,'params'=>'{}','custom_data'=>'','system_data'=>'{}'];
      $db->insertObject('#__extensions',$o);
    }else{
      $q=$db->getQuery(true)->update($db->quoteName('#__extensions'))
        ->set($db->quoteName('enabled').'=1')
        ->set($db->quoteName('manifest_cache').'='.$db->quote($mc))
        ->where($db->quoteName('extension_id').'='.$pid);
      $db->setQuery($q)->execute();
    }
    Factory::getApplication()->getCache('com_plugins')->clean();
   }
  }catch(\Throwable $e){}
  return $ok;
 }
 private function ensureSchema():bool
 {
  $db=Factory::getContainer()->get('DatabaseDriver');

  // Full standalone schema: a fresh Joomla installation does not need any previous version.
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_serviceplans` (`id` int unsigned NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL,`billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid',`hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,`subscription_months` int unsigned NOT NULL DEFAULT 1,`total_minutes` int unsigned NOT NULL,`total_cost` decimal(10,2) NOT NULL,`min_minutes` int unsigned NOT NULL DEFAULT 30,`step_minutes` int unsigned NOT NULL DEFAULT 5,`description` text NULL,`pdf_title` varchar(255) NOT NULL DEFAULT '',`pdf_section_title` varchar(255) NOT NULL DEFAULT '',`pdf_work_title` varchar(255) NOT NULL DEFAULT '',`pdf_footer_title` varchar(255) NOT NULL DEFAULT '',`published` tinyint NOT NULL DEFAULT 1,`created` datetime NOT NULL,`modified` datetime NULL,PRIMARY KEY (`id`),UNIQUE KEY `idx_name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_plans` (`id` int unsigned NOT NULL AUTO_INCREMENT,`sequence_no` int unsigned NOT NULL DEFAULT 0,`user_id` int unsigned NOT NULL DEFAULT 0,`customer_name` varchar(190) NOT NULL DEFAULT '',`supported_websites` varchar(500) NOT NULL DEFAULT '',`form_number` varchar(80) NOT NULL DEFAULT '',`service_plan_id` int unsigned NOT NULL DEFAULT 0,`plan_type` varchar(100) NOT NULL,`billing_mode` varchar(20) NOT NULL DEFAULT 'prepaid',`subscription_months` int unsigned NOT NULL DEFAULT 1,`purchase_date` date NOT NULL,`initial_minutes` int unsigned NOT NULL,`hourly_rate` decimal(10,2) NOT NULL,`initial_amount` decimal(10,2) NOT NULL,`min_minutes` int unsigned NOT NULL DEFAULT 30,`step_minutes` int unsigned NOT NULL DEFAULT 5,`plan_description` text NULL,`vat_rate` decimal(5,2) NOT NULL DEFAULT 24.00,`status` varchar(20) NOT NULL DEFAULT 'active',`created` datetime NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `idx_sequence_no` (`sequence_no`),KEY `idx_service_plan_id` (`service_plan_id`),KEY `idx_status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
  $db->setQuery("CREATE TABLE IF NOT EXISTS `#__supportplans_entries` (`id` int unsigned NOT NULL AUTO_INCREMENT,`plan_id` int unsigned NOT NULL,`request_date` varchar(100) NOT NULL,`work_type` varchar(190) NOT NULL,`description` text NULL,`actual_minutes` int unsigned NOT NULL,`billable_minutes` int unsigned NOT NULL,`cost` decimal(10,2) NOT NULL,`created` datetime NOT NULL,PRIMARY KEY (`id`),KEY `idx_plan_id` (`plan_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

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

  // No default service plans are created or restored.
  // Existing user-created/renamed plans remain untouched during updates.
  return true;
 }

 private function installNoCpnb(): void
 {
  try {
   $src=JPATH_ADMINISTRATOR.'/components/com_supportplans/plugins/system/supportplansnocpnb';
   $dst=JPATH_PLUGINS.'/system/supportplansnocpnb';
   if(!is_dir($src)) return;
   if(!is_dir($dst)) \Joomla\CMS\Filesystem\Folder::create($dst);
   \Joomla\CMS\Filesystem\File::copy($src.'/supportplansnocpnb.php',$dst.'/supportplansnocpnb.php',null,true);
   \Joomla\CMS\Filesystem\File::copy($src.'/supportplansnocpnb.xml',$dst.'/supportplansnocpnb.xml',null,true);

   $db=\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
   $q=$db->getQuery(true)->select('extension_id')->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('type').'='.$db->quote('plugin'))
    ->where($db->quoteName('folder').'='.$db->quote('system'))
    ->where($db->quoteName('element').'='.$db->quote('supportplansnocpnb'));
   $id=(int)$db->setQuery($q)->loadResult();
   $cache=json_encode(['name'=>'System - Support Plans No CPNB','type'=>'plugin','version'=>'1.0.0','group'=>'system'],JSON_UNESCAPED_SLASHES);
   if(!$id){
    $row=(object)['name'=>'System - Support Plans No CPNB','type'=>'plugin','element'=>'supportplansnocpnb','folder'=>'system',
     'client_id'=>0,'enabled'=>1,'access'=>1,'protected'=>0,'manifest_cache'=>$cache,'params'=>'{}','custom_data'=>'','system_data'=>'{}'];
    $db->insertObject('#__extensions',$row);
   } else {
    $q=$db->getQuery(true)->update($db->quoteName('#__extensions'))->set($db->quoteName('enabled').'=1')
     ->set($db->quoteName('manifest_cache').'='.$db->quote($cache))->where($db->quoteName('extension_id').'='.$id);
    $db->setQuery($q)->execute();
   }
   \Joomla\CMS\Factory::getApplication()->getCache('com_plugins')->clean();
  } catch (\Throwable $e) {}
 }
}

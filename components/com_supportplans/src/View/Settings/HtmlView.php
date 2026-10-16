<?php
namespace Support\Component\Supportplans\Site\View\Settings;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;

class HtmlView extends BaseHtmlView
{
 public $params;
 public function display($tpl=null):void
 {
  $db=Factory::getContainer()->get('DatabaseDriver');
  $q=$db->getQuery(true)->select($db->quoteName('params'))->from($db->quoteName('#__extensions'))
    ->where($db->quoteName('type').'='.$db->quote('component'))
    ->where($db->quoteName('element').'='.$db->quote('com_supportplans'));
  $raw=(string)$db->setQuery($q)->loadResult();
  $registry=new \Joomla\Registry\Registry;
  $registry->loadString($raw,'JSON');
  $this->params=$registry;
  parent::display($tpl);
 }
}

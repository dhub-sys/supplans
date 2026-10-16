<?php
namespace Support\Component\Supportplans\Administrator\View\Plans;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
class HtmlView extends BaseHtmlView
{
 public $params;
 public array $plans=[]; public array $servicePlans=[]; public ?object $selected=null; public array $entries=[]; public string $mode='list';
 public function display($tpl=null):void{$this->params=ComponentHelper::getParams('com_supportplans');$app=Factory::getApplication();$db=Factory::getContainer()->get('DatabaseDriver');$id=$app->input->getInt('plan_id');$this->mode=$app->input->getCmd('mode',$id?'edit':'list');$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_serviceplans'))->order('published DESC,name ASC');$this->servicePlans=$db->setQuery($q)->loadObjectList();$q=$db->getQuery(true)->select('p.*')->from($db->quoteName('#__supportplans_plans','p'))->order('p.sequence_no ASC,p.id ASC');$this->plans=$db->setQuery($q)->loadObjectList();if($id){foreach($this->plans as $p)if((int)$p->id===$id){$this->selected=$p;break;}if($this->selected){$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_entries'))->where('plan_id='.(int)$id)->order('id ASC');$this->entries=$db->setQuery($q)->loadObjectList();}}$this->addToolbar();parent::display($tpl);}
 private function addToolbar():void{if($this->mode==='new'){ToolbarHelper::title('Νέο πακέτο','plus');ToolbarHelper::apply('plan.apply');ToolbarHelper::save('plan.save');ToolbarHelper::save2new('plan.save2new');ToolbarHelper::cancel('plan.cancel');return;}if($this->selected){ToolbarHelper::title('Επεξεργασία πακέτου','pencil-alt');ToolbarHelper::apply('plan.apply');ToolbarHelper::save('plan.save');ToolbarHelper::save2new('plan.save2new');ToolbarHelper::cancel('plan.cancel','JTOOLBAR_CLOSE');return;}ToolbarHelper::title('Πακέτα υποστήριξης','list');ToolbarHelper::addNew('plan.add','Νέο Πακέτο');ToolbarHelper::addNew('serviceplan.add','Νέο Πλάνο');ToolbarHelper::preferences('com_supportplans');}
}

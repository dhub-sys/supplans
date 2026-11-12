<?php
namespace Support\Component\Supportplans\Site\View\Plans;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
class HtmlView extends BaseHtmlView
{
 public $params;
 public array $plans=[]; public array $servicePlans=[]; public ?object $selected=null; public array $entries=[]; public string $mode='list';
 public function display($tpl=null):void{$this->params=ComponentHelper::getParams('com_supportplans');$app=Factory::getApplication();$db=Factory::getContainer()->get('DatabaseDriver');$id=$app->input->getInt('plan_id');$this->mode=$app->input->getCmd('mode',$id?'edit':'list');if($this->mode==='list'){/* The list of packages is the documents page (same look, by number). */$app->redirect(\Support\Component\Supportplans\Site\Helper\OfferHelper::url('view=documents&type=plan',false));return;}$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_serviceplans'))->order('published DESC,name ASC');$this->servicePlans=$db->setQuery($q)->loadObjectList();$q=$db->getQuery(true)->select('p.*')->from($db->quoteName('#__supportplans_plans','p'))->order('p.sequence_no ASC,p.id ASC');$this->plans=$db->setQuery($q)->loadObjectList();if($this->mode==='list'){$yr=\Support\Component\Supportplans\Site\Helper\Year::get();$cur=\Support\Component\Supportplans\Site\Helper\Year::isCurrent();/* Packages of the selected year; in the current year also the still active older ones. */$this->plans=array_values(array_filter($this->plans,static fn($p)=>\Support\Component\Supportplans\Site\Helper\Year::inYear((string)$p->purchase_date,$yr)||($cur&&($p->status??'active')==='active'&&(int)substr((string)$p->purchase_date,0,4)<$yr)));}if($id){foreach($this->plans as $p)if((int)$p->id===$id){$this->selected=$p;break;}if($this->selected){$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_entries'))->where('plan_id='.(int)$id)->order('id ASC');$this->entries=\Support\Component\Supportplans\Site\Helper\EntryOrder::sort($db->setQuery($q)->loadObjectList());}}parent::display($tpl);}
}

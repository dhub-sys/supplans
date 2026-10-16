<?php
namespace Support\Component\Supportplans\Site\View\Report;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
class HtmlView extends BaseHtmlView
{
    public object $plan; public array $entries=[]; public $params;
    public function display($tpl=null):void
    {
        $app=Factory::getApplication(); $this->params=ComponentHelper::getParams('com_supportplans'); $session=$app->getSession(); $userId=(int)$session->get('supportplans.auth.user_id',0);
        if(!$userId || !$session->get('supportplans.otp.verified',false)) throw new \RuntimeException('Login required',403);
        $db=Factory::getContainer()->get('DatabaseDriver'); $id=$app->input->getInt('plan_id');
        $q=$db->getQuery(true)->select('p.*,u.name AS user_name,u.email')->from('#__supportplans_plans AS p')->leftJoin('#__users AS u ON u.id=p.user_id')->where('p.id='.(int)$id);
        $plan=$db->setQuery($q)->loadObject(); if(!$plan)throw new \RuntimeException('Plan not found',404); $this->plan=$plan;
        $q=$db->getQuery(true)->select('*')->from('#__supportplans_entries')->where('plan_id='.(int)$id)->order('request_date ASC,id ASC'); $this->entries=$db->setQuery($q)->loadObjectList(); parent::display($tpl);
    }
}

<?php
namespace Support\Component\Supportplans\Site\View\Report;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\EntryOrder;
use Support\Component\Supportplans\Site\Helper\ShareLink;
class HtmlView extends BaseHtmlView
{
    public object $plan; public array $entries=[]; public $params; public bool $shared=false;
    public function display($tpl=null):void
    {
        $app=Factory::getApplication(); $this->params=ComponentHelper::getParams('com_supportplans'); $session=$app->getSession(); $userId=(int)$session->get('supportplans.auth.user_id',0);
        $db=Factory::getContainer()->get('DatabaseDriver');
        $token=(string)$app->input->getString('t','');
        if($token!==''){
            // Customer link: read-only report for the package that owns the token.
            $shared=ShareLink::plan($token); if(!$shared) throw new \RuntimeException('Not found',404);
            $id=(int)$shared->id; $this->shared=true; ShareLink::protectPage();
        }else{
            if(!$userId || !$session->get('supportplans.otp.verified',false)) throw new \RuntimeException('Login required',403);
            $id=$app->input->getInt('plan_id');
        }
        $q=$db->getQuery(true)->select('p.*,u.name AS user_name,u.email')->from('#__supportplans_plans AS p')->leftJoin('#__users AS u ON u.id=p.user_id')->where('p.id='.(int)$id);
        $plan=$db->setQuery($q)->loadObject(); if(!$plan)throw new \RuntimeException('Plan not found',404); $this->plan=$plan;
        $q=$db->getQuery(true)->select('*')->from('#__supportplans_entries')->where('plan_id='.(int)$id)->order('id ASC'); $this->entries=EntryOrder::sort($db->setQuery($q)->loadObjectList());
        // Saved Pdf file name: customer and form number (the browser uses the page title).
        $app->getDocument()->setTitle(self::fileTitle($plan));
        parent::display($tpl);
    }
    public static function fileTitle(object $plan): string
    {
        $form = trim((string) ($plan->form_number ?? '')) ?: (string) (int) ($plan->sequence_no ?? 0);
        $name = trim(preg_replace('~[\\\\/:*?"<>|]+~u', ' ', (string) $plan->customer_name));
        return trim($name . ' - ' . $form, ' -');
    }
}

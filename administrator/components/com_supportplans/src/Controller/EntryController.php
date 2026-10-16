<?php
namespace Support\Component\Supportplans\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
class EntryController extends BaseController
{
    public function save(): void
    {
        Session::checkToken() or jexit('Invalid Token'); $app=Factory::getApplication(); $in=$app->input; $db=Factory::getContainer()->get('DatabaseDriver');
        $planId=$in->getInt('plan_id');
        $q=$db->getQuery(true)->select(['billing_mode','hourly_rate','min_minutes','step_minutes'])->from($db->quoteName('#__supportplans_plans'))->where($db->quoteName('id').'='.(int)$planId);
        $plan=$db->setQuery($q)->loadObject(); if(!$plan){throw new \RuntimeException('Δεν βρέθηκε το πακέτο.');}
        $mode=$plan->billing_mode??'prepaid';
        if($mode==='subscription'){
            $actual=max(0,$in->getInt('actual_minutes',0)); $billable=0; $cost=0;
        }else{
            if((float)$plan->hourly_rate<=0){throw new \RuntimeException('Δεν έχει οριστεί ωριαία χρέωση στο πλάνο.');}
            $min=max(1,(int)$plan->min_minutes); $step=max(1,(int)$plan->step_minutes); $actual=max($min,$in->getInt('actual_minutes',$min));
            $billable=$actual<=$min?$min:(int)($min + ceil(($actual-$min)/$step)*$step); $rate=(float)$plan->hourly_rate;
            $cost=round(($billable/60)*$rate,2);
        }
        $cols=['plan_id','request_date','work_type','description','actual_minutes','billable_minutes','cost','created'];
        $vals=[(int)$planId,$db->quote($in->getString('request_date', date('d/m/Y'))),$db->quote($in->getString('work_type')),$db->quote($in->getString('description')),(int)$actual,(int)$billable,(float)$cost,$db->quote(Factory::getDate()->toSql())];
        $query=$db->getQuery(true)->insert($db->quoteName('#__supportplans_entries'))->columns($db->quoteName($cols))->values(implode(',',$vals));
        $db->setQuery($query)->execute(); $app->enqueueMessage('Η εργασία καταχωρίστηκε.'); $app->redirect(Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&plan_id=&Itemid=785'.$planId,false));
    }
    public function delete(): void
    {
        Session::checkToken() or jexit('Invalid Token'); $app=Factory::getApplication(); $db=Factory::getContainer()->get('DatabaseDriver'); $id=$app->input->getInt('id'); $planId=$app->input->getInt('plan_id');
        $q=$db->getQuery(true)->delete($db->quoteName('#__supportplans_entries'))->where($db->quoteName('id').'='.(int)$id); $db->setQuery($q)->execute();
        $app->enqueueMessage('Η εργασία διαγράφηκε.'); $app->redirect(Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&plan_id=&Itemid=785'.$planId,false));
    }
}

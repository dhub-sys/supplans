<?php
namespace Support\Component\Supportplans\Site\View\Otp;

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $app=Factory::getApplication();
        if (!(int)$app->getSession()->get('supportplans.pending.user_id',0)) {
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)), false)); return;
        }
        $app->input->set('tmpl','component'); parent::display($tpl);
    }
}

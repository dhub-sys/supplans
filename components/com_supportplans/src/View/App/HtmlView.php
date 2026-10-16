<?php
namespace Support\Component\Supportplans\Site\View\App;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $session = $app->getSession();
        if ((int) $session->get('supportplans.auth.user_id', 0) > 0 && $session->get('supportplans.otp.verified', false)) {
            $app->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)), false));
            return;
        }
        $app->input->set('tmpl', 'component');
        parent::display($tpl);
    }
}

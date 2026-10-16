<?php
namespace Support\Component\Supportplans\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

final class Dispatcher extends ComponentDispatcher
{
    public function dispatch(): void
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $view = $input->getCmd('view', 'app');
        $task = $input->getCmd('task', '');
        $publicViews = ['app', 'otp'];
        $publicTasks = ['auth.authenticate', 'auth.logout', 'otp.send', 'otp.verify'];

        $needsAuth = !in_array($view, $publicViews, true) || ($task !== '' && !in_array($task, $publicTasks, true));
        if ($needsAuth) {
            $session = $app->getSession();
            if ((int) $session->get('supportplans.auth.user_id', 0) < 1 || !(bool) $session->get('supportplans.otp.verified', false)) {
                $itemid = max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
                $app->enqueueMessage('Συνδέσου για πρόσβαση στο Support Plans.', 'warning');
                $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . $itemid, false));
                return;
            }
        }
        parent::dispatch();
    }
}

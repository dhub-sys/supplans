<?php
namespace Support\Component\Supportplans\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Support\Component\Supportplans\Site\Helper\AuthCookie;
use Support\Component\Supportplans\Site\Helper\ShareLink;

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

        // Customer link to a package report (?view=report&t=<96 hex>): public, read-only.
        $sharedReport = $view === 'report' && $task === '' && ShareLink::isToken((string) $input->getString('t', ''));
        if ($sharedReport) {
            // Links sent before v1.10.1 lack the Itemid / spapp parameters: move them to the full URL.
            $canonical = ShareLink::url((string) $input->getString('t', ''));
            parse_str((string) parse_url($canonical, PHP_URL_QUERY), $want);
            if ($input->getInt('spapp', 0) !== 1 || $input->getInt('Itemid', 0) !== (int) $want['Itemid']) {
                $app->redirect($canonical, 301);
                return;
            }
        }
        $needsAuth = !$sharedReport && (!in_array($view, $publicViews, true) || ($task !== '' && !in_array($task, $publicTasks, true)));
        $session = $app->getSession();
        $signedIn = (int) $session->get('supportplans.auth.user_id', 0) > 0 && (bool) $session->get('supportplans.otp.verified', false);
        if (!$signedIn && $task !== 'auth.logout') {
            $signedIn = AuthCookie::restore();
        }
        // Already signed in (session or "stay signed in" cookie): skip the login / OTP screens.
        if ($signedIn && $task === '' && in_array($view, $publicViews, true)) {
            $itemid = max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
            $app->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid=' . $itemid, false));
            return;
        }
        if ($needsAuth) {
            if ((int) $session->get('supportplans.auth.user_id', 0) < 1 || !(bool) $session->get('supportplans.otp.verified', false)) {
                $itemid = max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
                $app->enqueueMessage('Συνδέσου για πρόσβαση στο Support Plans.', 'warning');
                $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . $itemid, false));
                return;
            }
        }
        parent::dispatch();

        // Browser tab title of the Service Hub (Settings > Τίτλος σελίδας). The printable views
        // keep their own title, which the browser uses as the Pdf file name.
        $document = $app->getDocument();
        if ($document && method_exists($document, 'setTitle') && !in_array($view, ['report', 'offerdoc'], true)) {
            $document->setTitle(trim((string) ComponentHelper::getParams('com_supportplans')->get('page_title', '')) ?: 'Support Plans');
        }
    }
}

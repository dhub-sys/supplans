<?php
namespace Support\Component\Supportplans\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Support\Component\Supportplans\Site\Helper\Activity;
use Support\Component\Supportplans\Site\Helper\AuthCookie;
use Support\Component\Supportplans\Site\Helper\ShareLink;

final class Dispatcher extends ComponentDispatcher
{
    /** Runs the task/view; a failure is written to the activity log before it propagates. */
    private function run(): void
    {
        try {
            parent::dispatch();
        } catch (\Throwable $e) {
            Activity::finish($e);
            throw $e;
        }
    }

    public function dispatch(): void
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $view = $input->getCmd('view', 'app');
        $task = $input->getCmd('task', '');
        // Activity log: every task (not plain page views), also when it ends with a redirect.
        // The unsubscribe page is logged only when the client confirms (POST).
        if ($task !== '' && ($task !== 'unsubscribe.run' || strtoupper($input->getMethod()) === 'POST')) {
            Activity::begin($task);
            register_shutdown_function([Activity::class, 'finish']);
        }
        $publicViews = ['app', 'otp'];
        $publicTasks = ['auth.authenticate', 'auth.logout', 'otp.send', 'otp.verify', 'unsubscribe.run'];

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
        // Customer link to an offer document (?view=offerdoc&t=<96 hex>): public, read-only.
        $sharedOffer = $view === 'offerdoc' && $task === '' && ShareLink::isToken((string) $input->getString('t', ''));
        $sharedReport = $sharedReport || $sharedOffer;
        // Unsubscribe link of the campaign e-mails: public, protected by its own HMAC token.
        if ($task === 'unsubscribe.run') {
            $this->run();
            return;
        }
        $needsAuth = !$sharedReport && (!in_array($view, $publicViews, true) || ($task !== '' && !in_array($task, $publicTasks, true)));
        $session = $app->getSession();
        $signedIn = (int) $session->get('supportplans.auth.user_id', 0) > 0 && (bool) $session->get('supportplans.otp.verified', false);
        // Blocked, demoted or deleted users lose access immediately, not when the session ends.
        if ($signedIn && !AuthCookie::allowed((int) $session->get('supportplans.auth.user_id', 0))) {
            $session->clear('supportplans.auth.user_id');
            $session->clear('supportplans.otp.verified');
            AuthCookie::clear();
            $signedIn = false;
        }
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
                $app->enqueueMessage('Συνδέσου για πρόσβαση στο Back Office.', 'warning');
                $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . $itemid, false));
                return;
            }
        }
        $this->run();

        // Browser tab title of the Service Hub (Settings > Τίτλος σελίδας). The printable views
        // keep their own title, which the browser uses as the Pdf file name.
        $document = $app->getDocument();
        if ($document && method_exists($document, 'setTitle') && !in_array($view, ['report', 'offerdoc', 'structuredoc'], true)) {
            $document->setTitle(trim((string) ComponentHelper::getParams('com_supportplans')->get('page_title', '')) ?: 'Back Office');
        }
    }
}

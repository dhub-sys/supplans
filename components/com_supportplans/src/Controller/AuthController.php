<?php
namespace Support\Component\Supportplans\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\UserHelper;

final class AuthController extends BaseController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_LOCK_SECONDS = 60;

    public function authenticate(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $session = $app->getSession();
        $now = time();
        $lockedUntil = (int) $session->get('supportplans.login.locked_until', 0);

        if ($lockedUntil > $now) {
            $app->enqueueMessage('Έγιναν πολλές αποτυχημένες προσπάθειες. Δοκίμασε ξανά σε λίγο.', 'warning');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $username = trim($this->input->post->getString('username', ''));
        $password = (string) $this->input->post->get('password', '', 'raw');
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'username', 'name', 'email', 'password', 'block', 'requireReset']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = :username')
            ->bind(':username', $username);
        $user = $db->setQuery($query)->loadObject();

        $valid = $user
            && !(int) $user->block
            && !(int) $user->requireReset
            && $password !== ''
            && UserHelper::verifyPassword($password, (string) $user->password);

        if (!$valid) {
            $attempts = (int) $session->get('supportplans.login.attempts', 0) + 1;
            if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $session->set('supportplans.login.locked_until', $now + self::LOGIN_LOCK_SECONDS);
                $session->set('supportplans.login.attempts', 0);
            } else {
                $session->set('supportplans.login.attempts', $attempts);
            }
            $app->enqueueMessage('Τα στοιχεία σύνδεσης δεν είναι σωστά.', 'error');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $session->clear('supportplans.login.attempts');
        $session->clear('supportplans.login.locked_until');
        $this->clearAuthState();

        // Pending identity only. We deliberately do NOT call Joomla $app->login(),
        // therefore Joomla MFA is not entered for this standalone application.
        $session->set('supportplans.pending.user_id', (int) $user->id);
        $session->set('supportplans.pending.username', (string) $user->username);
        $session->set('supportplans.pending.name', (string) $user->name);
        $session->set('supportplans.pending.email', (string) $user->email);
        $session->set('supportplans.pending.started', $now);

        $app->redirect(Route::_('index.php?option=com_supportplans&task=otp.send&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
    }

    public function logout(): void
    {
        $app = Factory::getApplication();
        $session = $app->getSession();
        $this->clearAuthState();
        $session->clear('supportplans.pending.user_id');
        $session->clear('supportplans.pending.username');
        $session->clear('supportplans.pending.name');
        $session->clear('supportplans.pending.email');
        $session->clear('supportplans.pending.started');
        $session->clear('supportplans.otp.hash');
        $session->clear('supportplans.otp.expires');
        $session->clear('supportplans.otp.attempts');
        $session->clear('supportplans.otp.last_sent');
        $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
    }

    private function clearAuthState(): void
    {
        $session = Factory::getApplication()->getSession();
        $session->clear('supportplans.auth.user_id');
        $session->clear('supportplans.auth.username');
        $session->clear('supportplans.auth.name');
        $session->clear('supportplans.auth.email');
        $session->clear('supportplans.auth.authenticated_at');
        $session->clear('supportplans.otp.verified');
    }
}

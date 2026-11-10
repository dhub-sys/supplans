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

        // Throttle per IP and per username in the database: clearing the browser cookies
        // (a new session) does not reset it.
        $ip = substr((string) $this->input->server->getString('REMOTE_ADDR', ''), 0, 45);
        if ($this->tooManyFailures($db, $ip, $username)) {
            $app->enqueueMessage('Έγιναν πολλές αποτυχημένες προσπάθειες. Δοκίμασε ξανά σε 15 λεπτά.', 'warning');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $valid = $user
            && !(int) $user->block
            && !(int) $user->requireReset
            && $password !== ''
            && UserHelper::verifyPassword($password, (string) $user->password)
            // Only Super Users may enter the Service Hub (same message, so accounts cannot be probed).
            && \Support\Component\Supportplans\Site\Helper\AuthCookie::allowed((int) $user->id);

        if (!$valid) {
            $this->recordFailure($db, $ip, $username);
        } else {
            $this->clearFailures($db, $ip, $username);
        }

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
        \Support\Component\Supportplans\Site\Helper\AuthCookie::clear();
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

    private const THROTTLE_WINDOW = 900;
    private const MAX_FAILS_PER_IP = 10;
    private const MAX_FAILS_PER_USERNAME = 5;

    private function tooManyFailures($db, string $ip, string $username): bool
    {
        try {
            $since = gmdate('Y-m-d H:i:s', time() - self::THROTTLE_WINDOW);
            $table = $db->quoteName('#__supportplans_login_attempts');
            $byIp = (int) $db->setQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ip=' . $db->quote($ip) . ' AND attempted>' . $db->quote($since))->loadResult();
            $byUser = (int) $db->setQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE username=' . $db->quote(mb_strtolower($username)) . ' AND attempted>' . $db->quote($since))->loadResult();
            return $byIp >= self::MAX_FAILS_PER_IP || ($username !== '' && $byUser >= self::MAX_FAILS_PER_USERNAME);
        } catch (\Throwable $e) {
            return false; // table missing before the update finished: fall back to the session limit
        }
    }

    private function recordFailure($db, string $ip, string $username): void
    {
        try {
            $row = (object) ['ip' => $ip, 'username' => mb_substr(mb_strtolower($username), 0, 150), 'attempted' => gmdate('Y-m-d H:i:s')];
            $db->insertObject('#__supportplans_login_attempts', $row);
            $db->setQuery('DELETE FROM ' . $db->quoteName('#__supportplans_login_attempts') . ' WHERE attempted<' . $db->quote(gmdate('Y-m-d H:i:s', time() - 86400)))->execute();
        } catch (\Throwable $e) {
        }
    }

    private function clearFailures($db, string $ip, string $username): void
    {
        try {
            $db->setQuery('DELETE FROM ' . $db->quoteName('#__supportplans_login_attempts') . ' WHERE ip=' . $db->quote($ip) . ' OR username=' . $db->quote(mb_strtolower($username)))->execute();
        } catch (\Throwable $e) {
        }
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

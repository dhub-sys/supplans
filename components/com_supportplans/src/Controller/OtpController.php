<?php
namespace Support\Component\Supportplans\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

final class OtpController extends BaseController
{
    private const TTL = 300;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_WAIT = 60;
    private const PENDING_TTL = 600;

    public function send(): void
    {
        $app = Factory::getApplication();
        $method = strtoupper($app->input->getMethod());
        if ($method === 'POST' && !Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $session = $app->getSession();
        $userId = (int) $session->get('supportplans.pending.user_id', 0);
        $email = (string) $session->get('supportplans.pending.email', '');
        $started = (int) $session->get('supportplans.pending.started', 0);

        if (!$userId || !$email || !$started || time() - $started > self::PENDING_TTL) {
            $this->clearPending();
            $app->enqueueMessage('Η διαδικασία σύνδεσης έληξε. Συνδέσου ξανά.', 'warning');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $now = time();
        $last = (int) $session->get('supportplans.otp.last_sent', 0);
        if ($last && ($now - $last) < self::RESEND_WAIT) {
            $app->enqueueMessage('Περίμενε λίγο πριν ζητήσεις νέο κωδικό.', 'warning');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=otp&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $code = (string) random_int(100000, 999999);
        $session->set('supportplans.otp.hash', password_hash($code, PASSWORD_DEFAULT));
        $session->set('supportplans.otp.expires', $now + self::TTL);
        $session->set('supportplans.otp.attempts', 0);
        $session->set('supportplans.otp.last_sent', $now);

        $mailer = Factory::getMailer();
        $mailer->addRecipient($email);
        $mailer->setSubject('Support Plans - Κωδικός επιβεβαίωσης');
        $mailer->isHtml(true);
        $mailer->setBody(
            '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto">' .
            '<h2>Support Plans</h2><p>Ο κωδικός επιβεβαίωσής σου είναι:</p>' .
            '<div style="font-size:32px;font-weight:700;letter-spacing:8px;margin:24px 0">' . $code . '</div>' .
            '<p>Ο κωδικός ισχύει για 5 λεπτά και χρησιμοποιείται μόνο μία φορά.</p>' .
            '<p style="color:#667085;font-size:13px">Αν δεν προσπάθησες να συνδεθείς, αγνόησε αυτό το μήνυμα.</p></div>'
        );

        try {
            $mailer->Send();
            $app->enqueueMessage('Στείλαμε έναν 6ψήφιο κωδικό στο email σου.', 'message');
        } catch (\Throwable $e) {
            $session->clear('supportplans.otp.hash');
            $app->enqueueMessage('Δεν ήταν δυνατή η αποστολή του κωδικού. Έλεγξε τις ρυθμίσεις email του Joomla.', 'error');
        }

        $app->redirect(Route::_('index.php?option=com_supportplans&view=otp&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
    }

    public function verify(): void
    {
        $app = Factory::getApplication();
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $session = $app->getSession();
        $userId = (int) $session->get('supportplans.pending.user_id', 0);
        $started = (int) $session->get('supportplans.pending.started', 0);
        if (!$userId || !$started || time() - $started > self::PENDING_TTL) {
            $this->clearPending();
            $app->redirect(Route::_('index.php?option=com_supportplans&view=app&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $hash = (string) $session->get('supportplans.otp.hash', '');
        $expires = (int) $session->get('supportplans.otp.expires', 0);
        $attempts = (int) $session->get('supportplans.otp.attempts', 0);
        $code = preg_replace('/\D+/', '', $this->input->post->getString('otp', ''));

        if (!$hash || time() > $expires) {
            $app->enqueueMessage('Ο κωδικός έχει λήξει. Ζήτησε νέο κωδικό.', 'warning');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=otp&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }
        if ($attempts >= self::MAX_ATTEMPTS) {
            $session->clear('supportplans.otp.hash');
            $app->enqueueMessage('Έγιναν πολλές αποτυχημένες προσπάθειες. Ζήτησε νέο κωδικό.', 'error');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=otp&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        $session->set('supportplans.otp.attempts', $attempts + 1);
        if (strlen($code) !== 6 || !password_verify($code, $hash)) {
            $app->enqueueMessage('Ο κωδικός δεν είναι σωστός.', 'error');
            $app->redirect(Route::_('index.php?option=com_supportplans&view=otp&tmpl=component&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
            return;
        }

        // Promote pending identity to the private Support Plans session.
        $session->set('supportplans.auth.user_id', $userId);
        $session->set('supportplans.auth.username', (string) $session->get('supportplans.pending.username', ''));
        $session->set('supportplans.auth.name', (string) $session->get('supportplans.pending.name', ''));
        $session->set('supportplans.auth.email', (string) $session->get('supportplans.pending.email', ''));
        $session->set('supportplans.auth.authenticated_at', time());
        $session->set('supportplans.otp.verified', true);
        $this->clearPending();
        $session->clear('supportplans.otp.hash');
        $session->clear('supportplans.otp.expires');
        $session->clear('supportplans.otp.attempts');

        $app->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid=' . max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785)) . '', false));
    }

    private function clearPending(): void
    {
        $session = Factory::getApplication()->getSession();
        foreach (['user_id','username','name','email','started'] as $key) {
            $session->clear('supportplans.pending.' . $key);
        }
    }
}

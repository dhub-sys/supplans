<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\ShareLink;

/** Customer link of a package: create / renew, disable and send by email. */
final class ShareController extends BaseController
{
    public function create(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $plan = $this->plan();
        if ($plan) {
            $this->setToken((int) $plan->id, ShareLink::newToken());
            Factory::getApplication()->enqueueMessage($plan->share_token !== ''
                ? 'Δημιουργήθηκε νέος σύνδεσμος. Ο προηγούμενος δεν λειτουργεί πλέον.'
                : 'Δημιουργήθηκε ο σύνδεσμος για τον πελάτη.');
        }
        $this->back($plan);
    }

    public function revoke(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $plan = $this->plan();
        if ($plan) {
            $this->setToken((int) $plan->id, '');
            Factory::getApplication()->enqueueMessage('Ο σύνδεσμος απενεργοποιήθηκε.');
        }
        $this->back($plan);
    }

    public function send(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $plan = $this->plan();
        if (!$plan) {
            $this->back(null);
            return;
        }
        $to = trim($this->input->getString('share_email'));
        if (!MailHelper::isEmailAddress($to)) {
            $app->enqueueMessage('Συμπληρώστε ένα έγκυρο email παραλήπτη.', 'error');
            $this->back($plan);
            return;
        }
        $token = (string) $plan->share_token;
        if (!ShareLink::isToken($token)) {
            $token = ShareLink::newToken();
            $this->setToken((int) $plan->id, $token);
        }
        $link = ShareLink::url($token);
        $brand = OfferHelper::brand();
        $subject = trim($this->input->getString('share_subject')) ?: 'Πρόοδος πακέτου υποστήριξης';
        $message = trim((string) $this->input->get('share_message', '', 'string'));
        $esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $body = '<div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#1f2937">'
            . ($message !== '' ? nl2br($esc($message)) : 'Σας στέλνουμε τον σύνδεσμο με την πρόοδο του πακέτου υποστήριξης.')
            . '<p style="margin:24px 0"><a href="' . $esc($link) . '" style="display:inline-block;background:' . $esc($brand['accent']) . ';color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">Προβολή αναφοράς πακέτου</a></p>'
            . '<p style="font-size:13px;color:#6b7280">Ο σύνδεσμος ενημερώνεται αυτόματα με κάθε νέα εργασία. Είναι προσωπικός· μην τον προωθήσετε.<br>'
            . '<a href="' . $esc($link) . '" style="color:#6b7280;word-break:break-all">' . $esc($link) . '</a></p>'
            . '<p style="margin-top:24px">' . $esc($brand['name']) . '</p></div>';
        try {
            $mailer = Factory::getMailer();
            $mailer->addRecipient($to);
            $mailer->setSubject($subject);
            $mailer->isHtml(true);
            $mailer->setBody($body);
            $mailer->AltBody = ($message !== '' ? $message : 'Σας στέλνουμε τον σύνδεσμο με την πρόοδο του πακέτου υποστήριξης.') . "\n\n" . $link;
            $mailer->Send();
            $app->enqueueMessage('Ο σύνδεσμος στάλθηκε στο ' . $to . '.');
        } catch (\Throwable $e) {
            $app->enqueueMessage('Το email δεν στάλθηκε: ' . $e->getMessage(), 'error');
        }
        $this->back($plan);
    }

    private function plan(): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select('id, share_token')->from($db->quoteName('#__supportplans_plans'))
            ->where('id = ' . (int) $this->input->getInt('plan_id'));
        return $db->setQuery($q)->loadObject() ?: null;
    }

    private function setToken(int $planId, string $token): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->update($db->quoteName('#__supportplans_plans'))
            ->set($db->quoteName('share_token') . ' = ' . $db->quote($token))->where('id = ' . $planId);
        $db->setQuery($q)->execute();
    }

    private function back(?object $plan): void
    {
        $itemid = max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
        Factory::getApplication()->redirect(Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1'
            . ($plan ? '&plan_id=' . (int) $plan->id : '') . '&Itemid=' . $itemid . '#sp-share', false));
    }
}

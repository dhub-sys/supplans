<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Support\Component\Supportplans\Site\Helper\Notify;

/**
 * Public unsubscribe link of the campaign e-mails: ?task=unsubscribe.run&c=<client>&t=<HMAC>.
 *
 * GET shows a confirmation button; only a POST unsubscribes (the button, or the one-click
 * RFC 8058 request of Gmail/Outlook), so link scanners that open every URL of an e-mail
 * cannot unsubscribe anyone. The token is an HMAC with the site secret: it cannot be guessed
 * and it only works for its own client.
 */
class UnsubscribeController extends BaseController
{
    public function run(): void
    {
        $app = Factory::getApplication();
        $client = Notify::client($this->input->getInt('c', 0));
        $token = (string) $this->input->getAlnum('t', '');
        $app->setHeader('X-Robots-Tag', 'noindex, nofollow', true);
        $app->setHeader('Cache-Control', 'no-store', true);

        if (!Notify::verifyUnsubscribe($client, $token)) {
            $this->page('Μη έγκυρος σύνδεσμος', '<p>Ο σύνδεσμος δεν είναι έγκυρος.</p>', 403);
            return;
        }
        $site = htmlspecialchars(Notify::brandName(), ENT_QUOTES, 'UTF-8');
        if (strtoupper($this->input->getMethod()) === 'POST') {
            if (!$client->unsubscribed_at) {
                $db = Factory::getContainer()->get('DatabaseDriver');
                $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_CLIENTS))
                    ->set('unsubscribed_at=' . $db->quote(Notify::now()))->where('id=' . (int) $client->id))->execute();
            }
            $this->page('Η διαγραφή ολοκληρώθηκε', '<p>Δεν θα λαμβάνετε πλέον ενημερωτικά μηνύματα από ' . $site
                . '.</p><p>Θα συνεχίσετε να λαμβάνετε μόνο μηνύματα που αφορούν τις υπηρεσίες σας (π.χ. ανανεώσεις).</p>');
            return;
        }
        if ($client->unsubscribed_at) {
            $this->page('Έχετε ήδη διαγραφεί', '<p>Δεν λαμβάνετε ενημερωτικά μηνύματα από ' . $site . '.</p>');
            return;
        }
        $action = htmlspecialchars(Notify::unsubscribeUrl($client), ENT_QUOTES, 'UTF-8');
        $this->page('Διαγραφή από ενημερώσεις', '<p>Να σταματήσει η αποστολή ενημερωτικών μηνυμάτων από ' . $site . ' στο <strong>'
            . htmlspecialchars((string) $client->email, ENT_QUOTES, 'UTF-8') . '</strong>;</p>'
            . '<form method="post" action="' . $action . '"><button type="submit">Ναι, διαγραφή</button></form>');
    }

    /** Minimal standalone page (no site template, no Service Hub). */
    private function page(string $title, string $body, int $status = 200): void
    {
        $app = Factory::getApplication();
        $app->setHeader('Status', (string) $status, true);
        $app->setHeader('Content-Type', 'text/html; charset=utf-8', true);
        $app->sendHeaders();
        echo '<!DOCTYPE html><html lang="el"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">'
            . '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<style>body{margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937}main{max-width:520px;margin:12vh auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 8px 30px rgba(0,0,0,.06)}h1{font-size:22px;margin:0 0 14px}button{background:#1d4ed8;color:#fff;border:0;border-radius:8px;padding:11px 20px;font-size:15px;cursor:pointer}</style></head>'
            . '<body><main><h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>' . $body . '</main></body></html>';
        $app->close();
    }
}

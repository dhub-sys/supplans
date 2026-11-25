<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;
use Support\Component\Supportplans\Site\Helper\Xlsx;

/** Συντήρηση sites: sites, versions, update rounds. POST + form token only (the Dispatcher checks the login). */
class SitesController extends BaseController
{
    private function guard(): void
    {
        if (strtoupper($this->input->getMethod()) !== 'POST' || !Session::checkToken('post')) {
            throw new \RuntimeException('Invalid Token', 403);
        }
    }

    private function db()
    {
        return Factory::getContainer()->get('DatabaseDriver');
    }

    private function msg(string $text, string $type = 'message'): void
    {
        Factory::getApplication()->enqueueMessage($text, $type);
    }

    private function go(string $query): void
    {
        [$query, $fragment] = array_pad(explode('#', $query, 2), 2, '');
        Factory::getApplication()->redirect(OfferHelper::url('view=notify&' . ltrim($query, '&'), false) . ($fragment !== '' ? '#' . $fragment : ''));
    }

    private function str(string $name, int $max = 190): string
    {
        return mb_substr(trim((string) $this->input->post->getString($name, '')), 0, $max);
    }

    private function email(string $name): string
    {
        $v = trim((string) $this->input->post->getString($name, ''));
        return $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
    }

    private function me(): object
    {
        $s = Factory::getApplication()->getSession();
        return (object) ['id' => 0, 'name' => (string) $s->get('supportplans.auth.name', ''), 'company' => '', 'email' => (string) $s->get('supportplans.auth.email', ''), 'status' => 'active', 'salutation' => ''];
    }

    /* ------------------------------------------------------------ sites */

    public function save(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $back = 'section=sites&' . ($id ? 'id=' . $id : 'mode=new');
        $domain = Sites::domain($this->str('domain'));
        $clientId = $this->input->post->getInt('client_id', 0);
        if ($domain === '' || !$clientId || !Notify::client($clientId)) {
            $this->msg('Συμπληρώστε site και πελάτη.', 'error');
            $this->go($back);
            return;
        }
        $other = Sites::siteByDomain($domain);
        if ($other && (int) $other->id !== $id) {
            $this->msg('Υπάρχει ήδη site με αυτό το domain.', 'error');
            $this->go($back);
            return;
        }
        $cms = Sites::cmsKey($this->input->post->getCmd('cms', 'joomla')) ?: 'joomla';
        $status = $this->input->post->getCmd('status', 'active');
        $db = $this->db();
        $now = Notify::now();
        $o = (object) ['client_id' => $clientId, 'domain' => $domain, 'cms' => $cms, 'status' => isset(Sites::STATUSES[$status]) ? $status : 'active',
            'notes' => mb_substr(trim((string) $this->input->post->getString('notes', '')), 0, 5000), 'modified' => $now];
        if ($id) {
            $o->id = $id;
            $db->updateObject(Sites::T_SITES, $o, 'id');
        } else {
            $o->created = $now;
            $db->insertObject(Sites::T_SITES, $o, 'id');
            $id = (int) $o->id;
        }
        Sites::setCore($id, $cms, $this->str('cms_version', 50));
        Sites::replaceItems($id, Sites::parseItems((string) $this->input->post->getString('items', '')));
        $this->msg('Το site αποθηκεύτηκε.');
        $this->go('section=sites&id=' . $id);
    }

    public function delete(): void
    {
        $this->guard();
        Sites::deleteSite($this->input->post->getInt('id', 0));
        $this->msg('Το site διαγράφηκε μαζί με τις εκδόσεις και το ιστορικό ενημερώσεών του.');
        $this->go('section=sites');
    }

    public function import(): void
    {
        $this->guard();
        $file = $this->input->files->get('sheet', null, 'raw');
        if (!is_array($file) || (int) ($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            $this->msg('Δεν ανέβηκε αρχείο.', 'error');
            $this->go('section=sites');
            return;
        }
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'csv'], true) || (int) $file['size'] > 10 * 1024 * 1024) {
            $this->msg('Επιτρέπεται αρχείο Excel (.xlsx) ή .csv έως 10 MB.', 'error');
            $this->go('section=sites');
            return;
        }
        try {
            $rows = $ext === 'xlsx' ? Xlsx::read((string) $file['tmp_name'], 'Sites') : $this->readCsv((string) $file['tmp_name']);
        } catch (\Throwable $e) {
            $this->msg($e->getMessage(), 'error');
            $this->go('section=sites');
            return;
        }
        $r = Sites::import($rows);
        $this->msg(sprintf('Εισαγωγή: %d νέα sites, %d ενημερώθηκαν · %d εκδόσεις extensions · %d νέοι πελάτες.', $r['sn'], $r['su'], $r['items'], $r['cn']));
        if ($r['errors']) {
            $this->msg('Γραμμές που δεν περάστηκαν:<br>' . implode('<br>', array_map(static fn($x) => htmlspecialchars($x, ENT_QUOTES, 'UTF-8'), array_slice($r['errors'], 0, 40)))
                . (count($r['errors']) > 40 ? '<br>… και ' . (count($r['errors']) - 40) . ' ακόμα.' : ''), 'warning');
        }
        $this->go('section=sites');
    }

    private function readCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        $first = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($fh));
        $delim = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $rows = [1 => str_getcsv(rtrim($first, "\r\n"), $delim, '"', '\\')];
        $n = 1;
        while (($cells = fgetcsv($fh, 0, $delim, '"', '\\')) !== false) {
            $rows[++$n] = array_map(static fn($c) => trim((string) $c), $cells);
        }
        fclose($fh);
        return $rows;
    }

    /** Excel with all sites and their extensions (also the template: it can be edited and imported back). */
    public function export(): void
    {
        $this->guard();
        if (!Xlsx::available()) {
            $this->msg('Ο server δεν υποστηρίζει αρχεία Excel (λείπει η επέκταση PHP zip).', 'error');
            $this->go('section=sites');
            return;
        }
        $rows = Sites::exportRows();
        if (count($rows) === 1) {
            $rows[] = ['info@example.gr', 'Example ΑΕ', 'example.gr', 'Joomla', '6.2.0', 'YOOtheme Pro', '5.5.3'];
            $rows[] = ['info@example.gr', 'Example ΑΕ', 'example.gr', 'Joomla', '6.2.0', 'Akeeba Backup Pro', '10.2.2'];
            $rows[] = ['shop@example.com', 'Shop', 'shop.example.com', 'WordPress', '6.8.3', 'WooCommerce', '10.3.0'];
        }
        $help = [['Οδηγίες'],
            ['Μία γραμμή για κάθε extension / plugin / theme / γλώσσα κάθε site. Site με 12 extensions = 12 γραμμές με το ίδιο Site.'],
            ['Email: το email του πελάτη (αν δεν υπάρχει, δημιουργείται νέος πελάτης με το όνομα της στήλης «Πελάτης»).'],
            ['Site: το domain, π.χ. example.gr. Με αυτό αναγνωρίζεται το site (αν υπάρχει, ενημερώνεται).'],
            ['CMS: Joomla, WordPress ή Άλλο. Έκδοση CMS: π.χ. 6.2.0 (αρκεί σε μία γραμμή του site).'],
            ['Extension / Έκδοση: π.χ. YOOtheme Pro | 5.5.3. Γράφετε τα ονόματα πάντα με τον ίδιο τρόπο σε όλα τα sites, ώστε να ενημερώνονται μαζικά.'],
            ['Η εισαγωγή προσθέτει και ενημερώνει· δεν διαγράφει extensions που δεν υπάρχουν στο αρχείο (αυτό γίνεται από τη σελίδα του site).'],
        ];
        $data = Xlsx::build([
            ['name' => 'Sites', 'rows' => $rows, 'widths' => [30, 26, 28, 12, 12, 36, 12]],
            ['name' => 'Οδηγίες', 'rows' => $help, 'widths' => [120]],
        ]);
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="sites-' . date('Y-m-d') . '.xlsx"', true);
        $app->setHeader('Content-Length', (string) strlen($data), true);
        $app->setHeader('Cache-Control', 'no-store', true);
        $app->sendHeaders();
        echo $data;
        $app->close();
    }

    /* ------------------------------------------------------------ update rounds */

    /** "Έλεγχος sites": the new versions entered -> a draft round with the sites that get each update. */
    public function prepare(): void
    {
        $this->guard();
        $entries = [];
        foreach ((array) $this->input->post->get('entries', [], 'array') as $e) {
            $name = mb_substr(trim((string) ($e['n'] ?? '')), 0, 190);
            $new = mb_substr(trim((string) ($e['v'] ?? '')), 0, 50);
            if ($name === '' || $new === '') continue;
            $entries[] = ['n' => $name, 'o' => mb_substr(trim((string) ($e['o'] ?? '')), 0, 50), 'v' => $new];
        }
        if (!$entries) {
            $this->msg('Συμπληρώστε τη νέα έκδοση σε τουλάχιστον ένα CMS ή extension.', 'error');
            $this->go('section=siteupdates');
            return;
        }
        $plan = Sites::plan($entries);
        if (!$plan) {
            $this->msg('Κανένα site δεν έχει παλαιότερη έκδοση από αυτές που γράψατε· όλα είναι ήδη ενημερωμένα.', 'warning');
            $this->go('section=siteupdates');
            return;
        }
        $now = Notify::now();
        $title = trim($this->str('title', 255)) ?: 'Ενημερώσεις ' . Notify::dateGr(Notify::today());
        $o = (object) ['title' => $title, 'entries' => json_encode($entries, JSON_UNESCAPED_UNICODE), 'plan' => json_encode($plan, JSON_UNESCAPED_UNICODE),
            'status' => 'draft', 'sites' => count($plan), 'messages' => 0, 'created' => $now, 'modified' => $now];
        $this->db()->insertObject(Sites::T_ROUNDS, $o, 'id');
        // "Καταγραφή απευθείας": every site that has the item gets the update, no review step.
        if ($this->input->post->getInt('direct', 0)) {
            [$sites, $items] = Sites::applyRound((int) $o->id, 'log');
            $when = Sites::scheduleLabel();
            $this->msg(sprintf('Καταγράφηκαν %d ενημερώσεις σε %d sites.', $items, $sites) . ' Θα συμπεριληφθούν στην επόμενη σύνοψη προς τους πελάτες' . ($when !== '' ? ' (αυτόματα ' . $when . ')' : '') . '.');
            $this->go('section=rounds&id=' . (int) $o->id);
            return;
        }
        $this->msg(count($plan) . ' sites θα πάρουν ενημερώσεις. Ελέγξτε τη λίστα, ξετσεκάρετε ό,τι δεν έγινε και πατήστε «Εφαρμογή & αποστολή».');
        $this->go('section=rounds&id=' . (int) $o->id);
    }

    /** Saves the choices of a draft round, then: save / test / send (apply + e-mails) / apply (no e-mails). */
    public function saveRound(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $round = Sites::round($id);
        if (!$round || $round->status !== 'draft') {
            $this->msg('Η ενημέρωση έχει ήδη εφαρμοστεί.', 'error');
            $this->go('section=rounds' . ($round ? '&id=' . $id : ''));
            return;
        }
        $on = (array) $this->input->post->get('on', [], 'array');
        $mail = (array) $this->input->post->get('mail', [], 'array');
        $plan = $round->planList;
        foreach ($plan as $i => &$p) {
            $p['m'] = !empty($mail[$i]) ? 1 : 0;
            foreach ($p['u'] as $j => &$u) {
                $u['on'] = !empty($on[$i][$j]) ? 1 : 0;
            }
            unset($u);
        }
        unset($p);
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(Sites::T_ROUNDS))
            ->set(['plan=' . $db->quote(json_encode($plan, JSON_UNESCAPED_UNICODE)), 'title=' . $db->quote($this->str('title', 255) ?: $round->title), 'modified=' . $db->quote(Notify::now())])
            ->where('id=' . $id))->execute();
        $do = $this->input->post->getCmd('do', 'save');
        if (preg_match('/^preview_(\d+)$/', $do, $m)) {
            $this->go('section=rounds&id=' . $id . '&preview=' . (int) $m[1] . '#sp-su-preview');
            return;
        }
        if ($do === 'test') {
            $round = Sites::round($id);
            $rows = Sites::planRows($round);
            foreach ($rows as $row) {
                [$client, $groups] = Sites::rowMessage($rows, $row['i']);
                if (!$groups) continue;
                $me = $this->me();
                [$subject, $body] = Sites::buildMessage($groups, (object) (['id' => 0] + (array) ($client ?: $me)));
                $ok = $me->email !== '' && Notify::sendTest($me->email, $subject, $body, 'maintenance');
                $this->msg($ok ? 'Δοκιμαστικό (το μήνυμα του ' . ($client ? Notify::clientLabel($client) : $row['site']->domain) . ') εστάλη στο ' . $me->email . '.' : 'Η δοκιμαστική αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
                break;
            }
        } elseif (in_array($do, ['log', 'send', 'apply'], true)) {
            [$sites, $items, $messages] = Sites::applyRound($id, $do === 'apply' ? 'silent' : $do);
            $when = Sites::scheduleLabel();
            $this->msg(sprintf('Καταγράφηκαν %d ενημερώσεις σε %d sites.', $items, $sites) . ($do === 'send' ? ' ' . $messages . ' μηνύματα μπήκαν σε ουρά και αποστέλλονται σταδιακά.'
                : ($do === 'log' ? ' Θα συμπεριληφθούν στην επόμενη σύνοψη προς τους πελάτες' . ($when !== '' ? ' (αυτόματα ' . $when . ')' : '') . '.' : ' Οι πελάτες δεν θα ενημερωθούν γι\' αυτές.')));
        } else {
            $this->msg('Οι επιλογές αποθηκεύτηκαν.');
        }
        $this->go('section=rounds&id=' . $id);
    }

    public function deleteRound(): void
    {
        $this->guard();
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(Sites::T_ROUNDS))->where('id=' . $this->input->post->getInt('id', 0))->where("status='draft'"))->execute();
        $this->msg('Το πρόχειρο διαγράφηκε.');
        $this->go('section=siteupdates');
    }

    /* ------------------------------------------------------------ monthly summary */

    /** Σύνοψη ενημερώσεων: send now (the checked sites) or keep the checked sites out of it. */
    public function digest(): void
    {
        $this->guard();
        $ids = array_values(array_filter(array_map('intval', (array) $this->input->post->get('clients', [], 'array'))));
        if (!$ids) {
            $this->msg('Επιλέξτε τουλάχιστον έναν πελάτη.', 'error');
            $this->go('section=sitedigest');
            return;
        }
        if ($this->input->post->getCmd('do', 'send') === 'skip') {
            Sites::skipPending($ids);
            $this->msg('Οι ενημερώσεις ' . count($ids) . (count($ids) === 1 ? ' πελάτη' : ' πελατών') . ' αφαιρέθηκαν από τη σύνοψη (μένουν στο ιστορικό των sites).');
        } else {
            $n = Sites::sendDigest($ids);
            $this->msg($n . ' μηνύματα σύνοψης μπήκαν σε ουρά και αποστέλλονται σταδιακά.');
        }
        $this->go('section=sitedigest');
    }

    /** Test of the summary of one site to the administrator. */
    public function digestTest(): void
    {
        $this->guard();
        $sid = $this->input->post->getInt('client', 0);
        $p = Sites::pendingByClient()[$sid] ?? null;
        $me = $this->me();
        if ($p && $p['client'] && $me->email !== '') {
            [$subject, $body] = Sites::buildMessage($p['groups'], (object) (['id' => 0] + (array) $p['client']), Sites::periodLabel($p['from'], $p['to']));
            $ok = Notify::sendTest($me->email, $subject, $body, 'maintenance');
            $this->msg($ok ? 'Δοκιμαστικό εστάλη στο ' . $me->email . '.' : 'Η δοκιμαστική αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
        }
        $this->go('section=sitedigest&preview=' . $sid . '#sp-su-preview');
    }

    /* ------------------------------------------------------------ settings */

    public function saveSettings(): void
    {
        $this->guard();
        $text = static function (string $v): string {
            return mb_substr(str_replace(["\r\n", "\r"], "\n", $v), 0, 20000);
        };
        $values = [
            'doc_maintenance_from_name' => $this->str('doc_maintenance_from_name'), 'doc_maintenance_from_email' => $this->email('doc_maintenance_from_email'),
            'doc_maintenance_reply_to' => $this->email('doc_maintenance_reply_to'),
            'notify_siteupdate_subject' => $this->str('notify_siteupdate_subject', 255),
            'notify_sitedigest_freq' => isset(Sites::FREQUENCIES[$this->input->post->getCmd('notify_sitedigest_freq', '')]) ? $this->input->post->getCmd('notify_sitedigest_freq') : 'monthly',
            'notify_sitedigest_day' => max(1, min(28, $this->input->post->getInt('notify_sitedigest_day', 1))),
            'notify_sitedigest_day2' => max(1, min(28, $this->input->post->getInt('notify_sitedigest_day2', 15))),
            'notify_sitedigest_weekday' => max(1, min(7, $this->input->post->getInt('notify_sitedigest_weekday', 1))),
            'notify_siteupdate_body' => $text((string) $this->input->post->get('notify_siteupdate_body', '', 'raw')),
        ];
        foreach ($values as $k => $v) {
            OfferHelper::saveParam($k, $v);
        }
        $this->msg('Οι ρυθμίσεις αποθηκεύτηκαν.');
        $this->go('section=sitesettings');
    }
}

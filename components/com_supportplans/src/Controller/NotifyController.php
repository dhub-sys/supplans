<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/**
 * Πελάτες & Ειδοποιήσεις. Every task is POST + form token, and the Dispatcher only lets
 * signed-in (password + OTP) Service Hub users reach it.
 */
class NotifyController extends BaseController
{
    /* ------------------------------------------------------------ helpers */

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
        OfferHelper::redirect('view=notify&' . ltrim($query, '&'));
    }

    /** Return location inside the notifications page (query characters only). */
    private function back(): string
    {
        $back = (string) $this->input->post->getString('back', '');
        return preg_match('/^[a-z0-9_=&]+$/i', $back) ? $back : 'section=overview';
    }

    private function str(string $name, int $max = 190): string
    {
        return mb_substr(trim((string) $this->input->post->getString($name, '')), 0, $max);
    }

    /** Light-markup text (rendered and escaped by OfferHelper::render when shown). */
    private function text(string $name): string
    {
        return str_replace("\r\n", "\n", (string) $this->input->post->get($name, '', 'raw'));
    }

    private function email(string $name): string
    {
        $v = trim((string) $this->input->post->getString($name, ''));
        return $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
    }

    private function price(string $name): ?float
    {
        $v = trim(str_replace(['€', ' '], '', (string) $this->input->post->getString($name, '')));
        if ($v === '') return null;
        if (strpos($v, ',') !== false) $v = str_replace(['.', ','], ['', '.'], $v);
        return round(max(0, (float) $v), 2);
    }

    private function days(string $name): string
    {
        return implode(',', Notify::parseDays($this->input->post->getString($name, '')));
    }

    /** INSERT or UPDATE $fields (column => PHP value) and return the id. */
    private function store(string $table, array $fields, int $id): int
    {
        $db = $this->db();
        $now = Notify::now();
        $fields['modified'] = $now;
        if (!$id) {
            $fields['created'] = $now;
        }
        $obj = (object) $fields;
        if ($id) {
            $obj->id = $id;
            $db->updateObject($table, $obj, 'id', true);
            return $id;
        }
        $db->insertObject($table, $obj, 'id');
        return (int) $obj->id;
    }

    private function delete(string $table, string $where): void
    {
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName($table))->where($where))->execute();
    }

    private function me(): object
    {
        $s = Factory::getApplication()->getSession();
        return (object) ['id' => 0, 'name' => (string) $s->get('supportplans.auth.name', ''), 'company' => '', 'email' => (string) $s->get('supportplans.auth.email', ''), 'status' => 'active', 'unsubscribed_at' => null];
    }

    /* ------------------------------------------------------------ clients */

    public function saveClient(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $email = $this->email('email');
        $back = 'section=clients&' . ($id ? 'id=' . $id : 'mode=new');
        if ($email === '') {
            $this->msg('Συμπληρώστε έγκυρο email.', 'error');
            $this->go($back);
            return;
        }
        $other = Notify::clientByEmail($email);
        if ($other && (int) $other->id !== $id) {
            $this->msg('Υπάρχει ήδη πελάτης με αυτό το email.', 'error');
            $this->go($back);
            return;
        }
        $status = $this->input->post->getCmd('status', 'active');
        $id = $this->store(Notify::T_CLIENTS, [
            'name' => $this->str('name'), 'company' => $this->str('company'), 'email' => $email, 'phone' => $this->str('phone', 50),
            'notes' => mb_substr(trim((string) $this->input->post->getString('notes', '')), 0, 5000),
            'status' => isset(Notify::CLIENT_STATUSES[$status]) ? $status : 'active',
        ], $id);
        $this->msg('Ο πελάτης αποθηκεύτηκε.');
        $this->go('section=clients&id=' . $id);
    }

    public function deleteClient(): void
    {
        $this->guard();
        Notify::deleteClient($this->input->post->getInt('id', 0));
        $this->msg('Ο πελάτης διαγράφηκε μαζί με τις υπηρεσίες, τις αναφορές και το ιστορικό του.');
        $this->go('section=clients');
    }

    public function unsubscribe(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $db = $this->db();
        $value = $this->input->post->getInt('value', 0) ? $db->quote(Notify::now()) : 'NULL';
        $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_CLIENTS))->set('unsubscribed_at=' . $value)->where('id=' . $id))->execute();
        $this->msg('Η προτίμηση ενημερώσεων άλλαξε.');
        $this->go('section=clients&id=' . $id);
    }

    /**
     * CSV: email;name;company;phone;service;label;expiry_date;price;duration_months
     * "service" is the name of a catalogue service. Existing clients/services are updated.
     */
    public function importCsv(): void
    {
        $this->guard();
        $file = $this->input->files->get('csv', null, 'raw');
        if (!is_array($file) || (int) ($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            $this->msg('Δεν ανέβηκε αρχείο.', 'error');
            $this->go('section=clients');
            return;
        }
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true) || (int) $file['size'] > 2 * 1024 * 1024) {
            $this->msg('Επιτρέπεται μόνο αρχείο .csv έως 2 MB.', 'error');
            $this->go('section=clients');
            return;
        }
        $catalog = [];
        foreach (Notify::catalog() as $k) {
            $catalog[mb_strtolower(trim($k->name))] = $k;
        }
        $r = ['cn' => 0, 'cu' => 0, 'sn' => 0, 'su' => 0, 'errors' => []];
        $fh = fopen((string) $file['tmp_name'], 'r');
        $first = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($fh));
        $delim = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $headers = array_map(static fn($h) => strtolower(trim($h)), str_getcsv($first, $delim, '"', '\\'));
        if (!in_array('email', $headers, true)) {
            $this->msg('Λείπει η στήλη email.', 'error');
            $this->go('section=clients');
            return;
        }
        $line = 1;
        while (($cells = fgetcsv($fh, 0, $delim, '"', '\\')) !== false) {
            $line++;
            if (!array_filter($cells, static fn($c) => trim((string) $c) !== '')) continue;
            $row = [];
            foreach ($headers as $i => $h) $row[$h] = trim((string) ($cells[$i] ?? ''));
            $email = filter_var($row['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $row['email'] : '';
            if ($email === '') { $r['errors'][] = "Γραμμή $line: μη έγκυρο email."; continue; }
            $client = Notify::clientByEmail($email);
            $fields = ['email' => $email];
            foreach (['name' => 190, 'company' => 190, 'phone' => 50] as $k => $max) {
                if (($row[$k] ?? '') !== '') $fields[$k] = mb_substr($row[$k], 0, $max);
            }
            if ($client) {
                $clientId = $this->store(Notify::T_CLIENTS, $fields, (int) $client->id);
                $r['cu']++;
            } else {
                $clientId = $this->store(Notify::T_CLIENTS, $fields + ['status' => 'active'], 0);
                $r['cn']++;
            }
            $serviceName = mb_strtolower($row['service'] ?? '');
            if ($serviceName === '') continue;
            if (!isset($catalog[$serviceName])) { $r['errors'][] = "Γραμμή $line: η υπηρεσία «" . $row['service'] . '» δεν υπάρχει στον κατάλογο.'; continue; }
            $k = $catalog[$serviceName];
            $expiry = ($row['expiry_date'] ?? '') !== '' ? Notify::parseDate($row['expiry_date']) : '';
            if (($row['expiry_date'] ?? '') !== '' && $expiry === '') { $r['errors'][] = "Γραμμή $line: μη έγκυρη ημερομηνία λήξης."; continue; }
            $label = mb_substr($row['label'] ?? '', 0, 190);
            $existing = null;
            foreach (Notify::services(['client_id' => $clientId, 'catalog_id' => (int) $k->id]) as $s) {
                if (strcasecmp($s->label, $label) === 0) { $existing = $s; break; }
            }
            $fields = ['client_id' => $clientId, 'catalog_id' => (int) $k->id, 'label' => $label];
            if ($expiry !== '') $fields['expiry_date'] = $expiry;
            if (($row['price'] ?? '') !== '') $fields['price'] = round((float) str_replace(',', '.', $row['price']), 2);
            if (($row['duration_months'] ?? '') !== '') $fields['duration_months'] = max(1, min(120, (int) $row['duration_months']));
            if (!$existing) {
                $fields += ['price' => $k->price, 'duration_months' => (int) $k->duration_months, 'auto_remind' => (int) $k->auto_remind, 'status' => 'active'];
            }
            $this->store(Notify::T_SERVICES, $fields, $existing ? (int) $existing->id : 0);
            $existing ? $r['su']++ : $r['sn']++;
        }
        fclose($fh);
        $this->msg(sprintf('Εισαγωγή: %d νέοι πελάτες, %d ενημερώθηκαν, %d νέες αναθέσεις υπηρεσιών, %d ενημερώθηκαν.', $r['cn'], $r['cu'], $r['sn'], $r['su']));
        if ($r['errors']) {
            $this->msg(implode('<br>', array_map(static fn($x) => htmlspecialchars($x, ENT_QUOTES, 'UTF-8'), array_slice($r['errors'], 0, 30))), 'warning');
        }
        $this->go('section=clients');
    }

    /* ------------------------------------------------------------ catalogue */

    public function saveCatalog(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $name = $this->str('name');
        if ($name === '') {
            $this->msg('Συμπληρώστε το όνομα της υπηρεσίας.', 'error');
            $this->go('section=catalog&' . ($id ? 'id=' . $id : 'mode=new'));
            return;
        }
        $id = $this->store(Notify::T_CATALOG, [
            'name' => $name,
            'description' => mb_substr(trim((string) $this->input->post->getString('description', '')), 0, 5000),
            'price' => $this->price('price'),
            'duration_months' => max(1, min(120, $this->input->post->getInt('duration_months', 12))),
            'reminder_days' => $this->days('reminder_days'),
            'auto_remind' => $this->input->post->getInt('auto_remind', 0) ? 1 : 0,
            'published' => $this->input->post->getInt('published', 1) ? 1 : 0,
            'ordering' => $this->input->post->getInt('ordering', 0),
        ], $id);
        $this->msg('Η υπηρεσία του καταλόγου αποθηκεύτηκε.');
        $this->go('section=catalog' . ($this->input->post->getCmd('after') === 'close' ? '' : '&id=' . $id));
    }

    public function deleteCatalog(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        if (Notify::services(['catalog_id' => $id])) {
            $this->msg('Η υπηρεσία έχει ανατεθεί σε πελάτες. Απενεργοποιήστε την αντί να τη διαγράψετε.', 'warning');
        } else {
            $this->delete(Notify::T_CATALOG, 'id=' . $id);
            $this->msg('Η υπηρεσία διαγράφηκε από τον κατάλογο.');
        }
        $this->go('section=catalog');
    }

    /* ------------------------------------------------------------ client services */

    public function saveService(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $clientId = $this->input->post->getInt('client_id', 0);
        $catalog = Notify::catalogItem($this->input->post->getInt('catalog_id', 0));
        $back = 'section=services&' . ($id ? 'id=' . $id : 'mode=new&client_id=' . $clientId);
        if (!Notify::client($clientId) || !$catalog) {
            $this->msg('Επιλέξτε πελάτη και υπηρεσία.', 'error');
            $this->go($back);
            return;
        }
        $duration = $this->input->post->getInt('duration_months', 0);
        $duration = $duration > 0 ? min(120, $duration) : (int) $catalog->duration_months;
        $price = $this->price('price');
        if ($price === null && !$id) $price = $catalog->price === null ? null : (float) $catalog->price;
        $start = Notify::parseDate($this->input->post->getString('start_date', ''));
        $expiry = Notify::parseDate($this->input->post->getString('expiry_date', ''));
        if ($expiry === '' && $start !== '') {
            $expiry = Notify::addMonths($start, $duration);
        }
        $status = $this->input->post->getCmd('status', 'active');
        $id = $this->store(Notify::T_SERVICES, [
            'client_id' => $clientId, 'catalog_id' => (int) $catalog->id, 'label' => $this->str('label'), 'price' => $price,
            'duration_months' => $duration, 'start_date' => $start ?: null, 'expiry_date' => $expiry ?: null,
            'reminder_days' => $this->days('reminder_days'), 'auto_remind' => $this->input->post->getInt('auto_remind', 0) ? 1 : 0,
            'status' => isset(Notify::SERVICE_STATUSES[$status]) ? $status : 'active',
            'notes' => mb_substr(trim((string) $this->input->post->getString('notes', '')), 0, 5000),
        ], $id);
        $this->msg('Η υπηρεσία του πελάτη αποθηκεύτηκε.' . ($expiry === '' ? ' Χωρίς ημερομηνία λήξης δεν προγραμματίζονται υπενθυμίσεις.' : ''), $expiry === '' ? 'warning' : 'message');
        $this->go($this->input->post->getCmd('after') === 'client' ? 'section=clients&id=' . $clientId : 'section=services&id=' . $id);
    }

    public function deleteService(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $this->delete(Notify::T_REPORTS, 'client_service_id=' . $id);
        $this->delete(Notify::T_MESSAGES, 'service_id=' . $id . " AND status='queued'");
        $this->delete(Notify::T_SERVICES, 'id=' . $id);
        $this->msg('Η υπηρεσία του πελάτη διαγράφηκε.');
        $this->go('section=services');
    }

    public function renew(): void
    {
        $this->guard();
        $new = Notify::renew($this->input->post->getInt('id', 0));
        $this->msg($new ? 'Νέα ημερομηνία λήξης: ' . Notify::dateGr($new) . '.' : 'Η υπηρεσία δεν βρέθηκε.', $new ? 'message' : 'error');
        $this->go($this->back());
    }

    public function remindNow(): void
    {
        $this->guard();
        $s = Notify::service($this->input->post->getInt('id', 0));
        $id = $s ? Notify::queueRenewal($s) : 0;
        $ok = $id && Notify::sendNow($id);
        $this->msg($ok ? 'Η υπενθύμιση εστάλη στο ' . $s->client_email . '.' : 'Η αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
        $this->go($this->back());
    }

    /* ------------------------------------------------------------ maintenance reports */

    public function saveReport(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $existing = $id ? Notify::report($id) : null;
        if ($existing && $existing->status === 'sent') {
            $this->msg('Η αναφορά έχει ήδη σταλεί.', 'error');
            $this->go('section=reports&id=' . $id);
            return;
        }
        $service = Notify::service($this->input->post->getInt('client_service_id', 0));
        if (!$service) {
            $this->msg('Επιλέξτε υπηρεσία πελάτη.', 'error');
            $this->go('section=reports&mode=new');
            return;
        }
        $id = $this->store(Notify::T_REPORTS, [
            'client_service_id' => (int) $service->id, 'period' => $this->str('period', 100),
            'summary' => $this->text('summary'), 'items' => mb_substr($this->text('items'), 0, 50000),
        ] + ($id ? [] : ['status' => 'draft']), $id);
        $report = Notify::report($id);
        $client = Notify::client((int) $service->client_id);
        [$subject, $body] = Notify::buildReport($report, $service, $client);
        $do = $this->input->post->getCmd('do', 'save');
        if ($do === 'test') {
            $me = $this->me();
            $ok = Notify::sendTest($me->email, $subject, $body);
            $this->msg($ok ? 'Δοκιμαστικό εστάλη στο ' . $me->email . '.' : 'Η δοκιμαστική αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
        } elseif ($do === 'send') {
            $mid = Notify::queue(['client_id' => $client->id, 'service_id' => $service->id, 'kind' => 'report', 'ref' => 'report:' . $id,
                'to_email' => $client->email, 'subject' => $subject, 'body' => $body]);
            if (!$mid) {
                $this->msg('Υπάρχει ήδη μήνυμα για αυτή την αναφορά στο Ιστορικό.', 'error');
            } else {
                $db = $this->db();
                $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_REPORTS))
                    ->set(["status='sent'", 'sent=' . $db->quote(Notify::now())])->where('id=' . $id))->execute();
                $ok = Notify::sendNow($mid);
                $this->msg($ok ? 'Η αναφορά εστάλη στο ' . $client->email . '.' : 'Η άμεση αποστολή απέτυχε· θα ξαναδοκιμαστεί αυτόματα — δείτε το Ιστορικό.', $ok ? 'message' : 'warning');
            }
        } else {
            $this->msg('Η αναφορά αποθηκεύτηκε.');
        }
        $this->go('section=reports&id=' . $id);
    }

    public function deleteReport(): void
    {
        $this->guard();
        $this->delete(Notify::T_REPORTS, 'id=' . $this->input->post->getInt('id', 0));
        $this->msg('Η αναφορά διαγράφηκε.');
        $this->go('section=reports');
    }

    /* ------------------------------------------------------------ campaigns */

    public function saveCampaign(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $existing = $id ? Notify::campaign($id) : null;
        if ($existing && $existing->status !== 'draft') {
            $this->msg('Η αποστολή έχει ήδη ξεκινήσει.', 'error');
            $this->go('section=campaigns&id=' . $id);
            return;
        }
        $mode = $this->input->post->getCmd('aud_mode', 'all');
        $aud = ['mode' => in_array($mode, ['all', 'catalog', 'clients'], true) ? $mode : 'all'];
        if ($aud['mode'] === 'catalog') $aud['catalog'] = array_values(array_filter(array_map('intval', (array) $this->input->post->get('aud_catalog', [], 'array'))));
        if ($aud['mode'] === 'clients') $aud['clients'] = array_values(array_filter(array_map('intval', (array) $this->input->post->get('aud_clients', [], 'array'))));
        $id = $this->store(Notify::T_CAMPAIGNS, [
            'subject' => $this->str('subject', 255), 'body' => $this->text('body'), 'audience' => json_encode($aud),
        ] + ($id ? [] : ['status' => 'draft']), $id);
        $campaign = Notify::campaign($id);
        $do = $this->input->post->getCmd('do', 'save');
        if ($do === 'test') {
            $me = $this->me();
            [$subject, $body] = Notify::buildCampaign($campaign, $me);
            $ok = Notify::sendTest($me->email, $subject, $body);
            $this->msg($ok ? 'Δοκιμαστικό εστάλη στο ' . $me->email . '.' : 'Η δοκιμαστική αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
        } elseif ($do === 'send') {
            if (trim($campaign->subject) === '' || trim($campaign->body) === '') {
                $this->msg('Συμπληρώστε θέμα και κείμενο.', 'error');
                $this->go('section=campaigns&id=' . $id);
                return;
            }
            $ids = Notify::audience($aud);
            if (!$ids) {
                $this->msg('Δεν υπάρχουν παραλήπτες με αυτές τις επιλογές.', 'error');
                $this->go('section=campaigns&id=' . $id);
                return;
            }
            $db = $this->db();
            // Claim the campaign atomically so a double click cannot queue it twice.
            $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_CAMPAIGNS))
                ->set(["status='queued'", 'queued=' . $db->quote(Notify::now()), 'recipients=' . count($ids)])
                ->where('id=' . $id)->where("status='draft'"))->execute();
            if ($db->getAffectedRows() > 0) {
                $n = 0;
                foreach ($ids as $cid) {
                    $client = Notify::client($cid);
                    [$subject, $body, $unsub] = Notify::buildCampaign($campaign, $client);
                    $n += Notify::queue(['client_id' => $cid, 'campaign_id' => $id, 'kind' => 'campaign', 'ref' => 'campaign:' . $id . ':' . $cid,
                        'to_email' => $client->email, 'subject' => $subject, 'body' => $body, 'unsub_url' => $unsub]) ? 1 : 0;
                }
                $this->msg($n . ' μηνύματα μπήκαν σε ουρά και αποστέλλονται σταδιακά.');
            }
        } else {
            $this->msg('Η αποστολή αποθηκεύτηκε ως πρόχειρο.');
        }
        $this->go('section=campaigns&id=' . $id);
    }

    public function deleteCampaign(): void
    {
        $this->guard();
        $this->delete(Notify::T_CAMPAIGNS, 'id=' . $this->input->post->getInt('id', 0) . " AND status='draft'");
        $this->msg('Το πρόχειρο διαγράφηκε.');
        $this->go('section=campaigns');
    }

    /* ------------------------------------------------------------ log & queue */

    public function retry(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_MESSAGES))
            ->set(["status='queued'", 'attempts=0', 'last_error=NULL', 'scheduled=' . $db->quote(Notify::now())])
            ->where('id=' . $id)->where("status IN ('failed','cancelled')"))->execute();
        $ok = Notify::sendNow($id);
        $m = Notify::message($id);
        $this->msg($ok ? 'Το μήνυμα εστάλη.' : 'Δεν εστάλη: ' . ($m ? (string) $m->last_error : ''), $ok ? 'message' : 'error');
        $this->go('section=log');
    }

    public function cancel(): void
    {
        $this->guard();
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(Notify::T_MESSAGES))
            ->set(["status='cancelled'", 'last_error=' . $db->quote('Ακυρώθηκε από διαχειριστή.')])
            ->where('id=' . $this->input->post->getInt('id', 0))->where("status='queued'"))->execute();
        $this->msg('Το μήνυμα ακυρώθηκε.');
        $this->go('section=log');
    }

    public function runNow(): void
    {
        $this->guard();
        $r = Notify::run();
        $this->msg(sprintf('Νέες υπενθυμίσεις: %d · Εστάλησαν: %d · Απέτυχαν: %d', $r['queued'], $r['sent'], $r['failed']), $r['failed'] ? 'warning' : 'message');
        $this->go($this->back());
    }

    /* ------------------------------------------------------------ settings */

    public function saveSettings(): void
    {
        $this->guard();
        $values = [
            'notify_from_name' => $this->str('notify_from_name'),
            'notify_from_email' => $this->email('notify_from_email'),
            'notify_reply_to' => $this->email('notify_reply_to'),
            'notify_bcc' => $this->email('notify_bcc'),
            'notify_reminder_days' => $this->days('notify_reminder_days') ?: Notify::DEFAULT_REMINDER_DAYS,
            'notify_batch_size' => max(1, min(200, $this->input->post->getInt('notify_batch_size', 20))),
            'notify_footer' => mb_substr(trim((string) $this->input->post->getString('notify_footer', '')), 0, 2000),
            'notify_renewal_subject' => $this->str('notify_renewal_subject', 255),
            'notify_renewal_body' => $this->text('notify_renewal_body'),
            'notify_report_subject' => $this->str('notify_report_subject', 255),
            'notify_report_body' => $this->text('notify_report_body'),
        ];
        foreach ($values as $k => $v) {
            OfferHelper::saveParam($k, $v);
        }
        $this->msg('Οι ρυθμίσεις ειδοποιήσεων αποθηκεύτηκαν.');
        $this->go('section=settings');
    }

    public function sendTest(): void
    {
        $this->guard();
        $to = $this->email('to');
        if ($to === '') {
            $this->msg('Μη έγκυρο email.', 'error');
            $this->go('section=settings');
            return;
        }
        $client = $this->me();
        $client->email = $to;
        $service = (object) ['id' => 0, 'client_id' => 0, 'service_name' => 'Web Hosting', 'label' => 'example.gr', 'price' => 120,
            'duration_months' => 12, 'expiry_date' => Notify::addDays(Notify::today(), 14), 'status' => 'active'];
        if ($this->input->post->getCmd('template') === 'report') {
            $service->service_name = 'Maintenance';
            $report = (object) ['period' => 'Δοκιμή', 'summary' => 'Έγινε πλήρες backup, έλεγχος ασφαλείας και ενημέρωση όλων των στοιχείων.',
                'items' => "Joomla | Core | 6.2.0 | 6.2.1\nExtension | Support Plans | 1.11.2 | 1.12.0"];
            [$subject, $body] = Notify::buildReport($report, $service, $client);
        } else {
            [$subject, $body] = Notify::buildRenewal($service, $client);
        }
        $ok = Notify::sendTest($to, $subject, $body);
        $this->msg($ok ? 'Το δοκιμαστικό εστάλη στο ' . $to . '.' : 'Η αποστολή απέτυχε — δείτε το Ιστορικό για το σφάλμα.', $ok ? 'message' : 'error');
        $this->go('section=settings');
    }
}

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
            'name' => $this->str('name'), 'company' => $this->str('company'), 'email' => $email,
            'salutation' => rtrim($this->str('salutation'), ' ,'),
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

    /** Columns of the Excel/CSV file: key => accepted headers (normalised, without accents). */
    private const SHEET_COLUMNS = [
        'email'           => ['email', 'e-mail', 'ηλεκτρονικο ταχυδρομειο'],
        'name'            => ['name', 'ονοματεπωνυμο', 'ονομα'],
        'company'         => ['company', 'εταιρεια', 'επωνυμια'],
        'service'         => ['service', 'υπηρεσια'],
        'label'           => ['label', 'domain', 'domain / περιγραφη', 'περιγραφη'],
        'expiry_date'     => ['expiry_date', 'ληξη', 'ημερομηνια ληξης', 'ημ/νια ληξης'],
        'price'           => ['price', 'τιμη', 'τιμη (προ φπα)', 'τιμη προ φπα', 'τιμη (€ προ φπα)'],
        'duration_months' => ['duration_months', 'διαρκεια', 'διαρκεια (μηνες)', 'μηνες'],
        'salutation'      => ['salutation', 'greeting', 'προσφωνηση'],
    ];
    /** Headers of the template and of the export, in this order. */
    private const SHEET_HEADERS = ['Email *', 'Ονοματεπώνυμο', 'Εταιρεία', 'Υπηρεσία', 'Domain / περιγραφή', 'Ημερομηνία λήξης', 'Τιμή (προ ΦΠΑ)', 'Διάρκεια (μήνες)', 'Προσφώνηση'];

    private static function normaliseHeader(string $h): string
    {
        $h = mb_strtolower(trim(str_replace(['*', "\xC2\xA0"], ['', ' '], $h)));
        $h = strtr($h, ['ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ϊ' => 'ι', 'ΐ' => 'ι', 'ό' => 'ο', 'ύ' => 'υ', 'ϋ' => 'υ', 'ΰ' => 'υ', 'ώ' => 'ω']);
        return trim(preg_replace('/\s+/u', ' ', $h));
    }

    /** Value of a number cell or text such as "120,50" / "1.200,50" / "120.5". */
    private static function number(string $v): ?float
    {
        $v = trim(str_replace(['€', ' ', "\xC2\xA0"], '', $v));
        if ($v === '') return null;
        if (strpos($v, ',') !== false) $v = str_replace(['.', ','], ['', '.'], $v);
        return is_numeric($v) ? (float) $v : null;
    }

    /**
     * Import from Excel (.xlsx, sheet "Πελάτες" or the first one) or CSV. One row per client
     * service: several rows with the same e-mail give the client several services, each with its
     * own expiry date. A row without "Υπηρεσία" only creates/updates the client. Existing clients
     * (same e-mail) and services (same client + service + domain) are updated, never duplicated.
     */
    public function importSheet(): void
    {
        $this->guard();
        $file = $this->input->files->get('sheet', null, 'raw');
        if (!is_array($file) || (int) ($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            $this->msg('Δεν ανέβηκε αρχείο.', 'error');
            $this->go('section=clients');
            return;
        }
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'csv'], true) || (int) $file['size'] > 5 * 1024 * 1024) {
            $this->msg('Επιτρέπεται αρχείο Excel (.xlsx) ή .csv έως 5 MB.', 'error');
            $this->go('section=clients');
            return;
        }
        try {
            $rows = $ext === 'xlsx' ? \Support\Component\Supportplans\Site\Helper\Xlsx::read((string) $file['tmp_name'], 'Πελάτες') : $this->readCsv((string) $file['tmp_name']);
        } catch (\Throwable $e) {
            $this->msg($e->getMessage(), 'error');
            $this->go('section=clients');
            return;
        }
        $r = $this->importRows($rows);
        $this->msg(sprintf('Εισαγωγή: %d νέοι πελάτες, %d ενημερώθηκαν · %d νέες υπηρεσίες σε πελάτες, %d ενημερώθηκαν.', $r['cn'], $r['cu'], $r['sn'], $r['su']));
        if ($r['errors']) {
            $this->msg('Γραμμές που δεν περάστηκαν:<br>' . implode('<br>', array_map(static fn($x) => htmlspecialchars($x, ENT_QUOTES, 'UTF-8'), array_slice($r['errors'], 0, 40)))
                . (count($r['errors']) > 40 ? '<br>… και ' . (count($r['errors']) - 40) . ' ακόμα.' : ''), 'warning');
        }
        $this->go('section=clients');
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

    /** $rows: line number => cells; the first row is the header. */
    private function importRows(array $rows): array
    {
        $r = ['cn' => 0, 'cu' => 0, 'sn' => 0, 'su' => 0, 'errors' => []];
        if (!$rows) {
            $r['errors'][] = 'Το αρχείο είναι κενό.';
            return $r;
        }
        // Keep the keys: they are the line numbers of the file, shown in the error messages.
        $headerLine = array_key_first($rows);
        $header = $rows[$headerLine];
        unset($rows[$headerLine]);
        $map = [];
        foreach ($header as $i => $h) {
            $h = self::normaliseHeader((string) $h);
            foreach (self::SHEET_COLUMNS as $key => $names) {
                if (in_array($h, $names, true) && !isset($map[$key])) $map[$key] = $i;
            }
        }
        if (!isset($map['email'])) {
            $r['errors'][] = 'Γραμμή ' . $headerLine . ': δεν βρέθηκε η στήλη «Email». Χρησιμοποιήστε το πρότυπο Excel.';
            return $r;
        }
        $catalog = [];
        foreach (Notify::catalog() as $k) {
            $catalog[mb_strtolower(trim($k->name))] = $k;
        }
        $seenClients = [];
        foreach ($rows as $line => $cells) {
            $get = static fn($key) => isset($map[$key]) ? trim((string) ($cells[$map[$key]] ?? '')) : '';
            if (!array_filter(array_map(static fn($c) => trim((string) $c) !== '', $cells))) continue;
            $email = $get('email');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $r['errors'][] = "Γραμμή $line: μη έγκυρο email «" . $email . '».'; continue; }
            $email = mb_strtolower($email);

            $client = Notify::clientByEmail($email);
            $fields = ['email' => $email];
            foreach (['name', 'company'] as $k) {
                if ($get($k) !== '') $fields[$k] = mb_substr($get($k), 0, 190);
            }
            if ($get('salutation') !== '') $fields['salutation'] = mb_substr(rtrim($get('salutation'), ' ,'), 0, 190);
            if ($client) {
                $clientId = $this->store(Notify::T_CLIENTS, $fields, (int) $client->id);
                if (!isset($seenClients[$clientId])) $r['cu']++;
            } else {
                $clientId = $this->store(Notify::T_CLIENTS, $fields + ['status' => 'active'], 0);
                $r['cn']++;
            }
            $seenClients[$clientId] = true;

            $serviceName = $get('service');
            if ($serviceName === '') continue;
            $k = $catalog[mb_strtolower($serviceName)] ?? null;
            if (!$k) { $r['errors'][] = "Γραμμή $line: η υπηρεσία «" . $serviceName . '» δεν υπάρχει στον κατάλογο.'; continue; }
            $expiry = $get('expiry_date') !== '' ? \Support\Component\Supportplans\Site\Helper\Xlsx::toDate($get('expiry_date')) : '';
            if ($get('expiry_date') !== '' && $expiry === '') { $r['errors'][] = "Γραμμή $line: μη έγκυρη ημερομηνία λήξης «" . $get('expiry_date') . '» (π.χ. 25/02/2027).'; continue; }
            $price = self::number($get('price'));
            if ($get('price') !== '' && $price === null) { $r['errors'][] = "Γραμμή $line: μη έγκυρη τιμή «" . $get('price') . '».'; continue; }
            $duration = $get('duration_months') !== '' ? (int) self::number($get('duration_months')) : 0;
            $label = mb_substr($get('label'), 0, 190);

            $existing = null;
            foreach (Notify::services(['client_id' => $clientId, 'catalog_id' => (int) $k->id]) as $s) {
                if (strcasecmp($s->label, $label) === 0) { $existing = $s; break; }
            }
            $fields = ['client_id' => $clientId, 'catalog_id' => (int) $k->id, 'label' => $label];
            if ($expiry !== '') $fields['expiry_date'] = $expiry;
            if ($price !== null) $fields['price'] = round(max(0, $price), 2);
            if ($duration > 0) $fields['duration_months'] = min(120, $duration);
            if (!$existing) {
                $fields += ['price' => $k->price, 'duration_months' => (int) $k->duration_months, 'auto_remind' => (int) $k->auto_remind, 'status' => 'active'];
            } elseif ($expiry !== '' && $expiry !== (string) $existing->expiry_date) {
                // New expiry from the file: reminders queued for the old date are stale.
                Notify::cancelQueuedRenewals((int) $existing->id, 'Η ημερομηνία λήξης άλλαξε από εισαγωγή Excel.');
            }
            $this->store(Notify::T_SERVICES, $fields, $existing ? (int) $existing->id : 0);
            $existing ? $r['su']++ : $r['sn']++;
        }
        return $r;
    }

    /** Excel template to fill in: "Πελάτες" (with a drop-down of the catalogue), "Κατάλογος", "Οδηγίες". */
    public function template(): void
    {
        $this->sendWorkbook('protypo-pelates.xlsx', [[...self::SHEET_HEADERS]]);
    }

    /** All clients and their services in the template format (to edit and import again). */
    public function export(): void
    {
        $rows = [[...self::SHEET_HEADERS]];
        $withService = [];
        foreach (Notify::services() as $s) {
            $withService[(int) $s->client_id] = true;
            $rows[] = [$s->client_email, $s->client_name, $s->client_company, (string) $s->service_name, $s->label, (string) $s->expiry_date,
                $s->price === null ? '' : (float) $s->price, (int) $s->duration_months, (string) ($s->client_salutation ?? '')];
        }
        foreach (Notify::clients() as $c) {
            if (!isset($withService[(int) $c->id])) $rows[] = [$c->email, $c->name, $c->company, '', '', '', '', '', (string) ($c->salutation ?? '')];
        }
        $this->sendWorkbook('pelates-' . Notify::today() . '.xlsx', $rows);
    }

    private function sendWorkbook(string $filename, array $clientRows): void
    {
        $app = Factory::getApplication();
        if (!\Support\Component\Supportplans\Site\Helper\Xlsx::available()) {
            $this->msg('Ο server δεν υποστηρίζει αρχεία Excel (λείπει η επέκταση PHP zip).', 'error');
            $this->go('section=clients');
            return;
        }
        $catalog = Notify::catalog();
        $catalogRows = [['Υπηρεσία', 'Τιμή (προ ΦΠΑ)', 'Διάρκεια (μήνες)', 'Ενεργή']];
        foreach ($catalog as $k) {
            $catalogRows[] = [(string) $k->name, $k->price === null ? '' : (float) $k->price, (int) $k->duration_months, (int) $k->published ? 'Ναι' : 'Όχι'];
        }
        $help = [['Οδηγίες συμπλήρωσης'],
            ['Συμπληρώστε το φύλλο «Πελάτες»: μία γραμμή για κάθε υπηρεσία κάθε πελάτη.'],
            ['Πελάτης με 3 υπηρεσίες = 3 γραμμές με το ίδιο email· κάθε γραμμή έχει τη δική της ημερομηνία λήξης.'],
            ['Email: υποχρεωτικό. Με αυτό αναγνωρίζεται ο πελάτης (αν υπάρχει ήδη, ενημερώνεται).'],
            ['Ονοματεπώνυμο / Εταιρεία: αρκεί στην πρώτη γραμμή του πελάτη.'],
            ['Υπηρεσία: επιλέξτε από τη λίστα (φύλλο «Κατάλογος»). Κενό = μόνο ο πελάτης, χωρίς υπηρεσία.'],
            ['Domain / περιγραφή: π.χ. example.gr. Ίδιος πελάτης + υπηρεσία + domain = ενημέρωση της υπάρχουσας, όχι νέα.'],
            ['Ημερομηνία λήξης: π.χ. 25/02/2027. Από αυτήν προγραμματίζονται οι υπενθυμίσεις.'],
            ['Τιμή (προ ΦΠΑ) και Διάρκεια (μήνες): αν μείνουν κενά, χρησιμοποιούνται του καταλόγου.'],
            ['Προσφώνηση (προαιρετικό): π.χ. «Αγαπητέ κύριε Δημητρίου», χωρίς κόμμα. Κενό = η γενική προσφώνηση των ρυθμίσεων email (π.χ. «Αγαπητέ/ή πελάτη»).'],
            [''],
            ['Παράδειγμα (ένας πελάτης με δύο υπηρεσίες):'],
            ['info@example.gr | Γιώργος Παπαδόπουλος | Example ΑΕ | ' . ($catalog[0]->name ?? 'Web Hosting') . ' | example.gr | 25/02/2027 | 120 | 12'],
            ['info@example.gr |  |  | ' . ($catalog[1]->name ?? ($catalog[0]->name ?? 'Ανανέωση Domain .gr')) . ' | example.gr | 24/02/2027 |  | '],
        ];
        $sheets = [
            ['name' => 'Πελάτες', 'rows' => $clientRows, 'widths' => [30, 26, 24, 30, 24, 17, 15, 15, 30], 'dateCols' => [5],
                // Grows with the "Κατάλογος" sheet: services added there later are in the drop-down too.
                'list' => $catalog ? ['col' => 3, 'source' => "OFFSET('Κατάλογος'!\$A\$2,0,0,MAX(1,COUNTA('Κατάλογος'!\$A:\$A)-1),1)"] : null],
            ['name' => 'Κατάλογος', 'rows' => $catalogRows, 'widths' => [36, 16, 17, 10]],
            ['name' => 'Οδηγίες', 'rows' => $help, 'widths' => [120]],
        ];
        $data = \Support\Component\Supportplans\Site\Helper\Xlsx::build($sheets);
        $app->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true);
        $app->setHeader('Content-Length', (string) strlen($data), true);
        $app->setHeader('Cache-Control', 'no-store', true);
        $app->sendHeaders();
        echo $data;
        $app->close();
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
            'expired_text' => mb_substr(trim($this->text('expired_text')), 0, 20000),
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
        Notify::cancelQueuedRenewals($id, 'Η υπηρεσία διαγράφηκε.');
        $this->delete(Notify::T_REPORTS, 'client_service_id=' . $id);
        $this->delete(Notify::T_RENEWALS, 'service_id=' . $id);
        $this->delete(Notify::T_SERVICES, 'id=' . $id);
        $this->msg('Η υπηρεσία του πελάτη διαγράφηκε.');
        $this->go('section=services');
    }

    /**
     * "Πληρώθηκε": records the payment and moves the expiry. Optional fields: new_expiry (else one
     * period after the current expiry), paid_on (else today), amount, note.
     */
    public function renew(): void
    {
        $this->guard();
        $id = $this->input->post->getInt('id', 0);
        $newExpiry = Notify::parseDate($this->input->post->getString('new_expiry', ''));
        $paidOn = Notify::parseDate($this->input->post->getString('paid_on', ''));
        $s = Notify::service($id);
        if ($s && $newExpiry !== '' && $s->expiry_date && $newExpiry <= $s->expiry_date) {
            $this->msg('Η νέα ημερομηνία λήξης πρέπει να είναι μετά την τρέχουσα (' . Notify::dateGr($s->expiry_date) . ').', 'error');
            $this->go($this->back());
            return;
        }
        $new = Notify::renew($id, $newExpiry ?: null, $paidOn ?: null, $this->price('amount'), mb_substr(trim((string) $this->input->post->getString('note', '')), 0, 1000));
        $this->msg($new ? 'Καταχωρήθηκε η πληρωμή. Νέα λήξη: ' . Notify::dateGr($new) . '· οι υπενθυμίσεις για την προηγούμενη λήξη σταμάτησαν. Αν έγινε κατά λάθος, πατήστε «Αναίρεση» στο ιστορικό πληρωμών της υπηρεσίας.' : 'Η υπηρεσία δεν βρέθηκε.', $new ? 'message' : 'error');
        $this->go($this->back());
    }

    /** Undo a payment registered by mistake (latest payment of the service only). */
    public function undoRenewal(): void
    {
        $this->guard();
        $error = Notify::undoRenewal($this->input->post->getInt('id', 0));
        $this->msg($error === '' ? 'Η πληρωμή αναιρέθηκε: η υπηρεσία επανήλθε στην προηγούμενη λήξη και οι υπενθυμίσεις συνεχίζουν κανονικά.' : $error, $error === '' ? 'message' : 'error');
        $this->go($this->back());
    }

    /** "Πληρώθηκαν όλες": every service of a (combined) reminder, each by its own period. */
    public function renewGroup(): void
    {
        $this->guard();
        $clientId = $this->input->post->getInt('client_id', 0);
        $done = [];
        foreach (array_unique(array_map('intval', (array) $this->input->post->get('ids', [], 'array'))) as $id) {
            $s = Notify::service($id);
            if (!$s || (int) $s->client_id !== $clientId) continue;
            $new = Notify::renew($id);
            if ($new) $done[] = Notify::serviceFull($s) . ' → ' . Notify::dateGr($new);
        }
        $this->msg($done ? 'Καταχωρήθηκε πληρωμή για: ' . implode(', ', $done) . '. Αν έγινε κατά λάθος, πατήστε «Αναίρεση» στις «Πρόσφατες πληρωμές» παρακάτω.' : 'Δεν επιλέχθηκαν υπηρεσίες.', $done ? 'message' : 'warning');
        $this->go('section=clients&id=' . $clientId);
    }

    /**
     * Manual reminder. With ids[] (+ client_id): one message for exactly the chosen services of the
     * client. With id only: the service together with its group of close expiry dates.
     */
    public function remindNow(): void
    {
        $this->guard();
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->input->post->get('ids', [], 'array')))));
        if ($ids) {
            $client = Notify::client($this->input->post->getInt('client_id', 0));
            $services = $client ? array_filter(array_map([Notify::class, 'service'], $ids), static fn($x) => $x && (int) $x->client_id === (int) $client->id) : [];
            $id = $client && $services ? Notify::queueRenewalFor($services, $client) : 0;
            $to = $client->email ?? '';
            if (!$services) {
                $this->msg('Επιλέξτε τουλάχιστον μία υπηρεσία.', 'error');
                $this->go($this->back());
                return;
            }
        } else {
            $s = Notify::service($this->input->post->getInt('id', 0));
            $id = $s ? Notify::queueRenewal($s) : 0;
            $to = $s->client_email ?? '';
        }
        $ok = $id && Notify::sendNow($id);
        $this->msg($ok ? 'Η υπενθύμιση εστάλη στο ' . $to . '.' : 'Η αποστολή απέτυχε — δείτε το Ιστορικό.', $ok ? 'message' : 'error');
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
        $r = Notify::run(true);
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
            'notify_greeting' => rtrim($this->str('notify_greeting'), ' ,'),
            'notify_paid_notice' => mb_substr(trim($this->text('notify_paid_notice')), 0, 2000),
            'notify_paid_notice_multi' => mb_substr(trim($this->text('notify_paid_notice_multi')), 0, 2000),
            'doc_offer_from_name' => $this->str('doc_offer_from_name'), 'doc_offer_from_email' => $this->email('doc_offer_from_email'),
            'doc_offer_reply_to' => $this->email('doc_offer_reply_to'), 'doc_offer_bcc' => $this->email('doc_offer_bcc'),
            'doc_structure_from_name' => $this->str('doc_structure_from_name'), 'doc_structure_from_email' => $this->email('doc_structure_from_email'),
            'doc_structure_reply_to' => $this->email('doc_structure_reply_to'), 'doc_structure_bcc' => $this->email('doc_structure_bcc'),
            'doc_plan_from_name' => $this->str('doc_plan_from_name'), 'doc_plan_from_email' => $this->email('doc_plan_from_email'),
            'doc_plan_reply_to' => $this->email('doc_plan_reply_to'), 'doc_plan_bcc' => $this->email('doc_plan_bcc'),
            'notify_reminder_days' => $this->days('notify_reminder_days') ?: Notify::DEFAULT_REMINDER_DAYS,
            'notify_batch_size' => max(1, min(200, $this->input->post->getInt('notify_batch_size', 20))),
            'notify_vat_rate' => max(0, min(100, round((float) str_replace(',', '.', (string) $this->input->post->getString('notify_vat_rate', '24')), 2))),
            'notify_group_days' => max(0, min(60, $this->input->post->getInt('notify_group_days', 7))),
            'notify_send_time' => preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim((string) $this->input->post->getString('notify_send_time', '')), $tm) ? sprintf('%02d:%02d', (int) $tm[1], (int) $tm[2]) : '',
            'notify_font' => isset(Notify::FONTS[$this->input->post->getCmd('notify_font', '')]) ? $this->input->post->getCmd('notify_font') : 'geom',
            'notify_font_outlook' => isset(Notify::OUTLOOK_FONTS[$this->input->post->getCmd('notify_font_outlook', '')]) ? $this->input->post->getCmd('notify_font_outlook') : 'aptos',
            'notify_subject_today' => $this->str('notify_subject_today', 255),
            'notify_subject_after' => $this->str('notify_subject_after', 255),
            'notify_intro_single' => mb_substr(trim($this->text('notify_intro_single')), 0, 2000),
            'notify_intro_multi' => mb_substr(trim($this->text('notify_intro_multi')), 0, 2000),
            'notify_phrase_before' => $this->str('notify_phrase_before', 255),
            'notify_phrase_today' => $this->str('notify_phrase_today', 255),
            'notify_phrase_after' => $this->str('notify_phrase_after', 255),
            'notify_expired_text' => mb_substr(trim($this->text('notify_expired_text')), 0, 20000),
            'notify_footer' => mb_substr(trim((string) $this->input->post->getString('notify_footer', '')), 0, 2000),
            'notify_renewal_subject' => $this->str('notify_renewal_subject', 255),
            'notify_renewal_body' => $this->text('notify_renewal_body'),
            'notify_report_subject' => $this->str('notify_report_subject', 255),
            'notify_report_body' => $this->text('notify_report_body'),
        ];
        foreach ($values as $k => $v) {
            OfferHelper::saveParam($k, $v);
        }
        $this->saveEmailLogo();
        $this->msg('Οι ρυθμίσεις ειδοποιήσεων αποθηκεύτηκαν.');
        $this->go('section=settings');
    }

    /**
     * E-mail logo: an uploaded PNG/JPG/GIF, or the PNG the settings page makes in the browser
     * from the SVG logo (data URL). Always re-encoded with GD to a clean PNG, so nothing but
     * pixels is stored; max 1 MB, max 1200×600.
     */
    private function saveEmailLogo(): void
    {
        $dir = JPATH_ROOT . '/images/supportplans';
        $target = $dir . '/email-logo.png';
        if ($this->input->post->getInt('notify_email_logo_remove', 0)) {
            if (is_file($target)) @unlink($target);
            OfferHelper::saveParam('notify_email_logo', '');
            return;
        }
        $bytes = '';
        $upload = $this->input->files->get('notify_email_logo_upload', null, 'raw');
        if (is_array($upload) && (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && is_uploaded_file((string) $upload['tmp_name'])) {
            $bytes = (int) $upload['size'] <= 1024 * 1024 ? (string) file_get_contents((string) $upload['tmp_name']) : '';
        } else {
            $data = (string) $this->input->post->get('notify_email_logo_data', '', 'raw');
            if (preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $data, $m)) {
                $bytes = (string) base64_decode($m[1], true);
            }
        }
        if ($bytes === '') {
            return;
        }
        $info = @getimagesizefromstring($bytes);
        if (!$info || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true) || strlen($bytes) > 1024 * 1024 || $info[0] > 1200 || $info[1] > 600 || !function_exists('imagecreatefromstring')) {
            $this->msg('Το λογότυπο email πρέπει να είναι PNG, JPG ή GIF έως 1 MB και 1200×600 pixels.', 'error');
            return;
        }
        $img = @imagecreatefromstring($bytes);
        if (!$img) {
            $this->msg('Το αρχείο του λογοτύπου δεν διαβάζεται.', 'error');
            return;
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (!@imagepng($img, $target, 9)) {
            $this->msg('Δεν ήταν δυνατή η αποθήκευση του λογοτύπου στο ' . $dir . '.', 'error');
            return;
        }
        imagedestroy($img);
        @chmod($target, 0644);
        OfferHelper::saveParam('notify_email_logo', 'images/supportplans/email-logo.png');
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
        $tpl = $this->input->post->getCmd('template');
        if ($tpl === 'group') {
            // One message for three services of the same client: expired, expiring today, upcoming.
            $demo = [
                (object) ['id' => 0, 'service_name' => 'Ανανέωση Domain .gr', 'label' => 'example.gr', 'price' => 15, 'duration_months' => 24, 'expiry_date' => Notify::addDays(Notify::today(), -2), 'catalog_expired_text' => ''],
                (object) ['id' => 0, 'service_name' => 'Web Hosting', 'label' => 'example.gr', 'price' => 120, 'duration_months' => 12, 'expiry_date' => Notify::today(), 'catalog_expired_text' => ''],
                (object) ['id' => 0, 'service_name' => 'SSL Certificate', 'label' => 'example.gr', 'price' => 40, 'duration_months' => 12, 'expiry_date' => Notify::addDays(Notify::today(), 3), 'catalog_expired_text' => ''],
            ];
            [$subject, $body] = Notify::buildRenewal($demo, $client);
        } elseif ($tpl === 'report') {
            $service->service_name = 'Maintenance';
            $report = (object) ['period' => 'Δοκιμή', 'summary' => 'Έγινε πλήρες backup, έλεγχος ασφαλείας και ενημέρωση όλων των στοιχείων.',
                'items' => "Joomla | Core | 6.2.0 | 6.2.1\nExtension | Back Office | 1.11.2 | 1.12.0"];
            [$subject, $body] = Notify::buildReport($report, $service, $client);
        } else {
            [$subject, $body] = Notify::buildRenewal($service, $client);
        }
        $ok = Notify::sendTest($to, $subject, $body);
        $this->msg($ok ? 'Το δοκιμαστικό εστάλη στο ' . $to . '.' : 'Η αποστολή απέτυχε — δείτε το Ιστορικό για το σφάλμα.', $ok ? 'message' : 'error');
        $this->go('section=settings');
    }
}

<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Activity log ("Ιστορικό ενεργειών") of the whole Service Hub: packages, work entries, plans,
 * offers, settings, client links, sign-in, Client Management and the scheduled runs.
 *
 * Every task goes through the site Dispatcher, which calls begin() before the task runs and
 * finish() when the request ends (also after a redirect), so the controllers need no changes.
 * Only an allow-list of harmless fields is recorded: never passwords, OTP codes or texts.
 */
final class Activity
{
    public const T = '#__supportplans_activity';

    /** Area of every task prefix (Client Management tasks are split by method below). */
    private const AREAS = [
        'plan' => 'Πακέτα', 'entry' => 'Εργασίες πακέτων', 'serviceplan' => 'Πλάνα',
        'offer' => 'Προσφορές', 'offerservice' => 'Προσφορές', 'offertemplate' => 'Προσφορές', 'offerfield' => 'Προσφορές',
        'settings' => 'Ρυθμίσεις', 'share' => 'Σύνδεσμοι πελάτη', 'auth' => 'Σύνδεση', 'otp' => 'Σύνδεση',
        'unsubscribe' => 'Client Management', 'activity' => 'Ιστορικό ενεργειών', 'structure' => 'Προτάσεις δομής', 'docmail' => 'Αποστολές εγγράφων', 'docgate' => 'Αποστολές εγγράφων',
    ];

    private const NOTIFY_AREAS = [
        'saveclient' => 'Πελάτες', 'deleteclient' => 'Πελάτες', 'unsubscribe' => 'Πελάτες',
        'importsheet' => 'Excel', 'template' => 'Excel', 'export' => 'Excel',
        'savecatalog' => 'Κατάλογος υπηρεσιών', 'deletecatalog' => 'Κατάλογος υπηρεσιών',
        'saveservice' => 'Υπηρεσίες πελατών', 'deleteservice' => 'Υπηρεσίες πελατών', 'renew' => 'Πληρωμές',
        'undorenewal' => 'Πληρωμές', 'renewgroup' => 'Πληρωμές', 'remindnow' => 'Υπενθυμίσεις',
        'savereport' => 'Αναφορές maintenance', 'deletereport' => 'Αναφορές maintenance',
        'savecampaign' => 'Αποστολές', 'deletecampaign' => 'Αποστολές',
        'retry' => 'Ουρά email', 'cancel' => 'Ουρά email', 'runnow' => 'Ουρά email',
        'savesettings' => 'Ρυθμίσεις email', 'sendtest' => 'Ρυθμίσεις email',
    ];

    /** Human label of every task (lower case "controller.method"). */
    private const LABELS = [
        'plan.add' => 'Νέο πακέτο', 'plan.apply' => 'Αποθήκευση πακέτου', 'plan.save' => 'Αποθήκευση πακέτου', 'plan.update' => 'Ενημέρωση πακέτου',
        'plan.delete' => 'Διαγραφή πακέτου', 'plan.cancel' => 'Κλείσιμο πακέτου',
        'entry.save' => 'Αποθήκευση εργασίας', 'entry.delete' => 'Διαγραφή εργασίας',
        'serviceplan.add' => 'Νέο πλάνο', 'serviceplan.apply' => 'Αποθήκευση πλάνου', 'serviceplan.save' => 'Αποθήκευση πλάνου',
        'serviceplan.delete' => 'Διαγραφή πλάνου', 'serviceplan.cancel' => 'Κλείσιμο πλάνου',
        'offer.create' => 'Νέα προσφορά', 'offer.apply' => 'Αποθήκευση προσφοράς', 'offer.save' => 'Αποθήκευση προσφοράς', 'offer.preview' => 'Προεπισκόπηση προσφοράς',
        'offer.duplicate' => 'Αντίγραφο προσφοράς', 'offer.delete' => 'Διαγραφή προσφοράς',
        'offerservice.apply' => 'Αποθήκευση υπηρεσίας προσφορών', 'offerservice.save' => 'Αποθήκευση υπηρεσίας προσφορών', 'offerservice.delete' => 'Διαγραφή υπηρεσίας προσφορών',
        'offertemplate.apply' => 'Αποθήκευση template', 'offertemplate.save' => 'Αποθήκευση template', 'offertemplate.duplicate' => 'Αντίγραφο template',
        'offertemplate.delete' => 'Διαγραφή template', 'offertemplate.sample' => 'Δημιουργία παραδείγματος template',
        'offerfield.save' => 'Αποθήκευση μεταβλητών προσφορών',
        'structure.create' => 'Νέα πρόταση δομής', 'structure.apply' => 'Αποθήκευση πρότασης δομής', 'structure.save' => 'Αποθήκευση πρότασης δομής',
        'structure.preview' => 'Προεπισκόπηση πρότασης δομής', 'structure.duplicate' => 'Αντίγραφο πρότασης δομής', 'structure.delete' => 'Διαγραφή πρότασης δομής',
        'settings.apply' => 'Αποθήκευση ρυθμίσεων', 'settings.save' => 'Αποθήκευση ρυθμίσεων', 'settings.cancel' => 'Κλείσιμο ρυθμίσεων',
        'share.create' => 'Δημιουργία συνδέσμου πελάτη', 'share.revoke' => 'Ακύρωση συνδέσμου πελάτη', 'share.send' => 'Αποστολή συνδέσμου πελάτη',
        'auth.authenticate' => 'Σύνδεση (κωδικός)', 'auth.logout' => 'Αποσύνδεση', 'otp.send' => 'Αποστολή κωδικού OTP', 'otp.verify' => 'Επιβεβαίωση OTP',
        'unsubscribe.run' => 'Διαγραφή από ενημερώσεις (σύνδεσμος email)',
        'docgate.send' => 'Κωδικός προβολής εγγράφου (πελάτης)', 'docgate.verify' => 'Επιβεβαίωση κωδικού εγγράφου (πελάτης)',
        'docmail.send' => 'Αποστολή εγγράφου με email', 'docmail.revoke' => 'Απενεργοποίηση συνδέσμου εγγράφου',
        'activity.export' => 'Εξαγωγή ιστορικού ενεργειών σε Excel',
        'notify.saveclient' => 'Αποθήκευση πελάτη', 'notify.deleteclient' => 'Διαγραφή πελάτη', 'notify.unsubscribe' => 'Αλλαγή προτίμησης ενημερώσεων',
        'notify.importsheet' => 'Εισαγωγή από Excel', 'notify.template' => 'Λήψη προτύπου Excel', 'notify.export' => 'Εξαγωγή πελατών σε Excel',
        'notify.savecatalog' => 'Αποθήκευση υπηρεσίας καταλόγου', 'notify.deletecatalog' => 'Διαγραφή υπηρεσίας καταλόγου',
        'notify.saveservice' => 'Αποθήκευση υπηρεσίας πελάτη', 'notify.deleteservice' => 'Διαγραφή υπηρεσίας πελάτη',
        'notify.renew' => 'Πληρώθηκε', 'notify.undorenewal' => 'Αναίρεση πληρωμής', 'notify.renewgroup' => 'Πληρώθηκαν όλες',
        'notify.remindnow' => 'Χειροκίνητη υπενθύμιση', 'notify.savereport' => 'Αναφορά maintenance', 'notify.deletereport' => 'Διαγραφή αναφοράς',
        'notify.savecampaign' => 'Αποστολή / πρόχειρο', 'notify.deletecampaign' => 'Διαγραφή πρόχειρης αποστολής',
        'notify.retry' => 'Επανάληψη μηνύματος', 'notify.cancel' => 'Ακύρωση μηνύματος', 'notify.runnow' => 'Έλεγχος λήξεων & αποστολή ουράς',
        'notify.savesettings' => 'Αποθήκευση ρυθμίσεων email', 'notify.sendtest' => 'Δοκιμαστικό email',
    ];

    /** Record id => [table, label SQL] to name the item of a task by its "id". */
    private const ITEMS = [
        'plan' => ['#__supportplans_plans', "CONCAT(customer_name, IF(plan_type<>'', CONCAT(' – ', plan_type), ''))"],
        'entry' => ['#__supportplans_entries', 'work_type'],
        'serviceplan' => ['#__supportplans_serviceplans', 'name'],
        'offer' => ['#__supportplans_offers', "CONCAT(offer_number, ' – ', customer_name)"],
        'offerservice' => ['#__supportplans_offer_services', 'name'],
        'offertemplate' => ['#__supportplans_offer_templates', 'name'],
        'structure' => ['#__supportplans_structures', "CONCAT(number, ' – ', customer_name)"],
        'share' => ['#__supportplans_plans', 'customer_name'],
        'notify.client' => ['#__supportplans_clients', "CONCAT(IF(name<>'', name, company), ' <', email, '>')"],
        'notify.catalog' => ['#__supportplans_catalog', 'name'],
        'notify.service' => ['#__supportplans_client_services', null],
        'notify.report' => ['#__supportplans_notify_reports', 'period'],
        'notify.campaign' => ['#__supportplans_notify_campaigns', 'subject'],
        'notify.message' => ['#__supportplans_notify_messages', "CONCAT(to_email, ' – ', subject)"],
    ];

    /** Input fields that may be recorded (as details); everything else is ignored. */
    private const SAFE_FIELDS = ['id', 'plan_id', 'client_id', 'catalog_id', 'service_plan_id', 'client_service_id', 'do', 'status', 'name', 'company',
        'email', 'username', 'label', 'customer_name', 'project_name', 'offer_number', 'number', 'plan_type', 'work_type', 'period', 'subject', 'expiry_date',
        'new_expiry', 'paid_on', 'amount', 'price', 'duration_months', 'value', 'template', 'aud_mode', 'to'];

    private static ?array $pending = null;
    private static string $note = '';

    /** Extra text for the current entry (e.g. "Νέα λήξη 10/12/2027"). */
    public static function note(string $text): void
    {
        self::$note = trim(self::$note . ' ' . $text);
    }

    public static function begin(string $task): void
    {
        $task = strtolower($task);
        if ($task === '' || strpos($task, '.') === false) {
            return;
        }
        try {
            [$controller, $method] = explode('.', $task, 2);
            $in = Factory::getApplication()->input;
            $area = $controller === 'notify' ? (self::NOTIFY_AREAS[$method] ?? 'Client Management') : (self::AREAS[$controller] ?? 'Άλλο');
            $details = [];
            foreach (self::SAFE_FIELDS as $f) {
                $v = $in->get($f, null, 'raw');
                if (is_scalar($v) && trim((string) $v) !== '') {
                    $details[$f] = mb_substr(trim(strip_tags((string) $v)), 0, 190);
                }
            }
            $ids = $in->get('ids', [], 'array');
            if ($ids) {
                $details['ids'] = implode(',', array_map('intval', (array) $ids));
            }
            self::$pending = [
                'task' => $task, 'area' => $area, 'action' => self::LABELS[$task] ?? $task,
                'item_id' => (int) ($in->getInt('id', 0) ?: $in->getInt('plan_id', 0) ?: $in->getInt('c', 0)),
                'item' => self::itemLabel($controller, $method, $in),
                'details' => $details,
            ];
        } catch (\Throwable $e) {
            self::$pending = null;
        }
    }

    /** Writes the entry of the current request; $error when the task threw. */
    public static function finish(?\Throwable $error = null): void
    {
        if (!self::$pending) {
            return;
        }
        $p = self::$pending;
        self::$pending = null;
        $result = 'ok';
        $message = '';
        try {
            $queue = Factory::getApplication()->getMessageQueue();
            foreach ($queue as $m) {
                $type = (string) ($m['type'] ?? 'message');
                if (in_array($type, ['error', 'danger'], true)) {
                    $result = 'error';
                } elseif ($type === 'warning' && $result !== 'error') {
                    $result = 'warning';
                }
                $message .= ($message !== '' ? ' | ' : '') . trim(html_entity_decode(strip_tags(str_replace('<br>', ' ', (string) ($m['message'] ?? ''))), ENT_QUOTES, 'UTF-8'));
            }
        } catch (\Throwable $e) {
        }
        if ($error) {
            $result = 'error';
            $message = trim($message . ' ' . $error->getMessage());
        }
        if (self::$note !== '') {
            $message = trim(self::$note . ($message !== '' ? ' | ' . $message : ''));
        }
        self::write($p['area'], $p['action'], $p['item_id'], $p['item'], $p['details'], $result, $message, $p['task']);
    }

    /** Direct entry (scheduled runs, unsubscribe link, …). */
    public static function write(string $area, string $action, int $itemId = 0, string $item = '', array $details = [], string $result = 'ok', string $message = '', string $task = ''): void
    {
        try {
            $app = Factory::getApplication();
            $session = method_exists($app, 'getSession') && !(method_exists($app, 'isClient') && $app->isClient('cli')) ? $app->getSession() : null;
            $userId = $session ? (int) $session->get('supportplans.auth.user_id', 0) : 0;
            $username = $session ? (string) ($session->get('supportplans.auth.username', '') ?: $session->get('supportplans.pending.username', '')) : '';
            if ($username === '' && isset($details['username'])) {
                $username = (string) $details['username'];
            }
            $ip = '';
            try {
                $ip = substr((string) $app->input->server->getString('REMOTE_ADDR', ''), 0, 45);
            } catch (\Throwable $e) {
            }
            $row = (object) [
                'created' => gmdate('Y-m-d H:i:s'), 'user_id' => $userId, 'username' => mb_substr($username !== '' ? $username : ($ip === '' ? 'Σύστημα' : '—'), 0, 150),
                'ip' => $ip, 'area' => mb_substr($area, 0, 60), 'action' => mb_substr($action, 0, 190), 'task' => mb_substr($task, 0, 60),
                'item_id' => $itemId, 'item' => mb_substr($item, 0, 255), 'result' => $result,
                'message' => mb_substr($message, 0, 1000), 'details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : '',
            ];
            Factory::getContainer()->get('DatabaseDriver')->insertObject(self::T, $row);
        } catch (\Throwable $e) {
            // Logging must never break the action itself.
        }
    }

    private static function itemLabel(string $controller, string $method, $in): string
    {
        $id = (int) $in->getInt('id', 0);
        $key = $controller;
        if ($controller === 'notify') {
            if (preg_match('/client|unsubscribe/', $method)) $key = 'notify.client';
            elseif (strpos($method, 'catalog') !== false) $key = 'notify.catalog';
            elseif ($method === 'remindnow' && !$id) { $id = (int) $in->getInt('client_id', 0); $key = 'notify.client'; }
            elseif (in_array($method, ['saveservice', 'deleteservice', 'renew', 'remindnow'], true)) $key = 'notify.service';
            elseif (strpos($method, 'report') !== false) $key = 'notify.report';
            elseif (strpos($method, 'campaign') !== false) $key = 'notify.campaign';
            elseif (in_array($method, ['retry', 'cancel'], true)) $key = 'notify.message';
            elseif ($method === 'undorenewal') {
                $r = $id ? Factory::getContainer()->get('DatabaseDriver')->setQuery('SELECT service_id FROM ' . Factory::getContainer()->get('DatabaseDriver')->quoteName('#__supportplans_renewals') . ' WHERE id=' . $id)->loadResult() : 0;
                $id = (int) $r;
                $key = 'notify.service';
            } elseif ($method === 'renewgroup') {
                $id = (int) $in->getInt('client_id', 0);
                $key = 'notify.client';
            }
        } elseif ($controller === 'entry' && !$id) {
            return '';
        } elseif ($controller === 'unsubscribe') {
            $id = (int) $in->getInt('c', 0);
            $key = 'notify.client';
        }
        if (!$id && $key === 'notify.client') {
            $id = (int) $in->getInt('client_id', 0);
        }
        if (!$id || !isset(self::ITEMS[$key])) {
            foreach (['customer_name', 'name', 'subject', 'email', 'label'] as $f) {
                $v = trim((string) $in->getString($f, ''));
                if ($v !== '') return mb_substr($v, 0, 190);
            }
            return '';
        }
        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            [$table, $expr] = self::ITEMS[$key];
            if ($key === 'notify.service') {
                $s = Notify::service($id);
                return $s ? Notify::serviceFull($s) . ' · ' . Notify::clientLabel($s, 'client_') : '#' . $id;
            }
            $v = $db->setQuery('SELECT ' . $expr . ' FROM ' . $db->quoteName($table) . ' WHERE id=' . $id)->loadResult();
            return $v !== null ? mb_substr((string) $v, 0, 255) : '#' . $id;
        } catch (\Throwable $e) {
            return '#' . $id;
        }
    }

    /* ---------------------------------------------------------------- reading */

    public static function areas(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        try {
            return $db->setQuery('SELECT DISTINCT area FROM ' . $db->quoteName(self::T) . ' ORDER BY area')->loadColumn();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function users(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        try {
            return $db->setQuery('SELECT DISTINCT username FROM ' . $db->quoteName(self::T) . " WHERE username<>'' ORDER BY username")->loadColumn();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Filters: from, to (Y-m-d, site time zone), area, user, result, q. Returns [rows, total]. */
    public static function search(array $f, int $page = 1, int $perPage = 50, bool $all = false): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->from($db->quoteName(self::T));
        $tz = Notify::tz();
        foreach (['from' => '>=', 'to' => '<'] as $k => $op) {
            $d = Notify::parseDate($f[$k] ?? '');
            if ($d !== '') {
                $local = new \DateTimeImmutable($d . ' 00:00:00', $tz);
                if ($k === 'to') $local = $local->modify('+1 day');
                $q->where('created ' . $op . ' ' . $db->quote($local->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')));
            }
        }
        if (!empty($f['area'])) $q->where('area=' . $db->quote($f['area']));
        if (!empty($f['user'])) $q->where('username=' . $db->quote($f['user']));
        if (!empty($f['result']) && in_array($f['result'], ['ok', 'warning', 'error'], true)) $q->where('result=' . $db->quote($f['result']));
        if (!empty($f['q'])) {
            $like = $db->quote('%' . $db->escape($f['q'], true) . '%', false);
            $q->where("(action LIKE $like OR item LIKE $like OR message LIKE $like OR details LIKE $like OR ip LIKE $like)");
        }
        try {
            $total = (int) $db->setQuery((clone $q)->select('COUNT(*)'))->loadResult();
            $q->select('*')->order('id DESC');
            $rows = $all ? $db->setQuery($q, 0, 20000)->loadObjectList() : $db->setQuery($q, ($page - 1) * $perPage, $perPage)->loadObjectList();
            return [$rows, $total];
        } catch (\Throwable $e) {
            return [[], 0];
        }
    }

    public const RESULTS = ['ok' => 'Επιτυχία', 'warning' => 'Προειδοποίηση', 'error' => 'Σφάλμα'];
}

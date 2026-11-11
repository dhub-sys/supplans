<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Uri\Uri;

/**
 * Clients, service catalogue, client services, renewal reminders, maintenance reports,
 * campaigns and the e-mail queue ("Πελάτες & Ειδοποιήσεις").
 *
 * Every e-mail is first written to #__supportplans_notify_messages (the queue and the log),
 * then sent by the queue: from the Joomla Scheduled Task "Back Office – Ειδοποιήσεις"
 * or immediately for manual actions. Each message is claimed with an atomic UPDATE before
 * it is sent, so two overlapping runs never send the same message twice.
 */
final class Notify
{
    public const T_CLIENTS = '#__supportplans_clients';
    public const T_CATALOG = '#__supportplans_catalog';
    public const T_SERVICES = '#__supportplans_client_services';
    public const T_REPORTS = '#__supportplans_notify_reports';
    public const T_CAMPAIGNS = '#__supportplans_notify_campaigns';
    public const T_MESSAGES = '#__supportplans_notify_messages';
    /** One row per (client service, expiry date, reminder day) already covered by a reminder. */
    public const T_SENT = '#__supportplans_notify_sent';
    /** Payments / renewals recorded with "Πληρώθηκε" (history per client service). */
    public const T_RENEWALS = '#__supportplans_renewals';

    public const MAX_ATTEMPTS = 3;
    public const DEFAULT_REMINDER_DAYS = '30,14,7,1,0,-3';

    public const SERVICE_STATUSES = ['active' => 'Ενεργή', 'suspended' => 'Σε αναστολή', 'cancelled' => 'Ακυρωμένη'];
    public const CLIENT_STATUSES = ['active' => 'Ενεργός', 'inactive' => 'Ανενεργός'];
    public const KINDS = ['renewal' => 'Υπενθύμιση ανανέωσης', 'report' => 'Αναφορά maintenance', 'campaign' => 'Ενημέρωση', 'test' => 'Δοκιμαστικό'];
    public const MESSAGE_STATUSES = ['queued' => 'Σε αναμονή', 'sending' => 'Αποστέλλεται', 'sent' => 'Εστάλη', 'failed' => 'Απέτυχε', 'cancelled' => 'Ακυρώθηκε'];

    /* ================================================================ settings */

    public static function defaults(): array
    {
        return [
            'notify_from_name'       => '',
            'notify_from_email'      => '',
            'notify_reply_to'        => '',
            'notify_bcc'             => '',
            // Sender per document type (offer / structure / plan); empty = the sender above.
            'doc_offer_from_name' => '', 'doc_offer_from_email' => '', 'doc_offer_reply_to' => '', 'doc_offer_bcc' => '',
            'doc_structure_from_name' => '', 'doc_structure_from_email' => '', 'doc_structure_reply_to' => '', 'doc_structure_bcc' => '',
            'doc_plan_from_name' => '', 'doc_plan_from_email' => '', 'doc_plan_reply_to' => '', 'doc_plan_bcc' => '',
            'notify_reminder_days'   => self::DEFAULT_REMINDER_DAYS,
            'notify_batch_size'      => 20,
            'notify_footer'          => '',
            'notify_vat_rate'        => 24,
            'notify_send_time'       => '09:30',
            'notify_font'            => 'geom',
            'notify_font_outlook'    => 'aptos',
            'notify_group_days'      => 7,
            'notify_renewal_subject' => 'Υπενθύμιση ανανέωσης: {services}',
            'notify_subject_today'   => 'Λήγει σήμερα: {services}',
            'notify_subject_after'   => 'Έληξε: {services} – απαιτείται ανανέωση',
            'notify_intro_single'    => 'Θα θέλαμε να σας ενημερώσουμε ότι η υπηρεσία **{service_full}** {phrase}.',
            'notify_intro_multi'     => 'Θα θέλαμε να σας ενημερώσουμε για τη λήξη των παρακάτω υπηρεσιών σας:',
            'notify_phrase_before'   => 'λήγει στις **{date}**',
            'notify_phrase_today'    => 'λήγει **σήμερα, {date}**',
            'notify_phrase_after'    => 'έληξε στις **{date}**',
            'notify_expired_text'    => 'Η υπηρεσία **{service_full}** έχει λήξει. Αν δεν ανανεωθεί άμεσα, θα διακοπεί και η διακοπή θα επηρεάσει τη λειτουργία του project σας.',
            'notify_renewal_body'    => "Αγαπητέ/ή {client_name},\n\n{services_status}\n\n{expired_notice}\n\n{price_table}\n\n{banks}\n\nΓια να προχωρήσουμε στην ανανέωση, απαντήστε σε αυτό το email ή επικοινωνήστε μαζί μας.\n\nΜε εκτίμηση,\n{company}",
            'notify_report_subject'  => 'Αναφορά συντήρησης {label} – {period}',
            'notify_report_body'     => "Αγαπητέ/ή {client_name},\n\nΣας αποστέλλουμε την αναφορά συντήρησης για το **{label}** για την περίοδο **{period}**.\n\n{summary}\n\n{updates}\n\nΓια οποιαδήποτε απορία είμαστε στη διάθεσή σας.\n\nΜε εκτίμηση,\n{company}",
        ];
    }

    public static function setting(string $key)
    {
        $value = ComponentHelper::getParams('com_supportplans')->get($key, null);
        $defaults = self::defaults();
        if (($value === null || $value === '') && isset($defaults[$key])) {
            return $defaults[$key];
        }
        return $value;
    }

    /** Offsets in days before expiry (negative = after), unique, descending. */
    public static function parseDays(?string $raw): array
    {
        $days = [];
        foreach (preg_split('/[\s,;]+/', (string) $raw) as $part) {
            if (preg_match('/^-?\d+$/', $part) && (int) $part <= 365 && (int) $part >= -90) {
                $days[] = (int) $part;
            }
        }
        $days = array_values(array_unique($days));
        rsort($days);
        return $days;
    }

    /** Effective reminder days of a client service: its own, else the catalogue's, else the global setting. */
    public static function reminderDays(object $service): array
    {
        foreach ([$service->reminder_days ?? '', $service->catalog_reminder_days ?? '', (string) self::setting('notify_reminder_days')] as $raw) {
            if (trim((string) $raw) !== '') {
                return self::parseDays($raw);
            }
        }
        return [];
    }

    /* ================================================================ dates & formatting */

    public static function tz(): \DateTimeZone
    {
        try {
            return new \DateTimeZone((string) Factory::getApplication()->get('offset', 'UTC') ?: 'UTC');
        } catch (\Throwable $e) {
            return new \DateTimeZone('UTC');
        }
    }

    public static function today(): string
    {
        return (new \DateTimeImmutable('now', self::tz()))->format('Y-m-d');
    }

    /** UTC timestamp for the database (Joomla convention). */
    public static function now(int $plusSeconds = 0): string
    {
        return gmdate('Y-m-d H:i:s', time() + $plusSeconds);
    }

    public static function daysUntil(?string $ymd): ?int
    {
        $b = self::date($ymd);
        if (!$b) {
            return null;
        }
        $diff = (new \DateTimeImmutable(self::today(), self::tz()))->diff($b);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    public static function date(?string $ymd): ?\DateTimeImmutable
    {
        if (!$ymd || strpos($ymd, '0000') === 0) {
            return null;
        }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', substr($ymd, 0, 10), self::tz());
        return $d ?: null;
    }

    public static function addDays(string $ymd, int $days): string
    {
        $d = self::date($ymd);
        return $d ? $d->modify(($days >= 0 ? '+' : '') . $days . ' days')->format('Y-m-d') : $ymd;
    }

    public static function addMonths(string $ymd, int $months): string
    {
        $d = self::date($ymd);
        if (!$d) {
            return $ymd;
        }
        // 31/01 + 1 month = 28/02 (not 03/03).
        $target = $d->modify('first day of +' . $months . ' months');
        $day = min((int) $d->format('j'), (int) $target->format('t'));
        return $target->setDate((int) $target->format('Y'), (int) $target->format('n'), $day)->format('Y-m-d');
    }

    /** Accepts Y-m-d, d/m/Y, d-m-Y, d.m.Y; returns Y-m-d or ''. */
    public static function parseDate($value): string
    {
        $value = trim((string) $value);
        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!d.m.Y'] as $fmt) {
            $d = \DateTimeImmutable::createFromFormat($fmt, $value);
            $err = \DateTimeImmutable::getLastErrors();
            if ($d && (!$err || (!$err['warning_count'] && !$err['error_count']))) {
                return $d->format('Y-m-d');
            }
        }
        return '';
    }

    public static function dateGr(?string $ymd): string
    {
        $d = self::date($ymd);
        return $d ? $d->format('d/m/Y') : '—';
    }

    /** UTC database datetime shown in the site timezone. */
    public static function datetimeGr(?string $utc): string
    {
        if (!$utc || strpos($utc, '0000') === 0) {
            return '—';
        }
        try {
            return (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(self::tz())->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return '—';
        }
    }

    public static function money($value): string
    {
        return $value === null || $value === '' ? '' : number_format((float) $value, 2, ',', '.') . ' €';
    }

    public static function durationLabel(int $months): string
    {
        if ($months === 12) return '1 έτος';
        if ($months > 12 && $months % 12 === 0) return ($months / 12) . ' έτη';
        return $months === 1 ? '1 μήνας' : $months . ' μήνες';
    }

    public static function expiryPhrase(int $days): string
    {
        if ($days > 1) return 'λήγει σε ' . $days . ' ημέρες';
        if ($days === 1) return 'λήγει αύριο';
        if ($days === 0) return 'λήγει σήμερα';
        if ($days === -1) return 'έληξε χθες';
        return 'έληξε πριν από ' . abs($days) . ' ημέρες';
    }

    public static function clientLabel(object $c, string $prefix = ''): string
    {
        $name = trim((string) ($c->{$prefix . 'name'} ?? ''));
        $company = trim((string) ($c->{$prefix . 'company'} ?? ''));
        if ($name !== '' && $company !== '') return $name . ' (' . $company . ')';
        return $name !== '' ? $name : ($company !== '' ? $company : (string) ($c->{$prefix . 'email'} ?? ''));
    }

    /* ================================================================ data access */

    private static function db()
    {
        return Factory::getContainer()->get('DatabaseDriver');
    }

    public static function client(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_CLIENTS))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function clientByEmail(string $email): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_CLIENTS))
            ->where($db->quoteName('email') . '=' . $db->quote($email)))->loadObject() ?: null;
    }

    public static function clients(string $search = '', string $status = ''): array
    {
        $db = self::db();
        $q = $db->getQuery(true)
            ->select('c.*, (SELECT COUNT(*) FROM ' . $db->quoteName(self::T_SERVICES) . " s WHERE s.client_id=c.id AND s.status='active') AS active_services")
            ->from($db->quoteName(self::T_CLIENTS, 'c'))->order('c.name ASC, c.company ASC');
        if ($search !== '') {
            $like = $db->quote('%' . $db->escape($search, true) . '%', false);
            $q->where('(c.name LIKE ' . $like . ' OR c.company LIKE ' . $like . ' OR c.email LIKE ' . $like . ')');
        }
        if (isset(self::CLIENT_STATUSES[$status])) {
            $q->where('c.status=' . $db->quote($status));
        }
        return $db->setQuery($q)->loadObjectList();
    }

    public static function catalog(bool $publishedOnly = false): array
    {
        $db = self::db();
        $q = $db->getQuery(true)
            ->select('k.*, (SELECT COUNT(*) FROM ' . $db->quoteName(self::T_SERVICES) . " s WHERE s.catalog_id=k.id AND s.status='active') AS active_count")
            ->from($db->quoteName(self::T_CATALOG, 'k'))->order('k.ordering ASC, k.name ASC');
        if ($publishedOnly) {
            $q->where('k.published=1');
        }
        return $db->setQuery($q)->loadObjectList();
    }

    public static function catalogItem(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_CATALOG))->where('id=' . $id))->loadObject() ?: null;
    }

    private static function servicesQuery()
    {
        $db = self::db();
        return $db->getQuery(true)
            ->select('s.*, k.name AS service_name, k.reminder_days AS catalog_reminder_days, k.expired_text AS catalog_expired_text, c.name AS client_name, c.company AS client_company, c.email AS client_email, c.status AS client_status')
            // Last "Πληρώθηκε" of the service, so the lists can show that the expiry was moved by a payment.
            ->select('(SELECT r.paid_on FROM ' . $db->quoteName(self::T_RENEWALS) . ' r WHERE r.service_id=s.id ORDER BY r.id DESC LIMIT 1) AS last_paid_on')
            ->select('(SELECT r.old_expiry FROM ' . $db->quoteName(self::T_RENEWALS) . ' r WHERE r.service_id=s.id ORDER BY r.id DESC LIMIT 1) AS last_old_expiry')
            ->select('(SELECT r.new_expiry FROM ' . $db->quoteName(self::T_RENEWALS) . ' r WHERE r.service_id=s.id ORDER BY r.id DESC LIMIT 1) AS last_new_expiry')
            ->from($db->quoteName(self::T_SERVICES, 's'))
            ->join('LEFT', $db->quoteName(self::T_CATALOG, 'k') . ' ON k.id=s.catalog_id')
            ->join('INNER', $db->quoteName(self::T_CLIENTS, 'c') . ' ON c.id=s.client_id');
    }

    public static function service(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery(self::servicesQuery()->where('s.id=' . $id))->loadObject() ?: null;
    }

    /** Filters: client_id, catalog_id, status, expiring (days), search. */
    public static function services(array $f = []): array
    {
        $db = self::db();
        $q = self::servicesQuery()->order('s.expiry_date IS NULL, s.expiry_date ASC, c.name ASC');
        if (!empty($f['client_id'])) $q->where('s.client_id=' . (int) $f['client_id']);
        if (!empty($f['catalog_id'])) $q->where('s.catalog_id=' . (int) $f['catalog_id']);
        if (!empty($f['status']) && isset(self::SERVICE_STATUSES[$f['status']])) $q->where('s.status=' . $db->quote($f['status']));
        if (isset($f['expiring']) && $f['expiring'] !== '') {
            $q->where('s.expiry_date IS NOT NULL AND s.expiry_date<=' . $db->quote(self::addDays(self::today(), (int) $f['expiring'])));
        }
        if (!empty($f['search'])) {
            $like = $db->quote('%' . $db->escape($f['search'], true) . '%', false);
            $q->where('(s.label LIKE ' . $like . ' OR k.name LIKE ' . $like . ' OR c.name LIKE ' . $like . ' OR c.company LIKE ' . $like . ' OR c.email LIKE ' . $like . ')');
        }
        return $db->setQuery($q)->loadObjectList();
    }

    public static function report(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_REPORTS))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function reports(): array
    {
        $db = self::db();
        $q = $db->getQuery(true)
            ->select('r.*, s.label AS service_label, k.name AS service_name, c.name AS client_name, c.company AS client_company, c.email AS client_email')
            ->from($db->quoteName(self::T_REPORTS, 'r'))
            ->join('INNER', $db->quoteName(self::T_SERVICES, 's') . ' ON s.id=r.client_service_id')
            ->join('LEFT', $db->quoteName(self::T_CATALOG, 'k') . ' ON k.id=s.catalog_id')
            ->join('INNER', $db->quoteName(self::T_CLIENTS, 'c') . ' ON c.id=s.client_id')
            ->order('r.created DESC');
        return $db->setQuery($q, 0, 500)->loadObjectList();
    }

    public static function campaign(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_CAMPAIGNS))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function campaigns(): array
    {
        $db = self::db();
        $m = $db->quoteName(self::T_MESSAGES);
        $q = $db->getQuery(true)
            ->select("k.*, (SELECT COUNT(*) FROM $m WHERE campaign_id=k.id AND status='sent') AS sent_count, (SELECT COUNT(*) FROM $m WHERE campaign_id=k.id AND status='failed') AS failed_count")
            ->from($db->quoteName(self::T_CAMPAIGNS, 'k'))->order('k.created DESC');
        return $db->setQuery($q, 0, 500)->loadObjectList();
    }

    /** Client ids for a campaign audience: active and not unsubscribed. */
    public static function audience(array $aud): array
    {
        $db = self::db();
        $q = $db->getQuery(true)->select('c.id')->from($db->quoteName(self::T_CLIENTS, 'c'))
            ->where("c.status='active'")->where('c.unsubscribed_at IS NULL');
        $mode = $aud['mode'] ?? 'all';
        if ($mode === 'catalog') {
            $ids = array_values(array_filter(array_map('intval', (array) ($aud['catalog'] ?? []))));
            if (!$ids) return [];
            $q->where('EXISTS (SELECT 1 FROM ' . $db->quoteName(self::T_SERVICES) . " s WHERE s.client_id=c.id AND s.status='active' AND s.catalog_id IN (" . implode(',', $ids) . '))');
        } elseif ($mode === 'clients') {
            $ids = array_values(array_filter(array_map('intval', (array) ($aud['clients'] ?? []))));
            if (!$ids) return [];
            $q->where('c.id IN (' . implode(',', $ids) . ')');
        }
        return array_map('intval', $db->setQuery($q)->loadColumn());
    }

    public static function message(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_MESSAGES))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function messageByRef(string $ref): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_MESSAGES))->where($db->quoteName('ref') . '=' . $db->quote($ref)))->loadObject() ?: null;
    }

    public static function messages(array $f, int $page = 1, int $perPage = 50): array
    {
        $db = self::db();
        $q = $db->getQuery(true)->from($db->quoteName(self::T_MESSAGES, 'm'))
            ->join('LEFT', $db->quoteName(self::T_CLIENTS, 'c') . ' ON c.id=m.client_id');
        if (!empty($f['kind']) && isset(self::KINDS[$f['kind']])) $q->where('m.kind=' . $db->quote($f['kind']));
        if (!empty($f['status']) && isset(self::MESSAGE_STATUSES[$f['status']])) $q->where('m.status=' . $db->quote($f['status']));
        if (!empty($f['client_id'])) $q->where('m.client_id=' . (int) $f['client_id']);
        if (!empty($f['campaign_id'])) $q->where('m.campaign_id=' . (int) $f['campaign_id']);
        $count = clone $q;
        $total = (int) $db->setQuery($count->select('COUNT(*)'))->loadResult();
        $q->select('m.id, m.client_id, m.kind, m.to_email, m.subject, m.status, m.attempts, m.last_error, m.created, m.scheduled, m.sent, c.name AS client_name')
            ->order('m.id DESC');
        $rows = $db->setQuery($q, ($page - 1) * $perPage, $perPage)->loadObjectList();
        return [$rows, $total];
    }

    public static function messageCounts(): array
    {
        $db = self::db();
        $out = array_fill_keys(array_keys(self::MESSAGE_STATUSES), 0);
        $q = $db->getQuery(true)->select('status, COUNT(*) AS n')->from($db->quoteName(self::T_MESSAGES))->group('status');
        foreach ($db->setQuery($q)->loadObjectList() as $r) {
            $out[$r->status] = (int) $r->n;
        }
        return $out;
    }

    /** Deletes a client with services, reports and messages (also used by privacy erasure). */
    public static function deleteClient(int $id): void
    {
        $db = self::db();
        $sids = $db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName(self::T_SERVICES))->where('client_id=' . $id))->loadColumn();
        if ($sids) {
            $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_REPORTS))->where('client_service_id IN (' . implode(',', array_map('intval', $sids)) . ')'))->execute();
        }
        foreach ([self::T_SERVICES, self::T_MESSAGES, self::T_RENEWALS] as $t) {
            $db->setQuery($db->getQuery(true)->delete($db->quoteName($t))->where('client_id=' . $id))->execute();
        }
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_CLIENTS))->where('id=' . $id))->execute();
    }

    /** Expiry after one more period (from the current expiry, or today when there is none). */
    public static function nextExpiry(object $s): string
    {
        return self::addMonths($s->expiry_date ?: self::today(), max(1, (int) $s->duration_months));
    }

    /**
     * "Πληρώθηκε": records the payment, moves the expiry (by default one period after the
     * current expiry, also when it was paid late), reactivates the service and cancels every
     * queued reminder for it. No further reminders go out until the new expiry approaches.
     */
    public static function renew(int $id, ?string $newExpiry = null, ?string $paidOn = null, ?float $amount = null, string $note = ''): ?string
    {
        $s = self::service($id);
        if (!$s) {
            return null;
        }
        $db = self::db();
        $new = $newExpiry ?: self::nextExpiry($s);
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_SERVICES))
            ->set(['expiry_date=' . $db->quote($new), "status='active'", 'modified=' . $db->quote(self::now())])->where('id=' . $id))->execute();
        $row = (object) [
            'service_id' => $id, 'client_id' => (int) $s->client_id, 'old_expiry' => $s->expiry_date ?: null, 'old_status' => (string) $s->status, 'new_expiry' => $new,
            'paid_on' => $paidOn ?: self::today(), 'amount' => $amount, 'note' => mb_substr($note, 0, 1000), 'created' => self::now(),
            'created_by' => (int) Factory::getApplication()->getSession()->get('supportplans.auth.user_id', 0),
        ];
        try {
            $db->insertObject(self::T_RENEWALS, $row);
        } catch (\Throwable $e) {
            // History table missing before the update finished: the renewal itself still counts.
        }
        self::cancelQueuedRenewals($id, 'Ο πελάτης πλήρωσε / η υπηρεσία ανανεώθηκε.');
        return $new;
    }

    /**
     * Undoes a payment ("Πληρώθηκε" by mistake): the service gets back its previous expiry and
     * status and the entry leaves the history. Only the latest payment of a service, and only while
     * its expiry is still the one that payment set. Reminders already sent for the old expiry are
     * not sent again; the next run queues the one that is due, if any.
     * Returns '' on success, else the reason.
     */
    public static function undoRenewal(int $renewalId): string
    {
        $db = self::db();
        $r = $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_RENEWALS))->where('id=' . $renewalId))->loadObject();
        if (!$r) {
            return 'Η πληρωμή δεν βρέθηκε.';
        }
        $latest = (int) $db->setQuery($db->getQuery(true)->select('MAX(id)')->from($db->quoteName(self::T_RENEWALS))->where('service_id=' . (int) $r->service_id))->loadResult();
        if ($latest !== (int) $r->id) {
            return 'Αναιρείται μόνο η τελευταία πληρωμή της υπηρεσίας.';
        }
        $s = self::service((int) $r->service_id);
        if (!$s) {
            return 'Η υπηρεσία δεν υπάρχει πλέον.';
        }
        if ((string) $s->expiry_date !== (string) $r->new_expiry) {
            return 'Η λήξη της υπηρεσίας άλλαξε μετά την πληρωμή (' . self::dateGr($s->expiry_date) . '): διορθώστε την από τη φόρμα της υπηρεσίας.';
        }
        $status = isset(self::SERVICE_STATUSES[$r->old_status ?? '']) ? $r->old_status : 'active';
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_SERVICES))
            ->set(['expiry_date=' . ($r->old_expiry ? $db->quote($r->old_expiry) : 'NULL'), 'status=' . $db->quote($status), 'modified=' . $db->quote(self::now())])
            ->where('id=' . (int) $r->service_id))->execute();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_RENEWALS))->where('id=' . (int) $r->id))->execute();
        // Anything queued meanwhile for the new expiry is stale now.
        self::cancelQueuedRenewals((int) $r->service_id, 'Η πληρωμή αναιρέθηκε.');
        return '';
    }

    /** Recent payments of a client's services (newest first), with the service name. */
    public static function clientRenewals(int $clientId, int $limit = 15): array
    {
        $db = self::db();
        try {
            $q = $db->getQuery(true)->select('r.*, s.label, k.name AS service_name, s.expiry_date AS current_expiry')
                ->select('(SELECT MAX(r2.id) FROM ' . $db->quoteName(self::T_RENEWALS) . ' r2 WHERE r2.service_id=r.service_id) AS latest_id')
                ->from($db->quoteName(self::T_RENEWALS, 'r'))
                ->join('INNER', $db->quoteName(self::T_SERVICES, 's') . ' ON s.id=r.service_id')
                ->join('LEFT', $db->quoteName(self::T_CATALOG, 'k') . ' ON k.id=s.catalog_id')
                ->where('r.client_id=' . $clientId)->order('r.id DESC');
            return $db->setQuery($q, 0, $limit)->loadObjectList();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** True when a payment can still be undone (latest of its service, expiry unchanged). */
    public static function canUndo(object $r, ?string $currentExpiry): bool
    {
        return isset($r->latest_id) ? (int) $r->latest_id === (int) $r->id && (string) $currentExpiry === (string) $r->new_expiry : false;
    }

    public static function renewals(int $serviceId): array
    {
        $db = self::db();
        try {
            $rows = $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_RENEWALS))->where('service_id=' . $serviceId)->order('id DESC'))->loadObjectList();
            foreach ($rows as $row) {
                $row->latest_id = (int) $rows[0]->id;
            }
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Cancels queued renewal reminders that include a client service (also combined ones) and
     * frees their markers, so the next run rebuilds the reminder for the remaining services.
     */
    public static function cancelQueuedRenewals(int $serviceId, string $reason): void
    {
        $db = self::db();
        $ids = $db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName(self::T_MESSAGES))
            ->where("kind='renewal'")->where("status='queued'")
            ->where('(service_id=' . $serviceId . ' OR CONCAT(' . $db->quote(',') . ', service_ids) LIKE ' . $db->quote('%,' . $serviceId . ':%') . ')'))->loadColumn();
        foreach ($ids as $mid) {
            self::cancelRenewal((int) $mid, $reason);
        }
    }

    private static function cancelRenewal(int $messageId, string $reason): void
    {
        $db = self::db();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_MESSAGES))
            ->set(["status='cancelled'", 'last_error=' . $db->quote($reason)])->where('id=' . $messageId)->where("status IN ('queued','sending')"))->execute();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_SENT))->where('message_id=' . $messageId))->execute();
    }

    /* ================================================================ unsubscribe */

    public static function unsubscribeToken(object $client): string
    {
        $secret = (string) Factory::getApplication()->get('secret', '');
        return substr(hash_hmac('sha256', 'supportplans-unsub|' . (int) $client->id . '|' . strtolower((string) $client->email), $secret), 0, 40);
    }

    public static function verifyUnsubscribe(?object $client, string $token): bool
    {
        return $client && preg_match('/^[a-f0-9]{40}$/', $token) && hash_equals(self::unsubscribeToken($client), $token);
    }

    /**
     * Public site URL. A cron run from the command line does not know the domain, so the
     * last URL seen by the Service Hub is remembered (notify_site_root) and used there.
     */
    public static function siteRoot(): string
    {
        $app = Factory::getApplication();
        $cli = method_exists($app, 'isClient') && $app->isClient('cli');
        $saved = (string) ComponentHelper::getParams('com_supportplans')->get('notify_site_root', '');
        if ($cli && $saved !== '') {
            return rtrim($saved, '/') . '/';
        }
        try {
            return rtrim(Uri::root(), '/') . '/';
        } catch (\Throwable $e) {
            return $saved !== '' ? rtrim($saved, '/') . '/' : '/';
        }
    }

    public static function rememberSiteRoot(): void
    {
        $root = rtrim(Uri::root(), '/') . '/';
        if ((string) ComponentHelper::getParams('com_supportplans')->get('notify_site_root', '') !== $root) {
            try {
                OfferHelper::saveParam('notify_site_root', $root);
            } catch (\Throwable $e) {
            }
        }
    }

    public static function unsubscribeUrl(object $client): string
    {
        return rtrim(self::siteRoot(), '/') . '/index.php?option=com_supportplans&task=unsubscribe.run&tmpl=component&c=' . (int) $client->id . '&t=' . self::unsubscribeToken($client);
    }

    /* ================================================================ rendering */

    private static function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }

    public static function brandName(): string
    {
        $params = ComponentHelper::getParams('com_supportplans');
        return trim((string) $params->get('brand_name', '')) ?: (string) Factory::getApplication()->get('sitename', '');
    }

    public static function clientVars(object $client): array
    {
        return [
            'client_name'  => trim((string) $client->name) ?: (trim((string) ($client->company ?? '')) ?: (string) $client->email),
            'client_company' => (string) ($client->company ?? ''),
            'client_email' => (string) $client->email,
            'company'      => self::brandName(),
        ];
    }

    public static function serviceVars(object $s): array
    {
        $days = self::daysUntil($s->expiry_date ?? null);
        $price = self::money($s->price ?? null);
        $duration = self::durationLabel(max(1, (int) ($s->duration_months ?? 12)));
        return [
            'service'       => (string) ($s->service_name ?? ''),
            'label'         => (string) ($s->label ?? ''),
            'expiry_date'   => self::dateGr($s->expiry_date ?? null),
            'days_left'     => $days === null ? '' : (string) $days,
            'expiry_phrase' => $days === null ? '' : self::expiryPhrase($days),
            'price'         => $price,
            'price_vat'     => $price !== '' ? self::money(self::withVat((float) $s->price)) : '',
            'vat_rate'      => self::vatLabel(),
            'duration'      => $duration,
            'price_line'    => $price !== '' ? 'Κόστος ανανέωσης: ' . $price . ' + ΦΠΑ ' . self::vatLabel() . ' (σύνολο ' . self::money(self::withVat((float) $s->price)) . ') / ' . $duration . '.' : '',
            'service_full'  => self::serviceFull($s),
        ];
    }

    public static function vatRate(): float
    {
        return max(0.0, min(100.0, (float) str_replace(',', '.', (string) self::setting('notify_vat_rate'))));
    }

    public static function vatLabel(): string
    {
        return rtrim(rtrim(number_format(self::vatRate(), 2, ',', ''), '0'), ',') . '%';
    }

    public static function withVat(float $net): float
    {
        return round($net * (1 + self::vatRate() / 100), 2);
    }

    public static function serviceFull(object $s): string
    {
        $name = trim((string) ($s->service_name ?? '')) ?: 'Υπηρεσία';
        $label = trim((string) ($s->label ?? ''));
        return $label !== '' ? $name . ' (' . $label . ')' : $name;
    }

    /**
     * Light markup (same as the offer texts) to HTML with {variables}. Placeholders in $blocks
     * are replaced afterwards by HTML the plugin builds itself (already escaped).
     */
    public static function renderText(string $text, array $vars, array $blocks = []): string
    {
        $html = OfferHelper::render($text, $vars);
        foreach ($blocks as $key => $block) {
            $ph = '{' . $key . '}';
            // A block (table, list, notice) must never end up inside a paragraph: when the text
            // continues on the next line ("{price_table}\nΓια την εξόφληση…"), the paragraph is split
            // around it. Browsers repair <p><table>, but Outlook for Windows gives every table
            // cell the paragraph's spacing, which made the rows tall.
            $html = preg_replace_callback('#<p([^>]*)>(.*?)</p>#s', static function ($m) use ($ph, $block) {
                if (strpos($m[2], $ph) === false) {
                    return $m[0];
                }
                $parts = explode($ph, $m[2]);
                $out = '';
                foreach ($parts as $i => $part) {
                    $part = preg_replace(['#^(\s*<br\s*/?>)+#i', '#(<br\s*/?>\s*)+$#i'], '', trim($part));
                    if (trim(strip_tags($part)) !== '') {
                        $out .= '<p' . $m[1] . '>' . $part . '</p>';
                    }
                    if ($i < count($parts) - 1) {
                        $out .= $block;
                    }
                }
                return $out;
            }, $html);
            $html = str_replace($ph, $block, $html);
        }
        // Empty paragraphs left by empty variables (e.g. no price).
        return preg_replace('#<p[^>]*>\s*</p>#', '', $html);
    }

    public static function subject(string $template, array $vars): string
    {
        $s = OfferHelper::vars($template, $vars);
        return trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($s)));
    }

    public static function servicesListHtml(int $clientId): string
    {
        if ($clientId < 1) {
            return '';
        }
        $rows = self::services(['client_id' => $clientId, 'status' => 'active']);
        if (!$rows) {
            return '';
        }
        $html = '<ul>';
        foreach ($rows as $s) {
            $html .= '<li><strong>' . self::e($s->service_name ?: 'Υπηρεσία') . '</strong>' . ($s->label !== '' ? ' – ' . self::e($s->label) : '')
                . ($s->expiry_date ? ' (λήξη ' . self::e(self::dateGr($s->expiry_date)) . ')' : '') . '</li>';
        }
        return $html . '</ul>';
    }

    /** Maintenance updates, one per line: "Τύπος | Όνομα | Από | Σε". */
    public static function parseUpdates(string $text): array
    {
        $items = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) === 1) $parts = ['', $parts[0]];
            $items[] = array_pad(array_slice($parts, 0, 4), 4, '');
        }
        return $items;
    }

    public static function updatesHtml(array $items): string
    {
        if (!$items) {
            return '';
        }
        $accent = self::accent();
        $th = 'style="text-align:left;padding:8px;border-bottom:2px solid ' . $accent . ';font-size:13px;"';
        $td = 'style="padding:8px;border-bottom:1px solid #e5e7eb;font-size:13px;vertical-align:top;"';
        $html = '<table class="sp-n-data" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:16px 0;">'
            . "<tr><th $th>Τύπος</th><th $th>Στοιχείο</th><th $th>Προηγούμενη έκδοση</th><th $th>Νέα έκδοση</th></tr>";
        foreach ($items as $i) {
            $html .= "<tr><td $td>" . self::e($i[0]) . "</td><td $td><strong>" . self::e($i[1]) . "</strong></td><td $td>" . self::e($i[2]) . "</td><td $td>" . self::e($i[3]) . '</td></tr>';
        }
        return $html . '</table>';
    }

    /**
     * Accent and text colours of the business (Settings), read directly from the parameters.
     * Not OfferHelper::brand(): that also builds the logo URL from the site address, which a
     * command-line cron run (Scheduled Tasks via cli/joomla.php) may not be able to resolve.
     */
    public static function brandColors(): array
    {
        $params = ComponentHelper::getParams('com_supportplans');
        $accent = trim((string) $params->get('accent_color', '#367d22'));
        $secondary = trim((string) $params->get('secondary_color', '#ffffff'));
        return [
            'accent' => preg_match('/^#[0-9a-fA-F]{6}$/', $accent) ? $accent : '#367d22',
            'secondary' => preg_match('/^#[0-9a-fA-F]{6}$/', $secondary) ? $secondary : '#ffffff',
        ];
    }

    private static function accent(): string
    {
        return self::brandColors()['accent'];
    }

    /**
     * Logo for the e-mails (absolute URL, PNG/JPG/GIF; '' = none). Mail clients do not show SVG:
     * the PNG made in "Ρυθμίσεις email" (notify_email_logo) wins, then the Back Office logo if
     * it is already PNG/JPG/GIF.
     */
    public static function emailLogoUrl(): string
    {
        $params = ComponentHelper::getParams('com_supportplans');
        foreach ([(string) $params->get('notify_email_logo', ''), (string) $params->get('brand_logo', '')] as $path) {
            $path = trim($path);
            if ($path === '' || !preg_match('/\.(png|jpe?g|gif)(\?.*)?$/i', $path)) {
                continue;
            }
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            $file = JPATH_ROOT . '/' . ltrim(preg_replace('/\?.*$/', '', $path), '/');
            if (is_file($file)) {
                return self::siteRoot() . ltrim($path, '/') . (strpos($path, '?') === false ? '?v=' . filemtime($file) : '');
            }
        }
        return '';
    }

    /**
     * Fonts for the e-mails: Google Fonts with Greek. Web fonts show in Apple Mail, iOS, Outlook for
     * Mac and many Android apps; Gmail and Outlook for Windows use the fallback (Arial).
     */
    public const FONTS = [
        'geom'      => ['label' => 'Geom (όπως το Service Hub)', 'family' => 'Geom', 'css' => 'Geom:wght@400;600;700'],
        'inter'     => ['label' => 'Inter', 'family' => 'Inter', 'css' => 'Inter:wght@400;600;700'],
        'roboto'    => ['label' => 'Roboto', 'family' => 'Roboto', 'css' => 'Roboto:wght@400;500;700'],
        'open_sans' => ['label' => 'Open Sans', 'family' => 'Open Sans', 'css' => 'Open+Sans:wght@400;600;700'],
        'noto_sans' => ['label' => 'Noto Sans', 'family' => 'Noto Sans', 'css' => 'Noto+Sans:wght@400;600;700'],
        'arial'     => ['label' => 'Arial (χωρίς web font)', 'family' => '', 'css' => ''],
    ];

    /** Fonts for Outlook for Windows (installed fonts only; it cannot load web fonts). */
    public const OUTLOOK_FONTS = [
        'aptos'   => ['label' => 'Aptos (Microsoft 365 / νέο Outlook)', 'stack' => 'Aptos,Calibri,Arial,Helvetica,sans-serif'],
        'calibri' => ['label' => 'Calibri (όλα τα Office)', 'stack' => 'Calibri,Arial,Helvetica,sans-serif'],
        'arial'   => ['label' => 'Arial', 'stack' => 'Arial,Helvetica,sans-serif'],
    ];

    public static function outlookFontStack(): string
    {
        return (self::OUTLOOK_FONTS[(string) self::setting('notify_font_outlook')] ?? self::OUTLOOK_FONTS['aptos'])['stack'];
    }

    public static function font(): array
    {
        $key = (string) self::setting('notify_font');
        return self::FONTS[$key] ?? self::FONTS['geom'];
    }

    /** CSS font stack (no double quotes: it goes into style="…" attributes). */
    public static function fontStack(): string
    {
        $f = self::font();
        return ($f['family'] !== '' ? "'" . $f['family'] . "'," : '') . 'Arial,Helvetica,sans-serif';
    }

    /**
     * Logo for the e-mails with its display size: ['url' => …, 'w' => …, 'h' => 44] or []. Fixed
     * width/height keep the header in shape while a mail client still blocks the images.
     */
    public static function emailLogo(): array
    {
        $url = self::emailLogoUrl();
        if ($url === '') {
            return [];
        }
        $params = ComponentHelper::getParams('com_supportplans');
        $h = 44;
        $w = 0;
        foreach ([(string) $params->get('notify_email_logo', ''), (string) $params->get('brand_logo', '')] as $path) {
            $file = JPATH_ROOT . '/' . ltrim(preg_replace('/\?.*$/', '', trim($path)), '/');
            if ($path !== '' && !preg_match('#^https?://#i', $path) && is_file($file) && ($size = @getimagesize($file)) && $size[1] > 0) {
                $w = (int) round($size[0] * $h / $size[1]);
                break;
            }
        }
        if ($w > 220) {
            $h = (int) round($h * 220 / $w);
            $w = 220;
        }
        return ['url' => $url, 'w' => $w, 'h' => $h];
    }

    /**
     * Inline margins on the text blocks of the message. Outlook for Windows (Word engine) turns the
     * text of every table cell into a paragraph too, so a global p{margin} rule made table rows tall.
     */
    private static function inlineBlocks(string $html): string
    {
        $styles = [
            'p'  => 'margin:0 0 14px;',
            'h3' => 'margin:18px 0 8px;font-size:16px;',
            'h4' => 'margin:16px 0 6px;font-size:15px;',
            'ul' => 'margin:0 0 14px;padding-left:22px;',
            'ol' => 'margin:0 0 14px;padding-left:22px;',
            'li' => 'margin:0 0 4px;',
        ];
        return preg_replace_callback('#<(p|h3|h4|ul|ol|li)(\s[^>]*)?>#i', static function ($m) use ($styles) {
            $attrs = $m[2] ?? '';
            if (stripos($attrs, 'style=') !== false) {
                return $m[0];
            }
            return '<' . $m[1] . $attrs . ' style="' . $styles[strtolower($m[1])] . '">';
        }, $html);
    }

    /**
     * Outlook for Windows (Word engine) puts the text of every table cell in a paragraph of its own
     * with "auto" spacing before and after, which made the rows of the e-mail tables tall and the
     * text vertically centred. In the data tables (.sp-n-data) every cell's content is therefore
     * wrapped in an explicit paragraph with zero spacing (also the Word-only mso-margin-*-alt), and
     * the vertical alignment is repeated as an HTML attribute.
     */
    private static function outlookCells(string $html): string
    {
        return preg_replace_callback('#<table class="sp-n-data[^"]*".*?</table>#s', static function ($t) {
            return preg_replace_callback('#<(td|th)(\s[^>]*)?>(.*?)</\1>#s', static function ($m) {
                $attrs = $m[2] ?? '';
                $content = $m[3];
                $style = preg_match('/style="([^"]*)"/i', $attrs, $sm) ? $sm[1] : '';
                $keep = '';
                foreach (['font-size', 'font-weight', 'color', 'text-align', 'white-space', 'font-family'] as $prop) {
                    if (preg_match('/(?:^|;)\s*' . preg_quote($prop, '/') . '\s*:\s*([^;]+)/i', $style, $pm)) {
                        $keep .= $prop . ':' . trim($pm[1]) . ';';
                    }
                }
                $valign = preg_match('/vertical-align\s*:\s*(top|middle|bottom)/i', $style, $vm) ? strtolower($vm[1]) : 'top';
                if (stripos($attrs, 'valign=') === false) {
                    $attrs .= ' valign="' . $valign . '"';
                }
                if (trim($content) === '' || stripos($content, '<p') !== false) {
                    return '<' . $m[1] . $attrs . '>' . $content . '</' . $m[1] . '>';
                }
                return '<' . $m[1] . $attrs . '><p class="sp-n-cell" style="margin:0;mso-margin-top-alt:0;mso-margin-bottom-alt:0;line-height:18px;mso-line-height-rule:exactly;' . $keep . '">'
                    . $content . '</p></' . $m[1] . '>';
            }, $t[0]);
        }, $html);
    }

    /** Every table cell gets the font and an exact line height (Outlook does not inherit them). */
    private static function cellFonts(string $html): string
    {
        $font = 'font-family:' . self::fontStack() . ';';
        return preg_replace_callback('#<(td|th)(\s[^>]*)?>#i', static function ($m) use ($font) {
            $attrs = $m[2] ?? '';
            if (preg_match('/style="([^"]*)"/i', $attrs, $sm)) {
                if (stripos($sm[1], 'font-family') !== false) return $m[0];
                return '<' . $m[1] . str_replace($sm[0], 'style="' . $font . $sm[1] . '"', $attrs) . '>';
            }
            return '<' . $m[1] . $attrs . ' style="' . $font . '">';
        }, $html);
    }

    /** True when the Back Office logo is an SVG (the e-mails need a PNG made from it). */
    public static function brandLogoIsSvg(): bool
    {
        return (bool) preg_match('/\.svg(\?.*)?$/i', trim((string) ComponentHelper::getParams('com_supportplans')->get('brand_logo', '')));
    }

    /** Branded e-mail layout: logo top right (or the business name when there is no usable logo). */
    public static function wrap(string $inner, string $unsubscribeUrl = ''): string
    {
        $params = ComponentHelper::getParams('com_supportplans');
        $accent = self::accent();
        $name = self::brandName();
        $logo = self::emailLogo();
        $font = self::font();
        $stack = self::fontStack();
        $head = $logo
            ? '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td>&nbsp;</td><td align="right" style="text-align:right;">'
                . '<img src="' . self::e($logo['url']) . '" alt="' . self::e($name) . '"' . ($logo['w'] ? ' width="' . $logo['w'] . '"' : '') . ' height="' . $logo['h'] . '"'
                . ' style="height:' . $logo['h'] . 'px;' . ($logo['w'] ? 'width:' . $logo['w'] . 'px;' : 'width:auto;') . 'max-width:220px;border:0;display:inline-block;font-size:16px;font-weight:bold;color:#111827;"></td></tr></table>'
            : '<span style="font-size:20px;font-weight:bold;color:#111827;">' . self::e($name) . '</span>';
        $footer = trim((string) self::setting('notify_footer'));
        if ($footer === '') {
            $footer = implode("\n", array_filter([$name, (string) $params->get('brand_address', ''), (string) $params->get('brand_phone', ''), (string) $params->get('brand_email', ''), (string) $params->get('brand_website', '')]));
        }
        $footerHtml = nl2br(self::e($footer));
        if ($unsubscribeUrl !== '') {
            $footerHtml .= '<br><br><a href="' . self::e($unsubscribeUrl) . '" style="color:#6b7280;">Διαγραφή από τις ενημερώσεις</a>';
        }
        $webFont = $font['css'] !== ''
            // Not for Outlook for Windows: it cannot load web fonts and would fall back to Times.
            ? '<!--[if !mso]><!--><link href="https://fonts.googleapis.com/css2?family=' . $font['css'] . '&amp;display=swap" rel="stylesheet">'
                . '<style>@import url(https://fonts.googleapis.com/css2?family=' . $font['css'] . '&display=swap);</style><!--<![endif]-->'
            : '';
        $html = '<!DOCTYPE html><html lang="el" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="x-apple-disable-message-reformatting">' . $webFont
            . '<style>body,td,th,p,li,a,span,strong{font-family:' . $stack . '}a{color:' . $accent . '}'
            // Phones: bank, IBAN and beneficiary one under the other.
            . '@media only screen and (max-width:520px){.sp-n-bank td{display:block!important;width:auto!important;border-top:0!important;padding:1px 12px!important}'
            . '.sp-n-bank td.sp-n-bank-name{border-top:1px solid #eef0f4!important;padding-top:9px!important}.sp-n-bank td.sp-n-bank-ben{padding-bottom:9px!important}}'
            // Very narrow phones: smaller margins so a whole IBAN still fits on one line.
            . '@media only screen and (max-width:380px){.sp-n-outer{padding:12px 4px!important}.sp-n-body{padding:20px 14px!important}.sp-n-bank td.sp-n-bank-iban,.sp-n-bank td.sp-n-bank-iban p{font-size:11.5px!important}}</style>'
            // Outlook for Windows: Arial, no extra spacing around tables and cell paragraphs.
            . '<!--[if mso]><style>body,table,td,th,p,li,a,span,strong{font-family:' . self::outlookFontStack() . '!important}table{border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt}.MsoNormal{margin:0}'
            . '.sp-n-cell{margin:0!important;mso-margin-top-alt:0!important;mso-margin-bottom-alt:0!important}</style>'
            . '<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->'
            . '</head><body style="margin:0;padding:0;background:#f3f4f6;font-family:' . $stack . ';">'
            . '<table class="sp-n-outer" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;"><tr><td align="center">'
            . '<!--[if mso]><table role="presentation" width="640" cellpadding="0" cellspacing="0"><tr><td><![endif]-->'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:10px;overflow:hidden;color:#1f2937;">'
            . '<tr><td style="padding:18px 22px;border-bottom:4px solid ' . $accent . ';">' . $head . '</td></tr>'
            . '<tr><td class="sp-n-body" style="padding:24px 22px;font-size:15px;line-height:22px;mso-line-height-rule:exactly;">' . self::outlookCells(self::inlineBlocks($inner)) . '</td></tr>'
            . '<tr><td style="padding:16px 22px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;line-height:18px;mso-line-height-rule:exactly;">' . $footerHtml . '</td></tr>'
            . '</table><!--[if mso]></td></tr></table><![endif]--></td></tr></table></body></html>';
        return self::cellFonts($html);
    }

    public static function toText(string $html): string
    {
        $html = preg_replace('#<(head|style)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(br|/p|/tr|/li|/h[1-6])\s*/?>#i', "$0\n", $html);
        // Data tables (maintenance updates): cells separated by " | "; layout tables: line breaks.
        $html = preg_replace_callback('#<table class="sp-n-data[^"]*".*?</table>#is', static fn($m) => preg_replace(['#</?p\b[^>]*>\s*#i', '#</t[dh]>\s*(?=</tr>)#i', '#</t[dh]>#i'], ['', '', ' | '], $m[0]), $html);
        $html = preg_replace('#</t[dh]>#i', "\n", $html);
        $html = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ($1)', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/^[ \t]+|[ \t]+$/m', '', preg_replace('/[ \t]+/', ' ', $text));
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /* ================================================================ building messages */

    /* ---------------------------------------------------------------- renewal reminders */

    /** before | today | after, from the days until expiry. */
    public static function state(?int $days): string
    {
        if ($days === null || $days > 0) return 'before';
        return $days === 0 ? 'today' : 'after';
    }

    /** "λήγει στις 12/11/2026" / "λήγει σήμερα, 12/11/2026" / "έληξε στις 12/11/2026" (light markup). */
    public static function phrase(object $s): string
    {
        $days = self::daysUntil($s->expiry_date ?? null);
        $template = (string) self::setting('notify_phrase_' . self::state($days));
        return OfferHelper::vars($template, ['date' => self::dateGr($s->expiry_date ?? null), 'days' => $days === null ? '' : (string) abs($days)]);
    }

    /**
     * [subject, html] of ONE reminder for one or more services of the same client. Every
     * service gets its own wording (before / today / after expiry); expired services add the
     * "after expiry" text (from the catalogue service, or the general one); prices are net,
     * with VAT and total underneath.
     */
    public static function buildRenewal($services, object $client): array
    {
        $services = array_values(is_array($services) ? $services : [$services]);
        usort($services, static fn($a, $b) => strcmp((string) $a->expiry_date, (string) $b->expiry_date) ?: ((int) $a->id <=> (int) $b->id));
        $first = $services[0];
        $states = array_map(static fn($x) => self::state(self::daysUntil($x->expiry_date)), $services);

        $plain = static fn(string $markup) => trim(preg_replace('/\*\*|__|(?<!\*)\*(?!\*)/', '', $markup));
        $vars = self::clientVars($client) + self::serviceVars($first) + [
            'services' => implode(', ', array_map([self::class, 'serviceFull'], $services)),
            'date'     => self::dateGr($first->expiry_date),
            'phrase'   => $plain(self::phrase($first)),
        ];
        // Old (v1.12.0) single-service wording stays available for custom texts.
        $vars['expiry_phrase'] = $vars['phrase'];

        if (count($services) === 1) {
            $status = OfferHelper::vars((string) self::setting('notify_intro_single'), ['service_full' => self::serviceFull($first), 'service' => (string) $first->service_name,
                'label' => (string) $first->label, 'phrase' => self::phrase($first), 'date' => self::dateGr($first->expiry_date)]);
        } else {
            $status = (string) self::setting('notify_intro_multi');
            foreach ($services as $x) {
                $status .= "\n- **" . self::serviceFull($x) . '**: ' . self::phrase($x);
            }
        }
        $blocks = [
            'services_status' => OfferHelper::render($status, []),
            'price_table'     => self::priceTable($services),
            'banks'           => self::banksTable(),
            'expired_notice'  => self::expiredNotice($services),
        ];
        $subjectKey = in_array('after', $states, true) ? 'notify_subject_after' : (in_array('today', $states, true) ? 'notify_subject_today' : 'notify_renewal_subject');
        return [
            self::subject((string) self::setting($subjectKey), $vars),
            self::wrap(self::renderText((string) self::setting('notify_renewal_body'), $vars, $blocks)),
        ];
    }

    /** Compact card tables in the colours of the offer documents (readable on phones too). */
    private static function cardStyles(): array
    {
        $b = self::brandColors();
        $cell = 'padding:6px 10px;font-size:13px;line-height:18px;mso-line-height-rule:exactly;';
        return [
            'table' => 'style="width:100%;border-collapse:separate;border-spacing:0;margin:14px 0;border:1px solid #e4e7ec;border-radius:8px;overflow:hidden;"',
            'title' => 'style="background:' . $b['accent'] . ';color:' . $b['secondary'] . ';font-weight:700;text-align:left;' . $cell . 'padding-top:7px;padding-bottom:7px;"',
            'td'    => 'style="' . $cell . 'border-top:1px solid #eef0f4;text-align:left;vertical-align:top;"',
            'tdr'   => 'style="' . $cell . 'border-top:1px solid #eef0f4;text-align:right;vertical-align:top;white-space:nowrap;"',
            'sum'   => 'style="' . $cell . 'border-top:1px solid #eef0f4;text-align:left;color:#475467;"',
            'sumr'  => 'style="' . $cell . 'border-top:1px solid #eef0f4;text-align:right;white-space:nowrap;color:#475467;"',
            'total' => 'style="' . $cell . 'background:#fff3df;font-weight:800;text-align:left;"',
            'totalr'=> 'style="' . $cell . 'background:#fff3df;font-weight:800;text-align:right;white-space:nowrap;"',
            'small' => 'style="color:#667085;font-size:12px;"',
        ];
    }

    /**
     * "Οικονομική σύνοψη": one line per service with its net price (expiry and period in small
     * text underneath), then net total, VAT and total. Only services with a price.
     */
    public static function priceTable(array $services): string
    {
        $priced = array_values(array_filter($services, static fn($x) => $x->price !== null && $x->price !== ''));
        if (!$priced) {
            return '';
        }
        $c = self::cardStyles();
        $html = '<table class="sp-n-data" cellpadding="0" cellspacing="0" ' . $c['table'] . '>'
            . '<tr><td ' . $c['title'] . '>Οικονομική σύνοψη</td><td ' . str_replace('text-align:left;', 'text-align:right;white-space:nowrap;', $c['title']) . '>Αξία προ ΦΠΑ</td></tr>';
        $net = 0.0;
        foreach ($priced as $x) {
            $n = round((float) $x->price, 2);
            $net += $n;
            $html .= '<tr><td ' . $c['td'] . '><strong>' . self::e(self::serviceFull($x)) . '</strong><br><span ' . $c['small'] . '>λήξη ' . self::e(self::dateGr($x->expiry_date))
                . ' · ανανέωση ' . self::e(self::durationLabel(max(1, (int) $x->duration_months))) . '</span></td><td ' . $c['tdr'] . '>' . self::e(self::money($n)) . '</td></tr>';
        }
        $vat = round($net * self::vatRate() / 100, 2);
        $html .= '<tr><td ' . $c['sum'] . '>Σύνολο προ ΦΠΑ</td><td ' . $c['sumr'] . '>' . self::e(self::money($net)) . '</td></tr>'
            . '<tr><td ' . $c['sum'] . '>Φ.Π.Α. ' . self::e(self::vatLabel()) . '</td><td ' . $c['sumr'] . '>' . self::e(self::money($vat)) . '</td></tr>'
            . '<tr><td ' . $c['total'] . '>Σύνολο</td><td ' . $c['totalr'] . '>' . self::e(self::money($net + $vat)) . '</td></tr>';
        return $html . '</table>';
    }

    /**
     * Bank accounts from the Settings. Wide screens: one line per bank (bank | IBAN | beneficiary);
     * phones (≤ 520px, via the .sp-n-bank rules in wrap()): bank, IBAN and beneficiary on separate
     * lines, so the IBAN fits on one line.
     */
    public static function banksTable(): string
    {
        $banks = OfferHelper::banks();
        if (!$banks) {
            return '';
        }
        $c = self::cardStyles();
        $cell = 'padding:7px 10px;font-size:13px;line-height:18px;mso-line-height-rule:exactly;border-top:1px solid #eef0f4;text-align:left;vertical-align:top;';
        $html = '<table class="sp-n-data sp-n-bank" cellpadding="0" cellspacing="0" ' . $c['table'] . '>'
            . '<tr><td colspan="3" ' . $c['title'] . '>Τραπεζικοί λογαριασμοί</td></tr>';
        foreach ($banks as $b) {
            $html .= '<tr>'
                . '<td class="sp-n-bank-name" style="' . $cell . 'font-weight:700;">' . self::e(OfferHelper::upperGr((string) $b['name'])) . '</td>'
                . '<td class="sp-n-bank-iban" style="' . $cell . 'white-space:nowrap;font-family:Consolas,Menlo,monospace;">' . self::ibanHtml($b['iban']) . '</td>'
                . '<td class="sp-n-bank-ben" style="' . $cell . 'color:#475467;">' . self::e($b['beneficiary']) . '</td>'
                . '</tr>';
        }
        return $html . '</table>';
    }

    /** IBAN in groups of four characters, alternately bold, so that it is easy to read and copy. */
    public static function ibanHtml(string $iban): string
    {
        $compact = strtoupper(preg_replace('/\s+/', '', $iban));
        if ($compact === '') {
            return '';
        }
        $out = [];
        foreach (str_split($compact, 4) as $i => $group) {
            $out[] = $i % 2 === 0 ? '<strong>' . self::e($group) . '</strong>' : '<span style="color:#475467;">' . self::e($group) . '</span>';
        }
        return implode(' ', $out);
    }

    /**
     * Highlighted text for the services that have already expired (empty otherwise). Services
     * that share the same text get it once, naming them together ("A και B").
     */
    public static function expiredNotice(array $services): string
    {
        $byText = [];
        foreach ($services as $x) {
            if (self::state(self::daysUntil($x->expiry_date)) !== 'after') continue;
            $text = trim((string) ($x->catalog_expired_text ?? '')) ?: (string) self::setting('notify_expired_text');
            $byText[$text][] = $x;
        }
        $html = '';
        foreach ($byText as $text => $list) {
            $names = array_map([self::class, 'serviceFull'], $list);
            $joined = count($names) > 1 ? implode(', ', array_slice($names, 0, -1)) . ' και ' . end($names) : $names[0];
            $labels = array_values(array_unique(array_filter(array_map(static fn($x) => (string) $x->label, $list))));
            $html .= OfferHelper::render($text, ['service_full' => $joined, 'service' => implode(', ', array_map(static fn($x) => (string) $x->service_name, $list)),
                'label' => implode(', ', $labels), 'date' => self::dateGr($list[0]->expiry_date), 'company' => self::brandName()]);
        }
        return $html === '' ? '' : '<div style="border-left:4px solid #dc2626;background:#fef2f2;border-radius:6px;padding:10px 14px 0;margin:14px 0;font-size:14px;line-height:1.5;">' . $html . '</div>';
    }

    public static function buildReport(object $report, object $service, object $client): array
    {
        $vars = self::clientVars($client) + self::serviceVars($service) + ['period' => (string) $report->period];
        $blocks = [
            'summary' => OfferHelper::render((string) $report->summary, $vars),
            'updates' => self::updatesHtml(self::parseUpdates((string) $report->items)),
        ];
        return [
            self::subject((string) self::setting('notify_report_subject'), $vars),
            self::wrap(self::renderText((string) self::setting('notify_report_body'), $vars, $blocks)),
        ];
    }

    /** [subject, html, unsubscribe url]; $client->id 0 = preview with the admin's details. */
    public static function buildCampaign(object $campaign, object $client): array
    {
        $vars = self::clientVars($client);
        $unsub = (int) $client->id > 0 ? self::unsubscribeUrl($client) : self::siteRoot() . '#';
        $html = self::renderText((string) $campaign->body, $vars, ['services_list' => self::servicesListHtml((int) $client->id), 'banks' => self::banksTable()]);
        return [self::subject((string) $campaign->subject, $vars), self::wrap($html, $unsub), (int) $client->id > 0 ? $unsub : ''];
    }

    /** Queues a message; a duplicate non-empty ref is ignored. Returns the id or 0. */
    public static function queue(array $m): int
    {
        $db = self::db();
        $now = self::now();
        $cols = [
            'client_id' => (int) ($m['client_id'] ?? 0),
            'kind'      => $db->quote($m['kind']),
            'to_email'  => $db->quote($m['to_email']),
            'subject'   => $db->quote(mb_substr((string) $m['subject'], 0, 255)),
            'body'      => $db->quote($m['body']),
            'unsub_url' => $db->quote((string) ($m['unsub_url'] ?? '')),
            'status'    => $db->quote('queued'),
            'created'   => $db->quote($now),
            'scheduled' => $db->quote($m['scheduled'] ?? $now),
        ];
        // NULL keeps the unique ref index free for messages without a ref.
        $cols['ref'] = empty($m['ref']) ? 'NULL' : $db->quote($m['ref']);
        $cols['service_id'] = empty($m['service_id']) ? 'NULL' : (int) $m['service_id'];
        $cols['campaign_id'] = empty($m['campaign_id']) ? 'NULL' : (int) $m['campaign_id'];
        $cols['service_ids'] = $db->quote((string) ($m['service_ids'] ?? ''));
        $sql = 'INSERT IGNORE INTO ' . $db->quoteName(self::T_MESSAGES) . ' (' . implode(',', array_map([$db, 'quoteName'], array_keys($cols))) . ') VALUES (' . implode(',', $cols) . ')';
        $db->setQuery($sql)->execute();
        return (int) $db->getAffectedRows() > 0 ? (int) $db->insertid() : 0;
    }

    /** "12:2027-02-25,15:2027-02-24": the services of a reminder with the expiry it was written for. */
    private static function serviceIdsField(array $services): string
    {
        return implode(',', array_map(static fn($x) => (int) $x->id . ':' . $x->expiry_date, $services));
    }

    /** Manual reminder ("Υπενθύμιση τώρα"): the service together with its group. Returns the message id. */
    public static function queueRenewal(object $service): int
    {
        $client = self::client((int) $service->client_id);
        if (!$client) {
            return 0;
        }
        $group = [$service];
        foreach (self::groups(self::candidates((int) $client->id, false)) as $g) {
            if (in_array((int) $service->id, array_map(static fn($x) => (int) $x->id, $g), true)) {
                $group = $g;
                break;
            }
        }
        return self::queueRenewalFor($group, $client);
    }

    /** Manual reminder for the services chosen by the user (any services of the same client). */
    public static function queueRenewalFor(array $services, object $client): int
    {
        $services = array_values(array_filter($services, static fn($x) => (int) $x->client_id === (int) $client->id && $x->expiry_date));
        if (!$services) {
            return 0;
        }
        usort($services, static fn($a, $b) => strcmp((string) $a->expiry_date, (string) $b->expiry_date));
        [$subject, $body] = self::buildRenewal($services, $client);
        return self::queue(['client_id' => $client->id, 'service_id' => $services[0]->id, 'service_ids' => self::serviceIdsField($services), 'kind' => 'renewal',
            'to_email' => $client->email, 'subject' => $subject, 'body' => $body]);
    }

    /** Active services of a client that have an expiry date (the ones a reminder can be about). */
    public static function remindable(int $clientId): array
    {
        return array_values(array_filter(self::services(['client_id' => $clientId, 'status' => 'active']), static fn($x) => (bool) $x->expiry_date));
    }

    /**
     * Active client services with an expiry that can still get reminders: not past their own
     * last reminder day. Grouped per client. $auto = only services with automatic reminders.
     */
    public static function candidates(?int $clientId = null, bool $auto = true): array
    {
        $db = self::db();
        $today = self::today();
        $q = self::servicesQuery()->where("s.status='active'")->where("c.status='active'")->where('s.expiry_date IS NOT NULL')
            ->where('s.expiry_date BETWEEN ' . $db->quote(self::addDays($today, -90)) . ' AND ' . $db->quote(self::addDays($today, 400)))
            ->order('s.client_id ASC, s.expiry_date ASC, s.id ASC');
        if ($auto) $q->where('s.auto_remind=1');
        if ($clientId) $q->where('s.client_id=' . $clientId);
        $out = [];
        foreach ($db->setQuery($q)->loadObjectList() as $x) {
            $days = self::daysUntil($x->expiry_date);
            $offsets = self::reminderDays($x);
            if ($days === null || ($auto && (!$offsets || $days < min($offsets)))) continue;
            $out[] = $x;
        }
        return $out;
    }

    /**
     * Groups the services of each client whose expiry dates are close: sorted by date, a service
     * joins the group while it expires at most notify_group_days after the previous one.
     */
    public static function groups(array $services): array
    {
        $window = max(0, (int) self::setting('notify_group_days'));
        $groups = [];
        $current = [];
        $last = null;
        foreach ($services as $x) {
            if ($current && ((int) $current[0]->client_id !== (int) $x->client_id || $last === null
                || (int) self::date($last)->diff(self::date($x->expiry_date))->days > $window)) {
                $groups[] = $current;
                $current = [];
            }
            $current[] = $x;
            $last = $x->expiry_date;
        }
        if ($current) $groups[] = $current;
        return $groups;
    }

    /**
     * The reminder day of a group that has been reached today, counted from the earliest expiry
     * of the group, with the reminder days of all its services. null = nothing due yet.
     */
    public static function groupOffset(array $group): ?int
    {
        $offsets = [];
        foreach ($group as $x) $offsets = array_merge($offsets, self::reminderDays($x));
        $days = self::daysUntil($group[0]->expiry_date);
        $reached = array_filter(array_unique($offsets), static fn($o) => $o >= $days);
        return $reached ? min($reached) : null;
    }

    /** Next date on which a reminder will go out for a group (for the overview); null = none. */
    public static function nextReminderDate(array $group): ?string
    {
        $offsets = [];
        foreach ($group as $x) $offsets = array_merge($offsets, self::reminderDays($x));
        $offsets = array_unique($offsets);
        rsort($offsets);
        $current = self::groupOffset($group);
        if ($current !== null && !self::groupCovered($group, $current)) {
            return self::today();
        }
        foreach ($offsets as $o) {
            $date = self::addDays($group[0]->expiry_date, -$o);
            if ($date > self::today()) return $date;
        }
        return null;
    }

    /** Per client service with automatic reminders: next reminder date and size of its group. */
    public static function schedule(?int $clientId = null): array
    {
        $out = [];
        foreach (self::groups(self::candidates($clientId)) as $group) {
            $next = self::nextReminderDate($group);
            foreach ($group as $x) {
                $out[(int) $x->id] = ['date' => $next, 'size' => count($group)];
            }
        }
        return $out;
    }

    private static function groupCovered(array $group, int $offset): bool
    {
        $db = self::db();
        foreach ($group as $x) {
            $n = (int) $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from($db->quoteName(self::T_SENT))
                ->where('service_id=' . (int) $x->id)->where('expiry_date=' . $db->quote($x->expiry_date))->where('offset_days=' . $offset))->loadResult();
            if (!$n) return false;
        }
        return true;
    }

    /* ================================================================ sending */    /* ================================================================ sending */

    /** Atomically claims and sends one queued message. */
    public static function sendNow(int $id): bool
    {
        $db = self::db();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_MESSAGES))
            ->set(["status='sending'", 'attempts=attempts+1', 'claimed=' . $db->quote(self::now())])
            ->where('id=' . $id)->where("status='queued'"))->execute();
        if ($db->getAffectedRows() < 1) {
            return false;
        }
        return self::deliver(self::message($id));
    }

    private static function deliver(object $m): bool
    {
        $db = self::db();
        $set = static function (array $fields) use ($db, $m) {
            $sets = [];
            foreach ($fields as $k => $v) {
                $sets[] = $db->quoteName($k) . '=' . ($v === null ? 'NULL' : (is_int($v) ? $v : $db->quote($v)));
            }
            $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_MESSAGES))->set($sets)->where('id=' . (int) $m->id))->execute();
        };

        $client = (int) $m->client_id > 0 ? self::client((int) $m->client_id) : null;
        $skip = self::skipReason($m, $client);
        if ($skip !== '') {
            if ($m->kind === 'renewal') {
                self::cancelRenewal((int) $m->id, $skip);
            } else {
                $set(['status' => 'cancelled', 'last_error' => $skip]);
            }
            return false;
        }

        $error = '';
        try {
            $mailer = self::senderMailer();
            $bcc = trim((string) self::setting('notify_bcc'));
            if ($m->kind === 'renewal' && $bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
                $mailer->addBcc($bcc);
            }
            if ($m->kind === 'campaign' && (string) $m->unsub_url !== '') {
                $mailer->addCustomHeader('List-Unsubscribe', '<' . $m->unsub_url . '>');
                $mailer->addCustomHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            }
            $mailer->addRecipient((string) $m->to_email);
            $mailer->setSubject(preg_replace('/[\r\n]+/', ' ', (string) $m->subject));
            $mailer->isHtml(true);
            $mailer->setBody((string) $m->body);
            $mailer->AltBody = self::toText((string) $m->body);
            $ok = $mailer->Send();
            if ($ok !== true) {
                $error = (string) ($mailer->ErrorInfo ?? '') ?: 'Η αποστολή απέτυχε.';
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage() ?: get_class($e);
        }

        if ($error === '') {
            $set(['status' => 'sent', 'sent' => self::now(), 'last_error' => null]);
            return true;
        }
        $retry = (int) $m->attempts < self::MAX_ATTEMPTS && $m->kind !== 'test';
        $set(['status' => $retry ? 'queued' : 'failed', 'last_error' => mb_substr($error, 0, 1000), 'scheduled' => self::now(900 * max(1, (int) $m->attempts))]);
        return false;
    }

    /** [name, email] that a document type is sent from (for display). */
    public static function senderOf(string $docType = ''): array
    {
        $pick = static function (string $key) use ($docType): string {
            $own = $docType !== '' ? trim((string) self::setting('doc_' . $docType . '_' . $key)) : '';
            return $own !== '' ? $own : trim((string) self::setting('notify_' . $key));
        };
        // Same rules as senderMailer(): own email (+ name or company), else Joomla's sender.
        $app = Factory::getApplication();
        $email = $pick('from_email');
        $name = trim(preg_replace('/[\r\n"<>]+/', '', $pick('from_name')));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [$name !== '' ? $name : self::brandName(), $email];
        }
        return [$name !== '' ? $name : (string) $app->get('fromname'), (string) $app->get('mailfrom')];
    }

    /**
     * Mailer with the sender, name and reply-to of the email settings. With a document type
     * (offer / structure / plan), that type's own sender is used where set (Αποστολείς εγγράφων).
     */
    public static function senderMailer(string $docType = '')
    {
        $mailer = self::mailer();
        $pick = static function (string $key) use ($docType): string {
            $own = $docType !== '' ? trim((string) self::setting('doc_' . $docType . '_' . $key)) : '';
            return $own !== '' ? $own : trim((string) self::setting('notify_' . $key));
        };
        $fromEmail = $pick('from_email');
        $fromName = trim(preg_replace('/[\r\n"<>]+/', '', $pick('from_name')));
        if ($fromEmail !== '' && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $mailer->setSender([$fromEmail, $fromName !== '' ? $fromName : self::brandName()]);
        } elseif ($fromName !== '') {
            $mailer->setSender([(string) Factory::getApplication()->get('mailfrom'), $fromName]);
        }
        $replyTo = $pick('reply_to');
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mailer->addReplyTo($replyTo);
        }
        return $mailer;
    }

    private static function mailer()
    {
        $container = Factory::getContainer();
        if (interface_exists(MailerFactoryInterface::class) && $container->has(MailerFactoryInterface::class)) {
            return $container->get(MailerFactoryInterface::class)->createMailer();
        }
        return Factory::getMailer();
    }

    /** Last checks before sending: a queued message may no longer be appropriate. */
    private static function skipReason(object $m, ?object $client): string
    {
        if ($m->kind === 'test') return '';
        if (!$client) return 'Ο πελάτης δεν υπάρχει πλέον.';
        if ($client->status !== 'active') return 'Ο πελάτης είναι ανενεργός.';
        if ($m->kind === 'campaign' && $client->unsubscribed_at) return 'Ο πελάτης έχει διαγραφεί από τις ενημερώσεις.';
        if (strcasecmp((string) $client->email, (string) $m->to_email) !== 0) return 'Το email του πελάτη άλλαξε μετά τη δημιουργία του μηνύματος.';
        if ($m->kind === 'renewal') {
            // Every service of the reminder must still be active with the expiry it was written for.
            $items = [];
            foreach (array_filter(explode(',', (string) ($m->service_ids ?? ''))) as $pair) {
                [$sid, $exp] = array_pad(explode(':', $pair, 2), 2, '');
                $items[(int) $sid] = $exp;
            }
            if (!$items && $m->ref && preg_match('/^renewal:(\d+):(\d{4}-\d{2}-\d{2}):/', (string) $m->ref, $mm)) {
                $items[(int) $mm[1]] = $mm[2]; // v1.12.0 reminders
            }
            foreach ($items as $sid => $exp) {
                $s = self::service($sid);
                if (!$s || $s->status !== 'active') return 'Μία από τις υπηρεσίες δεν είναι πλέον ενεργή.';
                if ($exp !== '' && $exp !== (string) $s->expiry_date) return 'Η ημερομηνία λήξης άλλαξε (η υπηρεσία ανανεώθηκε).';
            }
        }
        return '';
    }

    /** Sends the next batch of due messages. Returns [sent, failed]. */
    public static function processQueue(?int $limit = null): array
    {
        $db = self::db();
        // Messages left in "sending" by an interrupted run go back to the queue.
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_MESSAGES))->set("status='queued'")
            ->where("status='sending'")->where('claimed<' . $db->quote(self::now(-1800))))->execute();
        $limit = $limit ?: max(1, min(200, (int) self::setting('notify_batch_size')));
        $ids = $db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName(self::T_MESSAGES))
            ->where("status='queued'")->where('scheduled<=' . $db->quote(self::now()))->order('scheduled ASC, id ASC'), 0, $limit)->loadColumn();
        $sent = $failed = 0;
        foreach ($ids as $id) {
            $m = self::message((int) $id);
            if (!$m || $m->status !== 'queued') continue;
            self::sendNow((int) $id) ? $sent++ : $failed++;
        }
        return [$sent, $failed];
    }

    /**
     * Queues the renewal reminders that are due. Services of the same client with close expiry
     * dates go out together in one message. For each group the reminder day reached today is
     * counted from its earliest expiry; every service is marked for (expiry, day) in T_SENT, so
     * a reminder is sent once, a missed day catches up with one message, and a service that
     * leaves the group (e.g. renewed) is not reminded again for a day already covered.
     */
    public static function queueRenewals(): int
    {
        $db = self::db();
        $queued = 0;
        foreach (self::groups(self::candidates()) as $group) {
            $offset = self::groupOffset($group);
            if ($offset === null) continue;
            // Claim the markers first: a parallel run that loses the race inserts nothing and skips.
            $claimed = 0;
            foreach ($group as $x) {
                $db->setQuery('INSERT IGNORE INTO ' . $db->quoteName(self::T_SENT) . ' (service_id, expiry_date, offset_days, message_id, created) VALUES ('
                    . (int) $x->id . ',' . $db->quote($x->expiry_date) . ',' . $offset . ',0,' . $db->quote(self::now()) . ')')->execute();
                $claimed += (int) $db->getAffectedRows();
            }
            if (!$claimed) continue;
            $client = self::client((int) $group[0]->client_id);
            [$subject, $body] = self::buildRenewal($group, $client);
            $id = self::queue(['client_id' => $client->id, 'service_id' => $group[0]->id, 'service_ids' => self::serviceIdsField($group), 'kind' => 'renewal',
                'to_email' => $client->email, 'subject' => $subject, 'body' => $body]);
            $where = [];
            foreach ($group as $x) $where[] = '(service_id=' . (int) $x->id . ' AND expiry_date=' . $db->quote($x->expiry_date) . ' AND offset_days=' . $offset . ' AND message_id=0)';
            $db->setQuery('UPDATE ' . $db->quoteName(self::T_SENT) . ' SET message_id=' . $id . ' WHERE ' . implode(' OR ', $where))->execute();
            if ($id) $queued++;
        }
        return $queued;
    }

    /**
     * Time of day (site time zone) from which the automatic renewal reminders are prepared, as
     * [hour, minute]; null = any time. Default 09:30.
     */
    public static function sendTime(): ?array
    {
        $raw = trim((string) self::setting('notify_send_time'));
        $saved = ComponentHelper::getParams('com_supportplans')->get('notify_send_time', null);
        if ($saved !== null && trim((string) $saved) === '') {
            return null; // explicitly cleared: send whenever the task runs
        }
        return preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $raw, $m) ? [(int) $m[1], (int) $m[2]] : [9, 30];
    }

    public static function sendTimeLabel(): string
    {
        $t = self::sendTime();
        return $t ? sprintf('%02d:%02d', $t[0], $t[1]) : '';
    }

    /** True from the send time of today until midnight (always true when no time is set). */
    public static function sendTimeReached(): bool
    {
        $t = self::sendTime();
        if (!$t) {
            return true;
        }
        $now = new \DateTimeImmutable('now', self::tz());
        return (int) $now->format('G') * 60 + (int) $now->format('i') >= $t[0] * 60 + $t[1];
    }

    /**
     * Scheduled run: the renewal reminders that are due (from the send time of the day on; each
     * reminder is queued once, so later runs of the same day add nothing), then the queue, which
     * also sends reports and campaigns at any time. $force (the "now" button) ignores the time.
     */
    public static function run(bool $force = false): array
    {
        $queued = $force || self::sendTimeReached() ? self::queueRenewals() : 0;
        [$sent, $failed] = self::processQueue();
        try {
            OfferHelper::saveParam('notify_last_run', self::now());
        } catch (\Throwable $e) {
        }
        // Scheduled runs are logged only when they did something (they run every few minutes).
        $app = Factory::getApplication();
        $isTask = !(method_exists($app, 'isClient') && $app->isClient('site') && $app->input->getCmd('task', '') !== '');
        if ($isTask && ($queued || $sent || $failed)) {
            Activity::write('Ουρά email', 'Αυτόματη εκτέλεση (Χρονοπρογραμματισμένη εργασία)', 0, '', [], $failed ? 'warning' : 'ok',
                sprintf('Νέες υπενθυμίσεις: %d · Εστάλησαν: %d · Απέτυχαν: %d', $queued, $sent, $failed), 'scheduler');
        }
        return ['queued' => $queued, 'sent' => $sent, 'failed' => $failed];
    }

    public static function sendTest(string $to, string $subject, string $body): bool
    {
        $id = self::queue(['kind' => 'test', 'to_email' => $to, 'subject' => '[ΔΟΚΙΜΗ] ' . $subject, 'body' => $body]);
        return $id > 0 && self::sendNow($id);
    }
}

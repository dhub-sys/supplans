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
 * then sent by the queue: from the Joomla Scheduled Task "Support Plans – Ειδοποιήσεις"
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
            'notify_reminder_days'   => self::DEFAULT_REMINDER_DAYS,
            'notify_batch_size'      => 20,
            'notify_footer'          => '',
            'notify_renewal_subject' => 'Υπενθύμιση ανανέωσης: {service} {label}',
            'notify_renewal_body'    => "Αγαπητέ/ή {client_name},\n\nΣας ενημερώνουμε ότι η υπηρεσία **{service}** {label} {expiry_phrase} (**{expiry_date}**).\n\n{price_line}\n\nΓια να μη διακοπεί η λειτουργία της, απαντήστε σε αυτό το email ή επικοινωνήστε μαζί μας ώστε να προχωρήσουμε στην ανανέωση.\n\nΜε εκτίμηση,\n{company}",
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
            ->select('s.*, k.name AS service_name, k.reminder_days AS catalog_reminder_days, c.name AS client_name, c.company AS client_company, c.email AS client_email, c.status AS client_status')
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
        foreach ([self::T_SERVICES, self::T_MESSAGES] as $t) {
            $db->setQuery($db->getQuery(true)->delete($db->quoteName($t))->where('client_id=' . $id))->execute();
        }
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_CLIENTS))->where('id=' . $id))->execute();
    }

    /** Moves the expiry forward by one period and cancels reminders queued for the old date. */
    public static function renew(int $id): ?string
    {
        $s = self::service($id);
        if (!$s) {
            return null;
        }
        $db = self::db();
        $new = self::addMonths($s->expiry_date ?: self::today(), max(1, (int) $s->duration_months));
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_SERVICES))
            ->set(['expiry_date=' . $db->quote($new), "status='active'", 'modified=' . $db->quote(self::now())])->where('id=' . $id))->execute();
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_MESSAGES))
            ->set(["status='cancelled'", 'last_error=' . $db->quote('Η υπηρεσία ανανεώθηκε.')])
            ->where('service_id=' . $id)->where("kind='renewal'")->where("status='queued'"))->execute();
        return $new;
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
        return rtrim($cli && $saved !== '' ? $saved : Uri::root(), '/') . '/';
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
            'duration'      => $duration,
            'price_line'    => $price !== '' ? 'Κόστος ανανέωσης: ' . $price . ' / ' . $duration . '.' : '',
        ];
    }

    /**
     * Light markup (same as the offer texts) to HTML with {variables}. Placeholders in $blocks
     * are replaced afterwards by HTML the plugin builds itself (already escaped).
     */
    public static function renderText(string $text, array $vars, array $blocks = []): string
    {
        $html = OfferHelper::render($text, $vars);
        foreach ($blocks as $key => $block) {
            $html = str_replace(['<p>{' . $key . '}</p>', '{' . $key . '}'], [$block, $block], $html);
        }
        // Empty paragraphs left by empty variables (e.g. no price).
        return preg_replace('#<p>\s*</p>#', '', $html);
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

    private static function accent(): string
    {
        $c = (string) ComponentHelper::getParams('com_supportplans')->get('accent_color', '#1d4ed8');
        return preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $c) ? $c : '#1d4ed8';
    }

    /** Branded e-mail layout around the message body. */
    public static function wrap(string $inner, string $unsubscribeUrl = ''): string
    {
        $params = ComponentHelper::getParams('com_supportplans');
        $accent = self::accent();
        $name = self::brandName();
        $logo = trim((string) $params->get('brand_logo', ''));
        if ($logo !== '' && !preg_match('#^https?://#i', $logo)) {
            $logo = self::siteRoot() . ltrim($logo, '/');
        }
        if ($logo !== '' && strtolower(substr($logo, -4)) === '.svg') {
            $logo = ''; // most mail clients do not show SVG images
        }
        $head = $logo !== ''
            ? '<img src="' . self::e($logo) . '" alt="' . self::e($name) . '" style="max-height:56px;max-width:220px;border:0;display:block;">'
            : '<span style="font-size:20px;font-weight:bold;color:#111827;">' . self::e($name) . '</span>';
        $footer = trim((string) self::setting('notify_footer'));
        if ($footer === '') {
            $footer = implode("\n", array_filter([$name, (string) $params->get('brand_address', ''), (string) $params->get('brand_phone', ''), (string) $params->get('brand_email', ''), (string) $params->get('brand_website', '')]));
        }
        $footerHtml = nl2br(self::e($footer));
        if ($unsubscribeUrl !== '') {
            $footerHtml .= '<br><br><a href="' . self::e($unsubscribeUrl) . '" style="color:#6b7280;">Διαγραφή από τις ενημερώσεις</a>';
        }
        return '<!DOCTYPE html><html lang="el"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<style>p{margin:0 0 14px}h3,h4{margin:18px 0 8px}ul,ol{margin:0 0 14px;padding-left:22px}a{color:' . $accent . '}</style></head>'
            . '<body style="margin:0;padding:0;background:#f3f4f6;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;"><tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:10px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
            . '<tr><td style="padding:22px 28px;border-bottom:4px solid ' . $accent . ';">' . $head . '</td></tr>'
            . '<tr><td style="padding:28px;font-size:15px;line-height:1.6;">' . $inner . '</td></tr>'
            . '<tr><td style="padding:16px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;line-height:1.5;">' . $footerHtml . '</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    public static function toText(string $html): string
    {
        $html = preg_replace('#<(head|style)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(br|/p|/tr|/li|/h[1-6])\s*/?>#i', "$0\n", $html);
        // Data tables (maintenance updates): cells separated by " | "; layout tables: line breaks.
        $html = preg_replace_callback('#<table class="sp-n-data".*?</table>#is', static fn($m) => preg_replace(['#</t[dh]>\s*(?=</tr>)#i', '#</t[dh]>#i'], ['', ' | '], $m[0]), $html);
        $html = preg_replace('#</t[dh]>#i', "\n", $html);
        $html = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ($1)', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/^[ \t]+|[ \t]+$/m', '', preg_replace('/[ \t]+/', ' ', $text));
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /* ================================================================ building messages */

    /** [subject, html] of the renewal reminder of a client service. */
    public static function buildRenewal(object $service, object $client): array
    {
        $vars = self::clientVars($client) + self::serviceVars($service);
        return [
            self::subject((string) self::setting('notify_renewal_subject'), $vars),
            self::wrap(self::renderText((string) self::setting('notify_renewal_body'), $vars)),
        ];
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
        $html = self::renderText((string) $campaign->body, $vars, ['services_list' => self::servicesListHtml((int) $client->id)]);
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
        $sql = 'INSERT IGNORE INTO ' . $db->quoteName(self::T_MESSAGES) . ' (' . implode(',', array_map([$db, 'quoteName'], array_keys($cols))) . ') VALUES (' . implode(',', $cols) . ')';
        $db->setQuery($sql)->execute();
        return (int) $db->getAffectedRows() > 0 ? (int) $db->insertid() : 0;
    }

    public static function queueRenewal(object $service, ?string $ref = null): int
    {
        $client = self::client((int) $service->client_id);
        if (!$client) {
            return 0;
        }
        [$subject, $body] = self::buildRenewal($service, $client);
        return self::queue(['client_id' => $client->id, 'service_id' => $service->id, 'kind' => 'renewal', 'ref' => $ref,
            'to_email' => $client->email, 'subject' => $subject, 'body' => $body]);
    }

    /* ================================================================ sending */

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
            $set(['status' => 'cancelled', 'last_error' => $skip]);
            return false;
        }

        $error = '';
        try {
            $mailer = self::mailer();
            $fromEmail = trim((string) self::setting('notify_from_email'));
            $fromName = trim(preg_replace('/[\r\n"<>]+/', '', (string) self::setting('notify_from_name')));
            if ($fromEmail !== '' && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                $mailer->setSender([$fromEmail, $fromName !== '' ? $fromName : self::brandName()]);
            } elseif ($fromName !== '') {
                $mailer->setSender([(string) Factory::getApplication()->get('mailfrom'), $fromName]);
            }
            $replyTo = trim((string) self::setting('notify_reply_to'));
            if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mailer->addReplyTo($replyTo);
            }
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
        if ($m->kind === 'renewal' && (int) $m->service_id > 0) {
            $s = self::service((int) $m->service_id);
            if (!$s || $s->status !== 'active') return 'Η υπηρεσία δεν είναι ενεργή.';
            if ($m->ref && preg_match('/^renewal:\d+:(\d{4}-\d{2}-\d{2}):/', (string) $m->ref, $mm) && $mm[1] !== (string) $s->expiry_date) {
                return 'Η ημερομηνία λήξης άλλαξε (η υπηρεσία ανανεώθηκε).';
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
     * Queues the renewal reminder whose threshold was reached most recently, for every active
     * client service with automatic reminders. Each (service, expiry date, threshold) is queued
     * once thanks to the unique ref; a missed day catches up with one message, not several.
     */
    public static function queueRenewals(): int
    {
        $db = self::db();
        $today = self::today();
        $q = self::servicesQuery()->where("s.status='active'")->where('s.auto_remind=1')->where("c.status='active'")
            ->where('s.expiry_date IS NOT NULL')
            ->where('s.expiry_date BETWEEN ' . $db->quote(self::addDays($today, -90)) . ' AND ' . $db->quote(self::addDays($today, 365)));
        $queued = 0;
        foreach ($db->setQuery($q)->loadObjectList() as $s) {
            $days = self::daysUntil($s->expiry_date);
            $offsets = self::reminderDays($s);
            if ($days === null || !$offsets) continue;
            $reached = array_filter($offsets, static fn($o) => $o >= $days);
            if (!$reached) continue;
            $offset = min($reached);
            if (self::queueRenewal($s, sprintf('renewal:%d:%s:%d', $s->id, $s->expiry_date, $offset))) {
                $queued++;
            }
        }
        return $queued;
    }

    /** Scheduled run: reminders, then the queue. */
    public static function run(): array
    {
        $queued = self::queueRenewals();
        [$sent, $failed] = self::processQueue();
        try {
            OfferHelper::saveParam('notify_last_run', self::now());
        } catch (\Throwable $e) {
        }
        return ['queued' => $queued, 'sent' => $sent, 'failed' => $failed];
    }

    public static function sendTest(string $to, string $subject, string $body): bool
    {
        $id = self::queue(['kind' => 'test', 'to_email' => $to, 'subject' => '[ΔΟΚΙΜΗ] ' . $subject, 'body' => $body]);
        return $id > 0 && self::sendNow($id);
    }
}

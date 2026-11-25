<?php
namespace Support\Component\Supportplans\Site\View\Notify;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\Notify;

/** Πελάτες & Ειδοποιήσεις: clients, catalogue, client services, reports, campaigns, log, settings. */
class HtmlView extends BaseHtmlView
{
    public const SECTIONS = [
        'overview'  => 'Επισκόπηση',
        'clients'   => 'Πελάτες',
        'services'  => 'Υπηρεσίες πελατών',
        'catalog'   => 'Κατάλογος υπηρεσιών',
        'reports'   => 'Αναφορές maintenance',
        'log'       => 'Ιστορικό',
        'settings'  => 'Ρυθμίσεις email',
    ];

    /** Ευχές & Αποστολές: a menu item of its own, with these tabs. */
    public const GREET_SECTIONS = [
        'campaigns'     => 'Αποστολές',
        'namedays'      => 'Ονομαστικές εορτές',
        'greetsettings' => 'Ρυθμίσεις ευχών',
    ];

    /** Συντήρηση sites: a menu item of its own, with these tabs. */
    public const SITE_SECTIONS = [
        'sites'        => 'Sites',
        'siteupdates'  => 'Νέες ενημερώσεις',
        'sitedigest'   => 'Μηνιαία σύνοψη',
        'rounds'       => 'Ιστορικό ενημερώσεων',
        'sitesettings' => 'Ρυθμίσεις',
    ];

    public $params;
    /** Συντήρηση sites data. */
    public array $inventory = [];
    public array $planRows = [];
    public array $items = [];
    public array $history = [];
    public array $sitePreview = [];
    public string $section = 'overview';
    public string $mode = 'list';
    public ?object $selected = null;
    public array $rows = [];
    public array $filters = [];
    public array $clients = [];
    public array $catalog = [];
    public array $services = [];
    public array $messages = [];
    public array $counts = [];
    public int $total = 0;
    public int $page = 1;
    public array $preview = [];
    /** service id => ['date' => next reminder, 'size' => services in its group] */
    public array $schedule = [];
    /** Renewals summary of the services page (Notify::summary). */
    public array $summary = [];
    public string $summaryPeriod = 'month';
    public string $summaryFrom = '';
    public string $summaryTo = '';
    /** Reminder groups of the selected client. */
    public array $groups = [];
    /** Payments / renewals of the selected client service. */
    public array $renewals = [];
    /** Client page, manual reminder: services that can be chosen, and the ones selected. */
    public array $remindable = [];
    public array $remindSelected = [];
    public array $remindPreview = [];
    /** Name days of the next 30 days (overview). */
    public array $namedays = [];
    /** New campaign from a ready-made greeting (Notify::campaignPresets). */
    public ?object $preset = null;
    /** Preview of the name day wishes (settings, ?preview=nameday[&client=id]). */
    public array $namedayPreview = [];

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $in = Factory::getApplication()->input;
        $section = $in->getCmd('section', 'overview');
        // 'invoices' (Υπενθυμίσεις πληρωμών) is a menu item of its own, not a tab of Ανανεώσεις.
        $this->section = isset(self::SECTIONS[$section]) || isset(self::GREET_SECTIONS[$section]) || isset(self::SITE_SECTIONS[$section]) || $section === 'invoices' ? $section : 'overview';
        $id = $in->getInt('id', 0);
        $this->mode = $in->getCmd('mode', '') === 'new' ? 'new' : ($id ? 'edit' : 'list');
        $this->counts = Notify::messageCounts();
        Notify::rememberSiteRoot();

        if ($this->section === 'namedays') {
            $this->namedays = Notify::upcomingNamedays(366);
            $this->mode = 'list';
        }
        if ($this->section === 'greetsettings') {
            $this->mode = 'list';
        }
        if ($this->section === 'greetsettings' && $in->getCmd('preview', '') === 'nameday') {
            // A client of the next name days (or the one asked for), else the administrator.
            $client = $in->getInt('client', 0) ? Notify::client($in->getInt('client', 0)) : null;
            if (!$client) {
                $next = Notify::upcomingNamedays(366);
                $client = $next ? $next[0]['client'] : null;
            }
            if (!$client) {
                $s = Factory::getApplication()->getSession();
                $client = (object) ['id' => 0, 'name' => (string) $s->get('supportplans.auth.name', ''), 'company' => '', 'email' => (string) $s->get('supportplans.auth.email', ''), 'salutation' => ''];
            }
            [$subject, $body] = Notify::buildNameday((object) (['id' => 0] + (array) $client));
            $this->namedayPreview = ['to' => Notify::clientLabel($client) . ' <' . $client->email . '>', 'subject' => $subject, 'body' => $body];
        }
        switch ($this->section) {
            case 'overview':
                $this->rows = Notify::services(['status' => 'active', 'expiring' => 60]);
                $this->schedule = Notify::schedule();
                $this->clients = Notify::clients('', 'active');
                $this->catalog = Notify::catalog();
                $this->mode = 'list';
                break;

            case 'clients':
                if ($this->mode === 'list') {
                    $this->filters = ['search' => trim($in->getString('q', '')), 'status' => $in->getCmd('status', '')];
                    $this->rows = Notify::clients($this->filters['search'], $this->filters['status']);
                } elseif ($id) {
                    $this->selected = Notify::client($id);
                    if ($this->selected) {
                        $this->services = Notify::services(['client_id' => $id]);
                        $this->schedule = Notify::schedule($id);
                        $this->groups = Notify::groups(Notify::candidates($id));
                        // Manual reminder: chosen services (posted from the preview button), else the
                        // group of the service the user came from (?remind=ID), else the first group.
                        $this->remindable = Notify::remindable($id);
                        $chosen = array_map('intval', (array) $in->get('rp_ids', [], 'array'));
                        if (!$chosen && ($rid = $in->getInt('remind', 0))) {
                            $chosen = [$rid];
                            foreach (Notify::groups(Notify::candidates($id, false)) as $grp) {
                                $gids = array_map(static fn($x) => (int) $x->id, $grp);
                                if (in_array($rid, $gids, true)) { $chosen = $gids; break; }
                            }
                        }
                        if (!$chosen && $this->groups) {
                            $chosen = array_map(static fn($x) => (int) $x->id, $this->groups[0]);
                        }
                        $valid = array_map(static fn($x) => (int) $x->id, $this->remindable);
                        $this->remindSelected = array_values(array_intersect($chosen, $valid));
                        if ($in->get('rp_ids', null, 'array') !== null && $this->remindSelected) {
                            $list = array_values(array_filter($this->remindable, fn($x) => in_array((int) $x->id, $this->remindSelected, true)));
                            [$subject, $body] = Notify::buildRenewal($list, $this->selected);
                            $this->remindPreview = ['subject' => $subject, 'body' => $body];
                        }
                        $g = $in->getInt('preview', 0);
                        if ($g > 0 && isset($this->groups[$g - 1])) {
                            [$subject, $body] = Notify::buildRenewal($this->groups[$g - 1], $this->selected);
                            $this->preview = ['to' => $this->selected->email, 'subject' => $subject, 'body' => $body, 'group' => $g];
                        }
                        [$this->messages] = Notify::messages(['client_id' => $id], 1, 20);
                    }
                }
                break;

            case 'catalog':
                $this->catalog = Notify::catalog();
                $this->selected = $id ? Notify::catalogItem($id) : null;
                break;

            case 'services':
                $this->catalog = Notify::catalog();
                if ($this->mode === 'list') {
                    $this->filters = [
                        'search' => trim($in->getString('q', '')),
                        'catalog_id' => $in->getInt('catalog_id', 0),
                        'status' => $in->get('status', 'active', 'cmd'),
                        'expiring' => $in->getString('expiring', ''),
                    ];
                    $this->rows = Notify::services($this->filters);
                    $this->schedule = Notify::schedule();
                    // Past working year: the whole year by default; "Τρέχον έτος" = the selected year.
                    $period = $in->getCmd('sp', \Support\Component\Supportplans\Site\Helper\Year::isCurrent() ? 'month' : 'year');
                    $this->summaryPeriod = isset(Notify::SUMMARY_PERIODS[$period]) ? $period : 'month';
                    $this->summaryFrom = Notify::parseDate($in->getString('sf', ''));
                    $this->summaryTo = Notify::parseDate($in->getString('st', ''));
                    if ($this->summaryPeriod === 'year' && !\Support\Component\Supportplans\Site\Helper\Year::isCurrent()) {
                        [$yf, $yt] = \Support\Component\Supportplans\Site\Helper\Year::range();
                        $this->summary = Notify::summary('custom', $yf, $yt);
                    } else {
                        $this->summary = Notify::summary($this->summaryPeriod, $this->summaryFrom, $this->summaryTo);
                    }
                } else {
                    $this->clients = Notify::clients();
                    $this->selected = $id ? Notify::service($id) : null;
                    if ($this->selected) {
                        $this->renewals = Notify::renewals($id);
                    }
                }
                break;

            case 'reports':
                if ($this->mode === 'list') {
                    $this->rows = Notify::reports();
                } else {
                    $this->services = Notify::services(['status' => 'active']);
                    $this->selected = $id ? Notify::report($id) : null;
                    $service = Notify::service((int) ($this->selected->client_service_id ?? 0));
                    $client = $service ? Notify::client((int) $service->client_id) : null;
                    $sent = $this->selected && $this->selected->status === 'sent' ? Notify::messageByRef('report:' . (int) $this->selected->id) : null;
                    if ($sent) {
                        // A sent report shows exactly what the client received.
                        $this->preview = ['to' => $sent->to_email, 'subject' => $sent->subject, 'body' => (string) $sent->body];
                    } elseif ($this->selected && $service && $client) {
                        [$subject, $body] = Notify::buildReport($this->selected, $service, $client);
                        $this->preview = ['to' => $client->email, 'subject' => $subject, 'body' => $body];
                    }
                }
                break;

            case 'campaigns':
                if ($this->mode === 'list') {
                    $this->rows = Notify::campaigns();
                } else {
                    $this->clients = Notify::clients('', 'active');
                    $this->catalog = Notify::catalog();
                    $this->selected = $id ? Notify::campaign($id) : null;
                    $presets = Notify::campaignPresets();
                    $preset = $in->getCmd('preset', '');
                    if (!$this->selected && isset($presets[$preset])) {
                        [, $subject, $body, $date] = $presets[$preset];
                        $this->preset = (object) ['subject' => $subject, 'body' => $body, 'send_date' => $date, 'send_time' => $date !== '' ? '09:30' : ''];
                    }
                    if ($this->selected) {
                        $s = Factory::getApplication()->getSession();
                        $me = (object) ['id' => 0, 'name' => (string) $s->get('supportplans.auth.name', ''), 'company' => '', 'email' => (string) $s->get('supportplans.auth.email', ''), 'status' => 'active'];
                        [$subject, $body] = Notify::buildCampaign($this->selected, $me);
                        $this->preview = ['subject' => $subject, 'body' => $body];
                        $aud = json_decode((string) $this->selected->audience, true) ?: ['mode' => 'all'];
                        $this->total = $this->selected->status === 'draft' ? count(Notify::audience($aud)) : (int) $this->selected->recipients;
                    }
                }
                break;

            case 'invoices':
                if ($this->mode === 'list') {
                    $this->filters = ['status' => $in->getCmd('status', 'open'), 'search' => trim($in->getString('q', ''))];
                    if ($this->filters['status'] === 'all') $this->filters['status'] = '';
                    $this->rows = Notify::invoices($this->filters['status'], $this->filters['search']);
                    $this->clients = Notify::clients('', 'active');
                } else {
                    $this->clients = Notify::clients('', 'active');
                    $this->selected = $id ? Notify::invoice($id) : null;
                    if ($this->selected) $this->messages = Notify::invoiceMessages($id);
                }
                break;

            case 'sites':
                if ($this->mode === 'list') {
                    $this->filters = ['search' => trim($in->getString('q', '')), 'cms' => $in->getCmd('cms', '')];
                    $this->rows = \Support\Component\Supportplans\Site\Helper\Sites::sites($this->filters['search'], $this->filters['cms']);
                } else {
                    $this->clients = Notify::clients();
                    $this->selected = $id ? \Support\Component\Supportplans\Site\Helper\Sites::site($id) : null;
                    if ($this->selected) {
                        $this->items = \Support\Component\Supportplans\Site\Helper\Sites::items($id);
                        $this->history = \Support\Component\Supportplans\Site\Helper\Sites::history($id);
                    }
                }
                break;

            case 'siteupdates':
                $this->inventory = \Support\Component\Supportplans\Site\Helper\Sites::inventory();
                $this->rows = array_values(array_filter(\Support\Component\Supportplans\Site\Helper\Sites::rounds(), static fn($r) => $r->status === 'draft'));
                $this->mode = 'list';
                break;

            case 'rounds':
                if ($this->mode === 'list') {
                    $this->rows = \Support\Component\Supportplans\Site\Helper\Sites::rounds();
                } else {
                    $this->selected = $id ? \Support\Component\Supportplans\Site\Helper\Sites::round($id) : null;
                    if ($this->selected) {
                        $this->planRows = \Support\Component\Supportplans\Site\Helper\Sites::planRows($this->selected);
                        $pv = $in->getInt('preview', -1);
                        [$client, $groups] = \Support\Component\Supportplans\Site\Helper\Sites::rowMessage($this->planRows, $pv);
                        if ($groups) {
                            $client = $client ?: (object) ['id' => 0, 'name' => '', 'company' => '', 'email' => '', 'salutation' => ''];
                            [$subject, $body] = \Support\Component\Supportplans\Site\Helper\Sites::buildMessage($groups, (object) (['id' => 0] + (array) $client));
                            $this->sitePreview = ['i' => $pv, 'to' => $client->email, 'subject' => $subject, 'body' => $body, 'sites' => count($groups)];
                        }
                    }
                }
                break;

            case 'sitedigest':
                $this->mode = 'list';
                $this->rows = \Support\Component\Supportplans\Site\Helper\Sites::pendingByClient();
                $pv = $in->getInt('preview', 0);
                if ($pv && isset($this->rows[$pv]) && $this->rows[$pv]['client']) {
                    $p = $this->rows[$pv];
                    [$subject, $body] = \Support\Component\Supportplans\Site\Helper\Sites::buildMessage($p['groups'], (object) (['id' => 0] + (array) $p['client']),
                        \Support\Component\Supportplans\Site\Helper\Sites::periodLabel($p['from'], $p['to']));
                    $this->sitePreview = ['i' => $pv, 'to' => $p['client']->email, 'subject' => $subject, 'body' => $body];
                }
                break;

            case 'sitesettings':
                $this->mode = 'list';
                $sample = (object) ['domain' => 'example.gr', 'cms' => 'joomla'];
                $first = \Support\Component\Supportplans\Site\Helper\Sites::sites();
                if ($first) $sample = $first[0];
                $client = !empty($sample->client_id) ? Notify::client((int) $sample->client_id) : null;
                $client = $client ?: (object) ['id' => 0, 'name' => '', 'company' => '', 'email' => '', 'salutation' => ''];
                [$subject, $body] = \Support\Component\Supportplans\Site\Helper\Sites::buildMessage([['site' => $sample, 'updates' =>
                    [['n' => 'Joomla', 'o' => '6.2.0', 'v' => '6.2.1'], ['n' => 'YOOtheme Pro', 'o' => '5.5.3', 'v' => '5.5.4'], ['n' => 'Akeeba Backup Pro', 'o' => '10.2.2', 'v' => '10.2.3']]]], (object) (['id' => 0] + (array) $client),
                    \Support\Component\Supportplans\Site\Helper\Sites::MONTHS[(int) date('n')] . ' ' . date('Y'));
                $this->sitePreview = ['to' => $client->email, 'subject' => $subject, 'body' => $body, 'site' => $sample->domain];
                break;

            case 'log':
                if ($id) {
                    $this->selected = Notify::message($id);
                } else {
                    $this->filters = ['kind' => $in->getCmd('kind', ''), 'status' => $in->getCmd('status', ''), 'campaign_id' => $in->getInt('campaign_id', 0),
                        'year' => \Support\Component\Supportplans\Site\Helper\Year::get()];
                    $this->page = max(1, $in->getInt('p', 1));
                    [$this->rows, $this->total] = Notify::messages($this->filters, $this->page, 50);
                }
                break;
        }
        if ($id && !$this->selected && $this->section !== 'overview') {
            Factory::getApplication()->enqueueMessage('Η εγγραφή δεν βρέθηκε.', 'warning');
            Factory::getApplication()->redirect(\Support\Component\Supportplans\Site\Helper\NotifyUi::url('section=' . $this->section, false));
            return;
        }
        parent::display($tpl);
    }
}

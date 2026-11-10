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
        'campaigns' => 'Αποστολές',
        'log'       => 'Ιστορικό',
        'settings'  => 'Ρυθμίσεις email',
    ];

    public $params;
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
    /** Reminder groups of the selected client. */
    public array $groups = [];
    /** Payments / renewals of the selected client service. */
    public array $renewals = [];

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $in = Factory::getApplication()->input;
        $section = $in->getCmd('section', 'overview');
        $this->section = isset(self::SECTIONS[$section]) ? $section : 'overview';
        $id = $in->getInt('id', 0);
        $this->mode = $in->getCmd('mode', '') === 'new' ? 'new' : ($id ? 'edit' : 'list');
        $this->counts = Notify::messageCounts();
        Notify::rememberSiteRoot();

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

            case 'log':
                if ($id) {
                    $this->selected = Notify::message($id);
                } else {
                    $this->filters = ['kind' => $in->getCmd('kind', ''), 'status' => $in->getCmd('status', ''), 'campaign_id' => $in->getInt('campaign_id', 0)];
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

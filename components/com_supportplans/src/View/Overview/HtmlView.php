<?php
namespace Support\Component\Supportplans\Site\View\Overview;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\Documents;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Year;

/** Overview (home page) of the Back Office for the working year. */
class HtmlView extends BaseHtmlView
{
    public $params;
    public int $year;
    public array $docs = [];
    public array $counts = [];
    public array $offers = ['accepted' => 0.0, 'acceptedCount' => 0, 'pending' => 0, 'pendingValue' => 0.0];
    public array $summary = [];
    public array $expiring = [];
    public int $activePlans = 0;
    public int $clients = 0;

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $this->year = Year::get();
        $this->docs = array_values(array_filter(Documents::all(), static fn($r) => Year::inYear($r['date'])));
        $this->counts = array_fill_keys(array_keys(Documents::TYPES), 0);
        foreach ($this->docs as $r) $this->counts[$r['type']]++;

        // Offers of the year: accepted value and pending (draft / sent), before VAT.
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $offers = $db->setQuery($db->getQuery(true)->select('id, status, vat_rate, offer_date')->from($db->quoteName('#__supportplans_offers'))
            ->where('YEAR(offer_date)=' . (int) $this->year))->loadObjectList();
        $items = [];
        if ($offers) {
            $ids = implode(',', array_map(static fn($o) => (int) $o->id, $offers));
            foreach ($db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_items'))->where('offer_id IN (' . $ids . ')'))->loadObjectList() as $it) {
                $items[(int) $it->offer_id][] = $it;
            }
        }
        foreach ($offers as $o) {
            $net = (float) OfferHelper::totals($items[(int) $o->id] ?? [], (float) $o->vat_rate)['net'];
            if ($o->status === 'accepted') {
                $this->offers['accepted'] += $net;
                $this->offers['acceptedCount']++;
            } elseif (in_array($o->status, ['draft', 'sent'], true)) {
                $this->offers['pending']++;
                $this->offers['pendingValue'] += $net;
            }
        }
        $this->activePlans = (int) $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__supportplans_plans'))->where("status='active'"))->loadResult();
        $this->clients = count(Notify::clients('', 'active'));
        if (Year::isCurrent()) {
            $this->summary = Notify::summary('month');
            $this->expiring = array_slice(array_values(array_filter(Notify::services(['status' => 'active', 'expiring' => 30]),
                static fn($s) => $s->expiry_date && Notify::daysUntil($s->expiry_date) >= 0)), 0, 8);
        } else {
            [$from, $to] = Year::range();
            $this->summary = Notify::summary('custom', $from, $to);
        }
        parent::display($tpl);
    }
}

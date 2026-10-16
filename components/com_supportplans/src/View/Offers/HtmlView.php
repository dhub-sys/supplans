<?php
namespace Support\Component\Supportplans\Site\View\Offers;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Offers workspace: offers, offer templates and the services library. */
class HtmlView extends BaseHtmlView
{
    public $params;
    public string $section = 'offers';
    public string $mode = 'list';
    public array $offers = [];
    public array $templates = [];
    public array $services = [];
    public ?object $selected = null;
    public array $items = [];
    public array $totals = [];
    public string $nextNumber = '';
    public array $fields = [];
    public array $undefinedVariables = [];

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $section = $app->input->getCmd('section', 'offers');
        $this->section = in_array($section, ['offers', 'templates', 'services', 'variables'], true) ? $section : 'offers';
        $id = $app->input->getInt('id');
        $this->mode = $app->input->getCmd('mode', $id ? 'edit' : 'list') === 'new' ? 'new' : ($id ? 'edit' : 'list');

        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_services'))->order('ordering ASC, name ASC');
        $this->services = $db->setQuery($q)->loadObjectList();
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_templates'))->order('published DESC, name ASC');
        $this->templates = $db->setQuery($q)->loadObjectList();

        $this->fields = OfferHelper::fields($this->params);
        if ($this->section === 'variables') {
            // Variables written in the library texts that are not defined yet.
            $texts = [];
            foreach ($this->services as $sv) {
                array_push($texts, $sv->section_title, $sv->table_label, $sv->body);
            }
            foreach ($this->templates as $tp) {
                array_push($texts, $tp->doc_title, $tp->doc_subtitle, $tp->footer_title, $tp->pricing_title, $tp->intro_title ?? '', $tp->intro_body ?? '', $tp->payment_text, $tp->terms_title, $tp->terms_body);
            }
            $defined = array_column($this->fields, 'key');
            foreach (OfferHelper::usedVariables($texts) as $key) {
                if (!OfferHelper::isReservedVariable($key) && !in_array($key, $defined, true)) {
                    $this->undefinedVariables[] = $key;
                }
            }
            $this->mode = 'list';
        } elseif ($this->section === 'services') {
            $this->selected = $id ? $this->find($this->services, $id) : null;
        } elseif ($this->section === 'templates') {
            $this->selected = $id ? $this->find($this->templates, $id) : null;
        } else {
            $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offers'))->order('offer_date DESC, id DESC');
            $this->offers = $db->setQuery($q)->loadObjectList();
            $byOffer = [];
            if ($this->offers) {
                $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_items'))->order('ordering ASC, id ASC');
                foreach ($db->setQuery($q)->loadObjectList() as $item) {
                    $byOffer[(int) $item->offer_id][] = $item;
                }
            }
            foreach ($this->offers as $offer) {
                $offer->totals = OfferHelper::totals($byOffer[(int) $offer->id] ?? [], (float) $offer->vat_rate);
            }
            if ($id) {
                $this->selected = $this->find($this->offers, $id);
                if ($this->selected) {
                    $this->items = $byOffer[$id] ?? [];
                    $this->totals = $this->selected->totals;
                }
            }
            if ($this->mode === 'new') {
                $this->nextNumber = OfferHelper::nextNumber($db);
            }
        }
        if ($this->mode === 'edit' && !$this->selected) {
            $app->enqueueMessage('Η εγγραφή δεν βρέθηκε.', 'warning');
            $this->mode = 'list';
        }
        parent::display($tpl);
    }

    private function find(array $rows, int $id): ?object
    {
        foreach ($rows as $row) {
            if ((int) $row->id === $id) {
                return $row;
            }
        }
        return null;
    }
}

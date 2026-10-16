<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\OfferSamples;

/** Offer templates: document texts plus a default selection of services. */
class OffertemplateController extends BaseController
{
    public function apply(): void { $this->store(false); }
    public function save(): void { $this->store(true); }

    private function store(bool $close): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $in = $app->input;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $in->getInt('id');
        $name = trim($in->getString('name'));
        if ($name === '') {
            $app->enqueueMessage('Συμπληρώστε το όνομα του template.', 'error');
            OfferHelper::redirect('view=offers&section=templates' . ($id ? '&id=' . $id : '&mode=new'));
            return;
        }

        // Selected services, in the order of the "order" inputs.
        $items = [];
        foreach ((array) $in->get('tpl', [], 'array') as $serviceId => $cfg) {
            if (!is_array($cfg) || empty($cfg['on'])) {
                continue;
            }
            $items[] = [
                'service_id'       => (int) $serviceId,
                'show_description' => empty($cfg['desc']) ? 0 : 1,
                'in_table'         => empty($cfg['table']) ? 0 : 1,
                'order'            => (int) ($cfg['order'] ?? 0),
            ];
        }
        usort($items, static fn($a, $b) => $a['order'] <=> $b['order']);
        $items = array_map(static function ($i) { unset($i['order']); return $i; }, $items);

        $now = Factory::getDate()->toSql();
        $row = (object) [
            'name'          => $name,
            'doc_title'     => trim(str_replace("\r\n", "\n", (string) $in->get('doc_title', '', 'raw'))),
            'doc_subtitle'  => trim($in->getString('doc_subtitle')),
            'footer_title'  => trim($in->getString('footer_title')),
            'pricing_title' => trim($in->getString('pricing_title')),
            'payment_text'  => str_replace("\r\n", "\n", (string) $in->get('payment_text', '', 'raw')),
            'terms_title'   => trim($in->getString('terms_title')),
            'terms_body'    => str_replace("\r\n", "\n", (string) $in->get('terms_body', '', 'raw')),
            'vat_rate'      => max(0, $in->getFloat('vat_rate', 24)),
            'show_banks'    => $in->getInt('show_banks', 0) ? 1 : 0,
            'show_header'   => $in->getInt('show_header', 0) ? 1 : 0,
            'pricing_break' => $in->getInt('pricing_break', 0) ? 1 : 0,
            'pricing_space' => max(0, min(80, $in->getInt('pricing_space', 5))),
            'terms_break'   => $in->getInt('terms_break', 0) ? 1 : 0,
            'terms_space'   => max(0, min(80, $in->getInt('terms_space', 5))),
            'header_text'   => trim($in->getString('header_text')),
            'items'         => json_encode($items),
            'published'     => $in->getInt('published', 1) ? 1 : 0,
            'modified'      => $now,
        ];
        if ($id > 0) {
            $row->id = $id;
            $db->updateObject('#__supportplans_offer_templates', $row, 'id');
        } else {
            $row->created = $now;
            $db->insertObject('#__supportplans_offer_templates', $row, 'id');
            $id = (int) $row->id;
        }
        $app->enqueueMessage('Το template αποθηκεύτηκε.');
        OfferHelper::redirect('view=offers&section=templates' . ($close ? '' : '&id=' . $id));
    }

    public function duplicate(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_templates'))->where($db->quoteName('id') . '=' . (int) $id);
        $row = $db->setQuery($q)->loadObject();
        if (!$row) {
            OfferHelper::redirect('view=offers&section=templates');
            return;
        }
        unset($row->id);
        $row->name .= ' (αντίγραφο)';
        $row->created = $row->modified = Factory::getDate()->toSql();
        $db->insertObject('#__supportplans_offer_templates', $row, 'id');
        $app->enqueueMessage('Δημιουργήθηκε αντίγραφο του template.');
        OfferHelper::redirect('view=offers&section=templates&id=' . (int) $row->id);
    }

    public function delete(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        if ($id > 0) {
            $q = $db->getQuery(true)->delete($db->quoteName('#__supportplans_offer_templates'))->where($db->quoteName('id') . '=' . (int) $id);
            $db->setQuery($q)->execute();
            $app->enqueueMessage('Το template διαγράφηκε. Οι υπάρχουσες προσφορές δεν επηρεάζονται.');
        }
        OfferHelper::redirect('view=offers&section=templates');
    }

    /** Creates an example template with its services, modelled on a typical web project offer. */
    public function sample(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = OfferSamples::install($db);
        $app->enqueueMessage('Δημιουργήθηκε το παράδειγμα template και οι υπηρεσίες του. Προσαρμόστε τα κείμενα και τις τιμές στις ανάγκες σας.');
        OfferHelper::redirect('view=offers&section=templates&id=' . $id);
    }
}

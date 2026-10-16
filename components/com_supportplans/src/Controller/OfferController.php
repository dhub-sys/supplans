<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/**
 * Customer offers. A new offer is a snapshot of its template and of the selected
 * services, so later changes to the library never alter an issued offer.
 */
class OfferController extends BaseController
{
    private const TEXT_FIELDS = ['doc_title', 'doc_subtitle', 'header_text', 'footer_title', 'pricing_title', 'payment_text', 'terms_title', 'terms_body'];

    public function create(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $in = $app->input;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $customer = trim($in->getString('customer_name'));
        $templateId = $in->getInt('template_id');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_templates'))->where($db->quoteName('id') . '=' . (int) $templateId);
        $template = $templateId ? $db->setQuery($q)->loadObject() : null;
        if ($customer === '' || !$template) {
            $app->enqueueMessage($customer === '' ? 'Συμπληρώστε τον πελάτη.' : 'Επιλέξτε template προσφοράς.', 'error');
            OfferHelper::redirect('view=offers&mode=new');
            return;
        }

        $now = Factory::getDate()->toSql();
        $number = trim($in->getString('offer_number')) ?: OfferHelper::nextNumber($db);
        $offer = (object) [
            'offer_number'  => $number,
            'offer_date'    => $this->date($in->getString('offer_date')),
            'customer_name' => $customer,
            'project_name'  => trim($in->getString('project_name')),
            'template_id'   => (int) $template->id,
            'vat_rate'      => (float) $template->vat_rate,
            'show_banks'    => (int) $template->show_banks,
            'show_header'   => (int) ($template->show_header ?? 1),
            'status'        => 'draft',
            'notes'         => '',
            'variables'     => $this->postedFields(),
            'created'       => $now,
            'modified'      => $now,
        ];
        foreach (self::TEXT_FIELDS as $field) {
            $offer->$field = (string) ($template->$field ?? '');
        }

        $db->transactionStart();
        try {
            $db->insertObject('#__supportplans_offers', $offer, 'id');
            $ordering = 0;
            $selection = json_decode((string) $template->items, true);
            foreach (is_array($selection) ? $selection : [] as $sel) {
                $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_services'))->where($db->quoteName('id') . '=' . (int) ($sel['service_id'] ?? 0));
                $service = $db->setQuery($q)->loadObject();
                if (!$service) {
                    continue;
                }
                $item = (object) [
                    'offer_id'         => (int) $offer->id,
                    'service_id'       => (int) $service->id,
                    'ordering'         => ++$ordering,
                    'show_description' => (int) !empty($sel['show_description']),
                    'in_table'         => (int) !empty($sel['in_table']),
                    'page_break'       => (int) ($service->page_break ?? 0),
                    'space_before'     => (int) ($service->space_before ?? 5),
                    'section_title'    => trim((string) $service->section_title) ?: (string) $service->name,
                    'table_label'      => trim((string) $service->table_label) ?: (string) $service->name,
                    'body'             => (string) $service->body,
                    'price'            => (float) $service->price,
                    'discount'         => 0,
                ];
                $db->insertObject('#__supportplans_offer_items', $item);
            }
            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
        $app->enqueueMessage('Η προσφορά ' . $number . ' δημιουργήθηκε από το template «' . $template->name . '».');
        OfferHelper::redirect('view=offers&id=' . (int) $offer->id);
    }

    public function apply(): void { $this->store(false); }
    public function save(): void { $this->store(true); }

    private function store(bool $close): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $this->input->getInt('id');
        if ($id < 1) {
            OfferHelper::redirect('view=offers');
            return;
        }
        [$offer, $items] = $this->collect($id);
        if ($offer->customer_name === '') {
            $app->enqueueMessage('Συμπληρώστε τον πελάτη.', 'error');
            OfferHelper::redirect('view=offers&id=' . $id);
            return;
        }
        $offer->modified = Factory::getDate()->toSql();

        $db->transactionStart();
        try {
            $db->updateObject('#__supportplans_offers', $offer, 'id');
            $q = $db->getQuery(true)->delete($db->quoteName('#__supportplans_offer_items'))->where($db->quoteName('offer_id') . '=' . (int) $id);
            $db->setQuery($q)->execute();
            foreach ($items as $item) {
                $db->insertObject('#__supportplans_offer_items', $item);
            }
            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
        $app->enqueueMessage('Η προσφορά αποθηκεύτηκε.');
        OfferHelper::redirect('view=offers' . ($close ? '' : '&id=' . $id));
    }

    /** Renders the offer document from the posted (unsaved) form data. Nothing is stored. */
    public function preview(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $id = $this->input->getInt('id');
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offers'))->where($db->quoteName('id') . '=' . (int) $id);
        $stored = $db->setQuery($q)->loadObject();
        if (!$stored) {
            throw new \RuntimeException('Offer not found', 404);
        }
        [$offer, $items] = $this->collect($id);
        foreach ($offer as $key => $value) {
            $stored->$key = $value;
        }
        $this->input->set('tmpl', 'component');
        $view = $this->getView('offerdoc', 'html', 'Site', ['base_path' => $this->basePath, 'layout' => 'default']);
        $view->document = $this->app->getDocument();
        $view->preview = ['offer' => $stored, 'items' => $items];
        $view->display();
    }

    /** Reads the offer edit form into an offer row and its item rows. */
    private function collect(int $id): array
    {
        $in = $this->input;
        $status = $in->getCmd('status', 'draft');
        $offer = (object) [
            'id'            => $id,
            'offer_number'  => trim($in->getString('offer_number')),
            'offer_date'    => $this->date($in->getString('offer_date')),
            'customer_name' => trim($in->getString('customer_name')),
            'project_name'  => trim($in->getString('project_name')),
            'vat_rate'      => max(0, $in->getFloat('vat_rate', 24)),
            'show_banks'    => $in->getInt('show_banks', 0) ? 1 : 0,
            'show_header'   => $in->getInt('show_header', 0) ? 1 : 0,
            'status'        => isset(OfferHelper::STATUSES[$status]) ? $status : 'draft',
            'notes'         => trim((string) $in->get('notes', '', 'raw')),
            'variables'     => $this->postedFields(),
        ];
        foreach (self::TEXT_FIELDS as $field) {
            $value = str_replace("\r\n", "\n", (string) $in->get($field, '', 'raw'));
            $offer->$field = in_array($field, ['doc_subtitle', 'header_text', 'footer_title', 'pricing_title', 'terms_title'], true) ? trim(strip_tags($value)) : $value;
        }

        $items = [];
        foreach ((array) $in->get('items', [], 'array') as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $title = trim(strip_tags((string) ($raw['section_title'] ?? '')));
            $label = trim(strip_tags((string) ($raw['table_label'] ?? '')));
            if ($title === '' && $label === '') {
                continue;
            }
            $price = round((float) str_replace(',', '.', (string) ($raw['price'] ?? 0)), 2);
            $discount = round(abs((float) str_replace(',', '.', (string) ($raw['discount'] ?? 0))), 2);
            $items[] = (object) [
                'offer_id'         => $id,
                'service_id'       => (int) ($raw['service_id'] ?? 0),
                'ordering'         => count($items) + 1,
                'show_description' => empty($raw['show_description']) ? 0 : 1,
                'in_table'         => empty($raw['in_table']) ? 0 : 1,
                'page_break'       => empty($raw['page_break']) ? 0 : 1,
                'space_before'     => max(0, min(80, (int) ($raw['space_before'] ?? 5))),
                'section_title'    => $title !== '' ? $title : $label,
                'table_label'      => $label !== '' ? $label : $title,
                'body'             => str_replace("\r\n", "\n", (string) ($raw['body'] ?? '')),
                'price'            => $price,
                'discount'         => min($discount, abs($price)),
            ];
        }
        return [$offer, $items];
    }

    public function duplicate(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offers'))->where($db->quoteName('id') . '=' . (int) $id);
        $offer = $db->setQuery($q)->loadObject();
        if (!$offer) {
            OfferHelper::redirect('view=offers');
            return;
        }
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_items'))->where($db->quoteName('offer_id') . '=' . (int) $id)->order('ordering ASC, id ASC');
        $items = $db->setQuery($q)->loadObjectList();
        $now = Factory::getDate()->toSql();
        unset($offer->id);
        $offer->offer_number = OfferHelper::nextNumber($db);
        $offer->offer_date = date('Y-m-d');
        $offer->status = 'draft';
        $offer->created = $offer->modified = $now;
        $db->transactionStart();
        try {
            $db->insertObject('#__supportplans_offers', $offer, 'id');
            foreach ($items as $item) {
                unset($item->id);
                $item->offer_id = (int) $offer->id;
                $db->insertObject('#__supportplans_offer_items', $item);
            }
            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        }
        $app->enqueueMessage('Δημιουργήθηκε αντίγραφο με αριθμό ' . $offer->offer_number . '.');
        OfferHelper::redirect('view=offers&id=' . (int) $offer->id);
    }

    public function delete(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        if ($id > 0) {
            $db->transactionStart();
            try {
                $q = $db->getQuery(true)->delete($db->quoteName('#__supportplans_offer_items'))->where($db->quoteName('offer_id') . '=' . (int) $id);
                $db->setQuery($q)->execute();
                $q = $db->getQuery(true)->delete($db->quoteName('#__supportplans_offers'))->where($db->quoteName('id') . '=' . (int) $id);
                $db->setQuery($q)->execute();
                $db->transactionCommit();
            } catch (\Throwable $e) {
                $db->transactionRollback();
                throw $e;
            }
            $app->enqueueMessage('Η προσφορά διαγράφηκε.');
        }
        OfferHelper::redirect('view=offers');
    }

    /** Offer field values posted as field[key] => value, stored as JSON [{key, value}]. */
    private function postedFields(): string
    {
        $posted = (array) $this->input->get('field', [], 'array');
        return json_encode(OfferHelper::customVariables(null, array_map('strval', array_keys($posted)), array_values($posted)), JSON_UNESCAPED_UNICODE);
    }

    private function date(string $value): string
    {
        $value = trim($value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : date('Y-m-d');
    }
}

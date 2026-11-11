<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Mail\MailHelper;
use Support\Component\Supportplans\Site\Helper\Activity;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\ShareLink;

/**
 * Customer offers. A new offer is a snapshot of its template and of the selected
 * services, so later changes to the library never alter an issued offer.
 */
class OfferController extends BaseController
{
    private const TEXT_FIELDS = ['doc_title', 'doc_subtitle', 'header_text', 'footer_title', 'intro_title', 'intro_body'];

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
            'header_logo'   => (int) ($template->header_logo ?? 28),
            'header_gap'    => (int) ($template->header_gap ?? 6),
            'intro_lists'   => (string) ($template->intro_lists ?? ''),
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
                $kind = OfferHelper::kind($service->kind ?? 'service');
                $item = (object) [
                    'offer_id'         => (int) $offer->id,
                    'service_id'       => (int) $service->id,
                    'kind'             => $kind,
                    'ordering'         => ++$ordering,
                    'show_description' => $kind === 'pricing' ? 1 : (int) !empty($sel['show_description']),
                    'in_table'         => $kind === 'service' ? (int) !empty($sel['in_table']) : 0,
                    'page_break'       => (int) ($service->page_break ?? 0),
                    'space_before'     => (int) ($service->space_before ?? 5),
                    'section_title'    => trim((string) $service->section_title) ?: ($kind === 'pricing' ? OfferHelper::PRICING_TITLE : (string) $service->name),
                    'table_label'      => trim((string) $service->table_label) ?: (string) $service->name,
                    'body'             => (string) $service->body,
                    'lists'            => (string) ($service->lists ?? ''),
                    'price'            => $kind === 'service' ? (float) $service->price : 0,
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
            'header_logo'   => max(12, min(120, $in->getInt('header_logo', 28))),
            'header_gap'    => max(0, min(40, $in->getInt('header_gap', 6))),
            'intro_lists'   => json_encode(OfferHelper::lists((array) $in->get('intro_lists', [], 'array')), JSON_UNESCAPED_UNICODE),
            'status'        => isset(OfferHelper::STATUSES[$status]) ? $status : 'draft',
            'notes'         => trim((string) $in->get('notes', '', 'raw')),
            'variables'     => $this->postedFields(),
        ];
        foreach (self::TEXT_FIELDS as $field) {
            $value = str_replace("\r\n", "\n", (string) $in->get($field, '', 'raw'));
            $offer->$field = in_array($field, ['doc_subtitle', 'header_text', 'footer_title', 'intro_title'], true) ? trim(strip_tags($value)) : $value;
        }

        $items = [];
        foreach ((array) $in->get('items', [], 'array') as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $title = trim(strip_tags((string) ($raw['section_title'] ?? '')));
            $label = trim(strip_tags((string) ($raw['table_label'] ?? '')));
            $kind = OfferHelper::kind($raw['kind'] ?? 'service');
            if ($kind === 'pricing' && $title === '') {
                $title = OfferHelper::PRICING_TITLE;
            }
            if ($title === '' && $label === '') {
                continue;
            }
            $price = $kind === 'service' ? round((float) str_replace(',', '.', (string) ($raw['price'] ?? 0)), 2) : 0.0;
            $discount = $kind === 'service' ? round(abs((float) str_replace(',', '.', (string) ($raw['discount'] ?? 0))), 2) : 0.0;
            $items[] = (object) [
                'offer_id'         => $id,
                'service_id'       => (int) ($raw['service_id'] ?? 0),
                'kind'             => $kind,
                'ordering'         => count($items) + 1,
                'show_description' => $kind === 'pricing' || !empty($raw['show_description']) ? 1 : 0,
                'in_table'         => $kind === 'service' && !empty($raw['in_table']) ? 1 : 0,
                'page_break'       => empty($raw['page_break']) ? 0 : 1,
                'space_before'     => max(0, min(80, (int) ($raw['space_before'] ?? 5))),
                'section_title'    => $title !== '' ? $title : $label,
                'table_label'      => $label !== '' ? $label : $title,
                'body'             => str_replace("\r\n", "\n", (string) ($raw['body'] ?? '')),
                'lists'            => json_encode(OfferHelper::lists($raw['lists'] ?? []), JSON_UNESCAPED_UNICODE),
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

    /**
     * Sends the offer by email: the message, a private link to view / save the offer, and / or
     * the offer Pdf attached. A draft offer becomes "sent". Every send is kept in the offer's history.
     */
    public function send(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $this->input->getInt('id');
        $offer = $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offers'))->where('id=' . (int) $id))->loadObject();
        if (!$offer) {
            OfferHelper::redirect('view=offers');
            return;
        }
        $back = 'view=offers&id=' . (int) $id . '#sp-sends';
        $fail = static function (string $msg) use ($app, $back) {
            $app->enqueueMessage($msg, 'error');
            OfferHelper::redirect($back);
        };
        $to = self::emails($this->input->getString('to', ''));
        $cc = self::emails($this->input->getString('cc', ''));
        if ($to === null || !$to) {
            $fail('Συμπληρώστε έγκυρο email παραλήπτη (για περισσότερους, χωρίστε με κόμμα).');
            return;
        }
        if ($cc === null) {
            $fail('Ένα από τα email της κοινοποίησης δεν είναι έγκυρο.');
            return;
        }
        $withLink = $this->input->getInt('with_link', 0) === 1;
        // Attached Pdf (saved from "Εκτύπωση / Pdf"): only real Pdf files, up to 15 MB.
        $file = $this->input->files->get('attachment', [], 'raw');
        $attachment = null;
        if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $tmp = (string) ($file['tmp_name'] ?? '');
            if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)) {
                $fail('Το αρχείο Pdf δεν ανέβηκε (ίσως είναι πολύ μεγάλο).');
                return;
            }
            if ((int) $file['size'] > 15 * 1024 * 1024 || file_get_contents($tmp, false, null, 0, 5) !== '%PDF-') {
                $fail('Το συνημμένο πρέπει να είναι αρχείο Pdf έως 15 MB.');
                return;
            }
            $name = trim(preg_replace('/[^\p{L}\p{N} ._()-]+/u', '', (string) pathinfo((string) $file['name'], PATHINFO_FILENAME)));
            if ($name === '') {
                $name = trim($offer->offer_number . ' - ' . ($offer->project_name ?: $offer->customer_name), ' -');
            }
            $attachment = ['path' => $tmp, 'name' => mb_substr($name, 0, 120) . '.pdf'];
        }
        if (!$withLink && !$attachment) {
            $fail('Επιλέξτε σύνδεσμο προβολής ή/και επισυνάψτε το Pdf της προσφοράς.');
            return;
        }
        $brand = OfferHelper::brand();
        $vars = OfferHelper::offerVars($offer, $brand);
        $subject = trim(preg_replace('/[\r\n]+/', ' ', OfferHelper::vars($this->input->getString('subject', ''), $vars)));
        if ($subject === '') {
            $subject = trim('Οικονομική προσφορά ' . $offer->offer_number . ($offer->project_name !== '' ? ' – ' . $offer->project_name : ''));
        }
        $message = str_replace("\r\n", "\n", trim((string) $this->input->get('message', '', 'raw')));
        $link = '';
        if ($withLink) {
            $token = (string) ($offer->share_token ?? '');
            if (!ShareLink::isToken($token)) {
                $token = ShareLink::newToken();
                $db->setQuery($db->getQuery(true)->update($db->quoteName('#__supportplans_offers'))->set($db->quoteName('share_token') . '=' . $db->quote($token))->where('id=' . (int) $id))->execute();
            }
            $link = ShareLink::offerUrl($token);
        }
        $esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $accent = $esc($brand['accent'] ?: '#367d22');
        $inner = OfferHelper::render($message, $vars);
        if ($link !== '') {
            $inner .= '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0 8px"><tr><td style="background:' . $accent . ';border-radius:8px;">'
                . '<a href="' . $esc($link) . '" style="display:inline-block;padding:12px 22px;color:' . $esc($brand['secondary'] ?: '#ffffff') . ';text-decoration:none;font-weight:bold;">Προβολή της προσφοράς</a></td></tr></table>'
                . '<p style="margin:0 0 6px;font-size:13px;color:#6b7280;">Από τη σελίδα της προσφοράς μπορείτε να την εκτυπώσετε ή να την αποθηκεύσετε σε Pdf. Ο σύνδεσμος είναι προσωπικός.</p>';
        }
        if ($attachment) {
            $inner .= '<p style="margin:10px 0 0;font-size:13px;color:#6b7280;">Συνημμένο: ' . $esc($attachment['name']) . '</p>';
        }
        $body = Notify::wrap($inner);
        try {
            $mailer = Notify::senderMailer();
            foreach ($to as $addr) $mailer->addRecipient($addr);
            foreach ($cc as $addr) $mailer->addCc($addr);
            $mailer->setSubject($subject);
            $mailer->isHtml(true);
            $mailer->setBody($body);
            $mailer->AltBody = Notify::toText($body) . ($link !== '' ? "\n\n" . $link : '');
            if ($attachment) {
                $mailer->addAttachment($attachment['path'], $attachment['name'], 'base64', 'application/pdf');
            }
            $ok = $mailer->Send();
            if ($ok !== true) {
                throw new \RuntimeException((string) ($mailer->ErrorInfo ?? '') ?: 'Η αποστολή απέτυχε.');
            }
        } catch (\Throwable $e) {
            $fail('Το email δεν στάλθηκε: ' . $e->getMessage());
            return;
        }
        $user = Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById((int) $app->getSession()->get('supportplans.auth.user_id', 0));
        $log = (object) [
            'offer_id' => (int) $id, 'to_email' => mb_substr(implode(', ', $to), 0, 255), 'cc' => mb_substr(implode(', ', $cc), 0, 500),
            'subject' => mb_substr($subject, 0, 255), 'attachment' => $attachment ? mb_substr($attachment['name'], 0, 255) : '',
            'with_link' => $link !== '' ? 1 : 0, 'username' => (string) ($user->username ?? ''), 'sent' => Factory::getDate()->toSql(),
        ];
        $db->insertObject('#__supportplans_offer_sends', $log);
        if ($offer->status === 'draft') {
            $db->setQuery($db->getQuery(true)->update($db->quoteName('#__supportplans_offers'))->set($db->quoteName('status') . '=' . $db->quote('sent'))->where('id=' . (int) $id))->execute();
        }
        Activity::note('Προς ' . implode(', ', $to) . ($cc ? ' (κοιν. ' . implode(', ', $cc) . ')' : '') . ($attachment ? ' · Pdf' : '') . ($link !== '' ? ' · σύνδεσμος' : ''));
        $app->enqueueMessage('Η προσφορά στάλθηκε στο ' . implode(', ', $to) . '.');
        OfferHelper::redirect($back);
    }

    /** Disables the private link of the offer (links already sent stop working). */
    public function revokelink(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $id = $this->input->getInt('id');
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->update($db->quoteName('#__supportplans_offers'))->set($db->quoteName('share_token') . "=''")->where('id=' . (int) $id))->execute();
        Factory::getApplication()->enqueueMessage('Ο σύνδεσμος προβολής απενεργοποιήθηκε. Με νέα αποστολή δημιουργείται νέος.');
        OfferHelper::redirect('view=offers&id=' . (int) $id . '#sp-sends');
    }

    /** "a@x.gr, b@y.gr" -> list; null when one of them is not valid. */
    private static function emails(string $raw): ?array
    {
        $out = [];
        foreach (preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) as $addr) {
            if (!MailHelper::isEmailAddress($addr)) return null;
            $out[] = $addr;
        }
        return array_values(array_unique(array_slice($out, 0, 10)));
    }
}

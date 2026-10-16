<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Offer fields (variables such as {intro_service}) that every offer can fill in. */
class OfferfieldController extends BaseController
{
    public function save(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $in = $this->input;
        $labels = (array) $in->get('field_label', [], 'array');
        $keys = (array) $in->get('field_key', [], 'array');
        $types = (array) $in->get('field_type', [], 'array');
        $defaults = (array) $in->get('field_default', [], 'array');

        $fields = [];
        $skipped = [];
        foreach ($keys as $i => $rawKey) {
            $label = trim(strip_tags((string) ($labels[$i] ?? '')));
            $key = OfferHelper::variableKey((string) $rawKey !== '' ? (string) $rawKey : $label);
            if ($key === '') {
                continue;
            }
            if (OfferHelper::isReservedVariable($key) || isset($fields[$key])) {
                $skipped[] = '{' . $key . '}';
                continue;
            }
            $fields[$key] = [
                'key'     => $key,
                'label'   => $label !== '' ? $label : OfferHelper::labelFromKey($key),
                'type'    => ($types[$i] ?? '') === 'textarea' ? 'textarea' : 'text',
                'default' => trim(str_replace(["\r\n", "\r"], "\n", (string) ($defaults[$i] ?? ''))),
            ];
        }
        OfferHelper::saveParam('offer_fields', json_encode(array_values($fields), JSON_UNESCAPED_UNICODE));
        if ($skipped) {
            $app->enqueueMessage('Παραλείφθηκαν (υπάρχουν ήδη ή είναι βασικές μεταβλητές): ' . implode(', ', array_unique($skipped)), 'warning');
        }
        $app->enqueueMessage('Οι μεταβλητές αποθηκεύτηκαν. Εμφανίζονται ως πεδία σε κάθε προσφορά.');
        OfferHelper::redirect('view=offers&section=variables');
    }
}

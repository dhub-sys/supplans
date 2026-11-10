<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Support\Component\Supportplans\Site\Helper\Activity;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\Xlsx;
use Support\Component\Supportplans\Site\View\Activity\HtmlView;

/** Export of the activity log to Excel, with the filters of the page (signed-in users only). */
class ActivityController extends BaseController
{
    public function export(): void
    {
        $app = Factory::getApplication();
        $filters = HtmlView::filters($this->input);
        [$rows] = Activity::search($filters, 1, 0, true);
        $sheet = [['Ημερομηνία', 'Ώρα', 'Χρήστης', 'IP', 'Ενότητα', 'Ενέργεια', 'Στοιχείο', 'Αποτέλεσμα', 'Μήνυμα', 'Λεπτομέρειες']];
        foreach ($rows as $r) {
            $local = Notify::datetimeGr($r->created);
            [$d, $t] = array_pad(explode(' ', $local, 2), 2, '');
            $details = '';
            foreach ((array) json_decode((string) $r->details, true) as $k => $v) {
                $details .= ($details !== '' ? ', ' : '') . $k . '=' . (is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE));
            }
            $sheet[] = [$d, $t, (string) $r->username, (string) $r->ip, (string) $r->area, (string) $r->action, (string) $r->item,
                Activity::RESULTS[$r->result] ?? (string) $r->result, (string) $r->message, $details];
        }
        $filterText = [['Φίλτρα εξαγωγής'], ['Από: ' . ($filters['from'] ?: '—') . ' · Έως: ' . ($filters['to'] ?: '—')],
            ['Ενότητα: ' . ($filters['area'] ?: 'όλες') . ' · Χρήστης: ' . ($filters['user'] ?: 'όλοι') . ' · Αποτέλεσμα: ' . (Activity::RESULTS[$filters['result']] ?? 'όλα')],
            ['Αναζήτηση: ' . ($filters['q'] ?: '—')], ['Εγγραφές: ' . (count($sheet) - 1) . (count($sheet) - 1 >= 20000 ? ' (μέγιστο 20.000· στενέψτε τα φίλτρα)' : '')],
            ['Εξαγωγή: ' . Notify::datetimeGr(gmdate('Y-m-d H:i:s'))]];
        if (!Xlsx::available()) {
            $app->enqueueMessage('Ο server δεν υποστηρίζει αρχεία Excel (λείπει η επέκταση PHP zip).', 'error');
            $app->redirect(\Support\Component\Supportplans\Site\Helper\OfferHelper::url('view=activity', false));
            return;
        }
        $data = Xlsx::build([
            ['name' => 'Ιστορικό', 'rows' => $sheet, 'widths' => [12, 8, 18, 15, 22, 34, 42, 14, 60, 50]],
            ['name' => 'Φίλτρα', 'rows' => $filterText, 'widths' => [90]],
        ]);
        $app->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="istoriko-energeion-' . Notify::today() . '.xlsx"', true);
        $app->setHeader('Content-Length', (string) strlen($data), true);
        $app->setHeader('Cache-Control', 'no-store', true);
        $app->sendHeaders();
        echo $data;
        $app->close();
    }
}

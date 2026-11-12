<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Xlsx;
use Support\Component\Supportplans\Site\Helper\Year;

/** Renewals report of the overview (payments, expiries, overdue) as Excel. */
class OverviewController extends BaseController
{
    /** Filters of the report: [period, from, to, parts]. Shared with the printable view. */
    public static function filters($in): array
    {
        $period = $in->getCmd('sp', 'month');
        $period = isset(Notify::SUMMARY_PERIODS[$period]) ? $period : 'month';
        $from = Notify::parseDate($in->getString('sf', ''));
        $to = Notify::parseDate($in->getString('st', ''));
        if ($period === 'year' && !Year::isCurrent()) {
            [$from, $to] = Year::range();
            $period = 'custom';
        }
        $parts = array_values(array_intersect((array) $in->get('parts', ['paid', 'due', 'overdue'], 'array'), ['paid', 'due', 'overdue']));
        return [$period, $from, $to, $parts ?: ['paid', 'due', 'overdue']];
    }

    public static function label(string $period, string $from, string $to): string
    {
        return $period === 'custom' && $from !== '' && $to !== '' ? Notify::dateGr($from) . ' – ' . Notify::dateGr($to) : (Notify::SUMMARY_PERIODS[$period] ?? '');
    }

    public function export(): void
    {
        $app = Factory::getApplication();
        [$period, $from, $to, $parts] = self::filters($this->input);
        $d = Notify::summaryRows($period, $from, $to);
        $client = static fn($r) => trim((string) ($r->client_name ?: $r->client_company)) ?: (string) $r->client_email;
        $sum = static fn(array $rows, string $k) => round(array_sum(array_map(static fn($r) => (float) $r->$k, $rows)), 2);
        $sheets = [];
        $summary = [['Αναφορά ανανεώσεων', ''], ['Περίοδος', self::label($period, $from, $to)], ['Εξαγωγή', Notify::datetimeGr(gmdate('Y-m-d H:i:s'))], ['', '']];
        if (in_array('paid', $parts, true)) {
            $summary[] = ['Πληρώθηκαν (' . Notify::dateGr($d['paidRange'][0]) . ' – ' . Notify::dateGr($d['paidRange'][1]) . ')', count($d['paid']) . ' πληρωμές · ' . Notify::money($sum($d['paid'], 'gross')) . ' με ΦΠΑ'];
            $rows = [['Ημ/νία πληρωμής', 'Πελάτης', 'Email', 'Υπηρεσία', 'Domain / περιγραφή', 'Λήξη πριν', 'Νέα λήξη', 'Ποσό (με ΦΠΑ)', 'Σημείωση']];
            foreach ($d['paid'] as $r) {
                $rows[] = [(string) $r->paid_on, $client($r), (string) $r->client_email, (string) $r->service_name, (string) $r->label, (string) $r->old_expiry, (string) $r->new_expiry, (float) $r->gross, (string) $r->note];
            }
            $rows[] = ['Σύνολο', '', '', '', '', '', '', $sum($d['paid'], 'gross'), ''];
            $sheets[] = ['name' => 'Πληρωμές', 'rows' => $rows, 'widths' => [14, 28, 30, 30, 26, 12, 12, 14, 40], 'dateCols' => [0, 5, 6]];
        }
        foreach (['due' => ['Προς πληρωμή', 'Λήγουν'], 'overdue' => ['Ληγμένες απλήρωτες', 'Ληγμένες']] as $k => [$title, $sheet]) {
            if (!in_array($k, $parts, true)) continue;
            $summary[] = [$title . ($k === 'due' ? ' (' . Notify::dateGr($d['dueRange'][0]) . ' – ' . Notify::dateGr($d['dueRange'][1]) . ')' : ''),
                count($d[$k]) . ' υπηρεσίες · ' . Notify::money($sum($d[$k], 'net')) . ' προ ΦΠΑ · ' . Notify::money($sum($d[$k], 'gross')) . ' με ΦΠΑ'];
            $rows = [['Λήξη', 'Πελάτης', 'Email', 'Υπηρεσία', 'Domain / περιγραφή', 'Τιμή (προ ΦΠΑ)', 'Με ΦΠΑ', $k === 'overdue' ? 'Ημέρες από τη λήξη' : 'Διάρκεια (μήνες)']];
            foreach ($d[$k] as $r) {
                $rows[] = [(string) $r->expiry_date, $client($r), (string) $r->client_email, (string) $r->service_name, (string) $r->label, (float) $r->net, (float) $r->gross, $k === 'overdue' ? (int) $r->days : (int) $r->duration_months];
            }
            $rows[] = ['Σύνολο', '', '', '', '', $sum($d[$k], 'net'), $sum($d[$k], 'gross'), ''];
            $sheets[] = ['name' => $sheet, 'rows' => $rows, 'widths' => [12, 28, 30, 30, 26, 14, 12, 14], 'dateCols' => [0]];
        }
        array_unshift($sheets, ['name' => 'Σύνοψη', 'rows' => $summary, 'widths' => [52, 60]]);
        if (!Xlsx::available()) {
            $app->enqueueMessage('Ο server δεν υποστηρίζει αρχεία Excel (λείπει η επέκταση PHP zip).', 'error');
            OfferHelper::redirect('view=overview');
            return;
        }
        $data = Xlsx::build($sheets);
        $app->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true);
        $app->setHeader('Content-Disposition', 'attachment; filename="ananeoseis-' . Notify::today() . '.xlsx"', true);
        $app->setHeader('Content-Length', (string) strlen($data), true);
        $app->setHeader('Cache-Control', 'no-store', true);
        $app->sendHeaders();
        echo $data;
        $app->close();
    }
}

<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * All the documents of the Dashboard (packages, offers, structure proposals) in one register,
 * with one shared numbering: a new document takes the number after the last one of any type.
 */
final class Documents
{
    public const TYPES = ['plan' => 'Πακέτο', 'offer' => 'Προσφορά', 'structure' => 'Πρόταση δομής'];

    /** [table, number column] of every document type. */
    private const SOURCES = [
        'plan' => ['#__supportplans_plans', 'form_number'],
        'offer' => ['#__supportplans_offers', 'offer_number'],
        'structure' => ['#__supportplans_structures', 'number'],
    ];

    /** Every document number in use. */
    private static function numbers(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $all = [];
        foreach (self::SOURCES as [$table, $col]) {
            try {
                $all = array_merge($all, (array) $db->setQuery($db->getQuery(true)->select($db->quoteName($col))->from($db->quoteName($table)))->loadColumn());
            } catch (\Throwable $e) {
                // table not there yet (during an update)
            }
        }
        return array_values(array_filter(array_map(static fn($n) => trim((string) $n), $all), 'strlen'));
    }

    /**
     * The number after the highest one in use (its prefix and zero padding are kept, e.g.
     * "26014" -> "26015", "Π-0099" -> "Π-0100"). The year numbering of the offers ("26001" = year
     * 2026, no. 001) starts again with the new year: after "26087", in 2027 the next is "27001".
     */
    public static function nextNumber(): string
    {
        $yy = date('y');
        $best = null;
        foreach (self::numbers() as $n) {
            if (!preg_match('/^(.*?)(\d+)$/u', $n, $m)) continue;
            $value = (int) ltrim($m[2], '0');
            if ($best === null || $value > $best['value']) {
                $best = ['prefix' => $m[1], 'digits' => $m[2], 'value' => $value];
            }
        }
        if ($best === null) {
            return $yy . '001';
        }
        // yy + 3 digits of an earlier year
        if ($best['prefix'] === '' && strlen($best['digits']) === 5 && (int) substr($best['digits'], 0, 2) < (int) $yy && (int) substr($best['digits'], 0, 2) >= 20) {
            return $yy . '001';
        }
        return $best['prefix'] . str_pad((string) ($best['value'] + 1), strlen($best['digits']), '0', STR_PAD_LEFT);
    }

    /** Numeric value of a number for sorting ("26014" -> 26014, "Π-12" -> 12). */
    public static function sortValue(string $number): int
    {
        return preg_match('/(\d+)$/', trim($number), $m) ? (int) $m[1] : -1;
    }

    /**
     * All documents, newest number first: [type, id, number, date, customer, title, status, statusLabel, open, pdf].
     */
    public static function all(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $rows = [];
        foreach ($db->setQuery($db->getQuery(true)->select('id, sequence_no, form_number, purchase_date, customer_name, plan_type, supported_websites, status')->from($db->quoteName('#__supportplans_plans')))->loadObjectList() as $p) {
            // Packages created before the shared numbering may have no number: their sequence then.
            $rows[] = ['type' => 'plan', 'id' => (int) $p->id, 'number' => trim((string) $p->form_number) ?: (string) $p->sequence_no, 'date' => (string) $p->purchase_date,
                'customer' => (string) $p->customer_name, 'title' => trim((string) $p->plan_type . ((string) $p->supported_websites !== '' ? ' · ' . $p->supported_websites : ''), ' ·'),
                'status' => $p->status === 'completed' ? 'accepted' : 'sent', 'statusLabel' => $p->status === 'completed' ? 'Ολοκληρωμένο' : 'Ενεργό',
                'open' => 'view=plans&plan_id=' . (int) $p->id, 'pdf' => 'view=report&plan_id=' . (int) $p->id];
        }
        foreach ($db->setQuery($db->getQuery(true)->select('id, offer_number, offer_date, customer_name, project_name, status')->from($db->quoteName('#__supportplans_offers')))->loadObjectList() as $o) {
            $rows[] = ['type' => 'offer', 'id' => (int) $o->id, 'number' => trim((string) $o->offer_number), 'date' => (string) $o->offer_date,
                'customer' => (string) $o->customer_name, 'title' => (string) $o->project_name, 'status' => (string) $o->status, 'statusLabel' => OfferHelper::statusLabel((string) $o->status),
                'open' => 'view=offers&id=' . (int) $o->id, 'pdf' => 'view=offerdoc&id=' . (int) $o->id];
        }
        foreach ($db->setQuery($db->getQuery(true)->select('id, number, proposal_date, customer_name, project_name, status')->from($db->quoteName(Structure::T)))->loadObjectList() as $s) {
            $rows[] = ['type' => 'structure', 'id' => (int) $s->id, 'number' => trim((string) $s->number), 'date' => (string) $s->proposal_date,
                'customer' => (string) $s->customer_name, 'title' => (string) $s->project_name, 'status' => (string) $s->status, 'statusLabel' => OfferHelper::statusLabel((string) $s->status),
                'open' => 'view=structures&id=' . (int) $s->id, 'pdf' => 'view=structuredoc&id=' . (int) $s->id];
        }
        usort($rows, static function ($a, $b) {
            return [self::sortValue($b['number']), $b['date'], $b['id']] <=> [self::sortValue($a['number']), $a['date'], $a['id']];
        });
        return $rows;
    }
}

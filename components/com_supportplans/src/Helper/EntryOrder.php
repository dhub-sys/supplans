<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Chronological order of the work entries.
 *
 * The request date is free text ("03/08/2026", "2026-08-03", "Ιούνιος 2025" …), so ordering it as
 * text in SQL sorts by day of month (03/08 before 09/07). Dates are parsed here instead; entries
 * whose date cannot be read fall back to their creation time.
 */
final class EntryOrder
{
    private const MONTHS = [
        'ιαν' => 1, 'φεβ' => 2, 'μαρ' => 3, 'απρ' => 4, 'μαι' => 5, 'ιουν' => 6,
        'ιουλ' => 7, 'αυγ' => 8, 'σεπ' => 9, 'οκτ' => 10, 'νοε' => 11, 'δεκ' => 12,
    ];

    public static function sort(array $entries): array
    {
        $keyed = [];
        foreach ($entries as $i => $entry) {
            $keyed[] = [self::key($entry), (int) ($entry->id ?? $i), $entry];
        }
        usort($keyed, static fn($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);
        return array_column($keyed, 2);
    }

    /** Sortable "YYYYMMDD" for the request date, else the creation date. */
    public static function key(object $entry): string
    {
        $text = trim((string) ($entry->request_date ?? ''));
        if (preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{2,4})/', $text, $m)) {
            return self::ymd((int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $text, $m)) {
            return self::ymd((int) $m[1], (int) $m[2], (int) $m[3]);
        }
        if (preg_match('/^(\d{1,2})[\/.\-](\d{4})$/', $text, $m)) {
            return self::ymd((int) $m[2], (int) $m[1], 1);
        }
        if (preg_match('/(\d{4})/', $text, $y)) {
            $plain = strtr(mb_strtolower($text), ['ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ϊ' => 'ι', 'ΐ' => 'ι', 'ό' => 'ο', 'ύ' => 'υ', 'ϋ' => 'υ', 'ΰ' => 'υ', 'ώ' => 'ω']);
            foreach (self::MONTHS as $prefix => $month) {
                if (mb_strpos($plain, $prefix) !== false) {
                    return self::ymd((int) $y[1], $month, 1);
                }
            }
        }
        $created = (string) ($entry->created ?? '');
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $created, $m) ? $m[1] . $m[2] . $m[3] : '99999999';
    }

    private static function ymd(int $y, int $m, int $d): string
    {
        if ($y < 100) {
            $y += 2000;
        }
        return sprintf('%04d%02d%02d', $y, max(1, min(12, $m)), max(1, min(31, $d)));
    }
}

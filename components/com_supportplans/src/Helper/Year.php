<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Working year ("Χρήση") of the Dashboard. Every year is a fresh start for the documents and
 * the figures (offers, structure proposals, packages, activity log, sent emails, summaries),
 * while clients, services and the catalogue are shared by all years. The year is chosen in the
 * sidebar (?year=2026) and kept in the session; the current calendar year by default.
 * The numbering already starts again every year (26001 → 27001, see Documents::nextNumber()).
 */
final class Year
{
    private const KEY = 'supportplans.year';

    public static function current(): int
    {
        return (int) date('Y');
    }

    /** The selected year (also takes ?year= from the request). */
    public static function get(): int
    {
        $app = Factory::getApplication();
        $session = $app->getSession();
        $req = $app->input->getInt('year', 0);
        if ($req >= 2000 && $req <= 2100) {
            $session->set(self::KEY, $req);
            return $req;
        }
        $y = (int) $session->get(self::KEY, 0);
        return $y >= 2000 && $y <= 2100 ? $y : self::current();
    }

    public static function isCurrent(): bool
    {
        return self::get() === self::current();
    }

    /** ['2026-01-01', '2026-12-31'] */
    public static function range(?int $year = null): array
    {
        $y = $year ?? self::get();
        return [$y . '-01-01', $y . '-12-31'];
    }

    public static function inYear(?string $date, ?int $year = null): bool
    {
        return $date !== null && $date !== '' && (int) substr($date, 0, 4) === ($year ?? self::get());
    }

    /** Years that have documents or activity, plus the current one (newest first). */
    public static function all(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $years = [self::current() => true];
        $sources = [['#__supportplans_offers', 'offer_date'], ['#__supportplans_structures', 'proposal_date'], ['#__supportplans_plans', 'purchase_date'],
            ['#__supportplans_activity', 'created'], ['#__supportplans_renewals', 'paid_on']];
        foreach ($sources as [$table, $col]) {
            try {
                foreach ((array) $db->setQuery('SELECT DISTINCT YEAR(' . $db->quoteName($col) . ') FROM ' . $db->quoteName($table) . ' WHERE ' . $db->quoteName($col) . ' IS NOT NULL')->loadColumn() as $y) {
                    if ((int) $y >= 2000 && (int) $y <= 2100) $years[(int) $y] = true;
                }
            } catch (\Throwable $e) {
            }
        }
        krsort($years);
        return array_keys($years);
    }

    /** Card of the overview with links to the previous years (and back to the current one). */
    public static function links(): string
    {
        $sel = self::get();
        $cur = self::current();
        $years = array_values(array_filter(self::all(), static fn($y) => $y !== $cur));
        if (!$years) {
            return '';
        }
        $url = static fn(int $y) => htmlspecialchars(OfferHelper::url('view=overview&year=' . $y, false), ENT_QUOTES, 'UTF-8');
        $html = '<div class="sp-card sp-ov-years"><div class="sp-title-row"><h2>Προηγούμενες χρονιές</h2>'
            . ($sel !== $cur ? '<a class="sp-doc-btn is-primary" href="' . $url($cur) . '">← Επιστροφή στο ' . $cur . '</a>' : '') . '</div>'
            . '<p class="sp-foot">Έγγραφα, προσφορές, ιστορικό ενεργειών, email και συνόψεις κάθε χρονιάς. Πελάτες, υπηρεσίες και κατάλογος είναι κοινά για όλα τα έτη.</p><div class="sp-ov-years-list">';
        foreach ($years as $y) {
            $html .= '<a class="sp-ov-year' . ($y === $sel ? ' is-active' : '') . '" href="' . $url($y) . '"><strong>' . $y . '</strong><span>Αρχείο</span></a>';
        }
        return $html . '</div></div>';
    }

    /** Notice at the top of the page while a previous year is shown. */
    public static function banner(): string
    {
        if (self::isCurrent()) {
            return '';
        }
        $y = self::get();
        return '<div class="sp-archive-banner">Αρχείο ' . $y . ' — βλέπετε τα έγγραφα και τα στοιχεία του ' . $y . '. <a href="'
            . htmlspecialchars(OfferHelper::url('view=overview&year=' . self::current(), false), ENT_QUOTES, 'UTF-8') . '">Επιστροφή στο ' . self::current() . '</a></div>';
    }
}

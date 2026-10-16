<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Shared helpers for the Offers (Προσφορές) module: URLs, money formatting,
 * light text markup, totals and branding.
 */
final class OfferHelper
{
    public const STATUSES = [
        'draft'    => 'Πρόχειρη',
        'sent'     => 'Απεσταλμένη',
        'accepted' => 'Αποδεκτή',
        'rejected' => 'Απορρίφθηκε',
    ];

    public static function itemid(): int
    {
        return max(1, (int) ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
    }

    /** Service Hub URL. $query is appended to the base component query. */
    public static function url(string $query = '', bool $xhtml = true): string
    {
        $query = ltrim($query, '&');
        return Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1' . ($query !== '' ? '&' . $query : '') . '&Itemid=' . self::itemid(), $xhtml);
    }

    public static function redirect(string $query): void
    {
        Factory::getApplication()->redirect(self::url($query, false));
    }

    public static function money(float $value): string
    {
        return number_format(round($value, 2), 2, ',', '.');
    }

    public static function dateGr(?string $date): string
    {
        if (!$date || $date === '0000-00-00') {
            return '';
        }
        try {
            return (new \DateTime($date))->format('d.m.Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status] ?? self::STATUSES['draft'];
    }

    /** Next offer number in the form YY + 3 digit sequence (e.g. 26023). */
    public static function nextNumber($db): string
    {
        $prefix = date('y');
        $q = $db->getQuery(true)
            ->select($db->quoteName('offer_number'))
            ->from($db->quoteName('#__supportplans_offers'))
            ->where($db->quoteName('offer_number') . ' LIKE ' . $db->quote($prefix . '%'));
        $max = 0;
        foreach ((array) $db->setQuery($q)->loadColumn() as $number) {
            $number = trim((string) $number);
            if (preg_match('/^' . $prefix . '(\d+)$/', $number, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Price table rows and totals for items that are flagged for the price table.
     *
     * @return array{rows: array, price: float, discount: float, net: float, vat: float, gross: float}
     */
    public static function totals(array $items, float $vatRate): array
    {
        $rows = [];
        $sum = ['price' => 0.0, 'discount' => 0.0, 'net' => 0.0, 'vat' => 0.0, 'gross' => 0.0];
        foreach ($items as $item) {
            if (!(int) ($item->in_table ?? 0)) {
                continue;
            }
            $price = round((float) ($item->price ?? 0), 2);
            $discount = round(min(abs((float) ($item->discount ?? 0)), abs($price)), 2);
            $net = round($price - $discount, 2);
            $vat = round($net * $vatRate / 100, 2);
            $row = [
                'label'    => trim((string) ($item->table_label ?? '')) ?: trim((string) ($item->section_title ?? '')),
                'price'    => $price,
                'discount' => $discount,
                'net'      => $net,
                'vat'      => $vat,
                'gross'    => round($net + $vat, 2),
            ];
            $rows[] = $row;
            foreach ($sum as $k => $v) {
                $sum[$k] = round($v + $row[$k], 2);
            }
        }
        return ['rows' => $rows] + $sum;
    }

    /** Brand values from the component settings. */
    public static function brand($params = null): array
    {
        $params = $params ?: ComponentHelper::getParams('com_supportplans');
        $name = trim((string) $params->get('brand_name', '')) ?: trim((string) Factory::getApplication()->get('sitename', ''));
        $accent = trim((string) $params->get('accent_color', '#367d22'));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
            $accent = '#367d22';
        }
        $secondary = trim((string) $params->get('secondary_color', '#ffffff'));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $secondary)) {
            $secondary = '#ffffff';
        }
        return [
            'name'      => $name !== '' ? $name : 'Support',
            'logo'      => self::assetUrl((string) $params->get('brand_logo', '')),
            'signature' => self::assetUrl((string) $params->get('offer_signature', '')),
            'address'   => trim((string) $params->get('brand_address', '')),
            'phone'     => trim((string) $params->get('brand_phone', '')),
            'email'     => trim((string) $params->get('brand_email', '')),
            'website'   => trim((string) $params->get('brand_website', '')),
            'accent'    => $accent,
            'secondary' => $secondary,
        ];
    }

    public static function assetUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        return preg_match('#^https?://#i', $path) ? $path : rtrim(Uri::root(), '/') . '/' . ltrim($path, '/');
    }

    /** Bank accounts from the component settings (same source as the reports). */
    public static function banks($params = null): array
    {
        $params = $params ?: ComponentHelper::getParams('com_supportplans');
        $banks = [];
        $decoded = json_decode((string) $params->get('bank_accounts', ''), true);
        foreach (is_array($decoded) ? $decoded : [] as $bank) {
            $row = ['name' => trim((string) ($bank['name'] ?? '')), 'iban' => trim((string) ($bank['iban'] ?? '')), 'beneficiary' => trim((string) ($bank['beneficiary'] ?? ''))];
            if (implode('', $row) !== '') {
                $banks[] = $row;
            }
        }
        if (!$banks) {
            for ($i = 1; $i <= 3; $i++) {
                $row = ['name' => trim((string) $params->get('bank_' . $i . '_name', '')), 'iban' => trim((string) $params->get('bank_' . $i . '_iban', '')), 'beneficiary' => trim((string) $params->get('bank_' . $i . '_beneficiary', ''))];
                if (implode('', $row) !== '') {
                    $banks[] = $row;
                }
            }
        }
        return $banks;
    }

    /** Replace {placeholders} in plain text. */
    public static function vars(string $text, array $vars): string
    {
        return preg_replace_callback('/\{([a-z_]+)\}/', static fn($m) => array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : $m[0], $text);
    }

    /**
     * Render the light markup used in service descriptions and terms into HTML blocks.
     *
     *   ## Τίτλος           underlined sub-heading
     *   ### Τίτλος          bold sub-heading
     *   - στοιχείο          bullet list (also "* ")
     *   1. στοιχείο         numbered list (10+ items are shown in two columns)
     *   **έντονα**          bold
     *   *πλάγια*            italic
     *   __υπογράμμιση__     underline
     *   ^1                  superscript reference (footnote mark)
     *   [κείμενο](https://…) link
     *   {customer} {project} {company} {number} {date}   placeholders
     *
     * Blank lines separate paragraphs. Every block is a top-level element so the
     * document paginator can move or split blocks between pages.
     */
    public static function render(string $text, array $vars = []): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", self::vars($text, $vars));
        $lines = explode("\n", $text);
        $html = [];
        $para = [];
        $list = null;
        $flushPara = static function () use (&$para, &$html) {
            if ($para) {
                // A paragraph that introduces a list ("...τα εξής:") stays on the same page as the list.
                $keep = preg_match('/:\s*$/u', strip_tags(end($para))) ? ' class="sp-o-keep"' : '';
                $html[] = '<p' . $keep . '>' . implode('<br>', $para) . '</p>';
                $para = [];
            }
        };
        $flushList = static function () use (&$list, &$html) {
            if ($list) {
                $tag = $list['type'];
                $class = $tag === 'ol' && count($list['items']) >= 10 ? ' class="sp-o-cols"' : '';
                $html[] = '<' . $tag . $class . '><li>' . implode('</li><li>', $list['items']) . '</li></' . $tag . '>';
                $list = null;
            }
        };
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                $flushPara();
                $flushList();
                continue;
            }
            if (preg_match('/^(#{2,3})\s+(.+)$/u', $trim, $m)) {
                $flushPara();
                $flushList();
                $tag = strlen($m[1]) === 2 ? 'h3' : 'h4';
                $html[] = '<' . $tag . ' class="sp-o-keep">' . self::inline($m[2]) . '</' . $tag . '>';
                continue;
            }
            $type = null;
            if (preg_match('/^[-*•▪]\s+(.+)$/u', $trim, $m)) {
                $type = 'ul';
                $content = $m[1];
            } elseif (preg_match('/^\d+[.)]\s+(.+)$/u', $trim, $m)) {
                $type = 'ol';
                $content = $m[1];
            }
            if ($type !== null) {
                $flushPara();
                if ($list && $list['type'] !== $type) {
                    $flushList();
                }
                $list = $list ?: ['type' => $type, 'items' => []];
                $list['items'][] = self::inline($content);
                continue;
            }
            $flushList();
            $para[] = self::inline($trim);
        }
        $flushPara();
        $flushList();
        return implode("\n", $html);
    }

    private static function inline(string $text): string
    {
        $out = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $out = preg_replace('/\*\*\*(.+?)\*\*\*/u', '<strong><em>$1</em></strong>', $out);
        $out = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $out);
        $out = preg_replace('/(^|[^*])\*(?!\s)(.+?)(?<!\s)\*(?!\*)/u', '$1<em>$2</em>', $out);
        $out = preg_replace('/__(.+?)__/u', '<u>$1</u>', $out);
        $out = preg_replace('/\^(\d{1,3})/', '<sup>$1</sup>', $out);
        $out = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/u', static fn($m) => '<a href="' . $m[2] . '">' . $m[1] . '</a>', $out);
        return $out;
    }
}

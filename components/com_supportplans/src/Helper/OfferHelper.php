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

    /**
     * Extra attributes for a section heading in the offer document: a new page
     * (class sp-o-break, handled by the paginator) or a spacing in mm before it.
     */
    public static function sectionLayout($pageBreak, $space): string
    {
        if ((int) $pageBreak) {
            return ' sp-o-break"';
        }
        return '" style="margin-top:' . max(0, min(80, (int) $space)) . 'mm"';
    }

    /**
     * Extra content blocks of a service, in order, after its main text:
     *   ['type' => 'list', 'title', 'desc', 'cols' (1|2), 'items' => [..]]  card list
     *   ['type' => 'text', 'body']                                          text with formatting
     * Accepts the stored JSON (blocks without a type are lists, as saved by v1.6.0)
     * or the posted form array. Empty blocks are dropped.
     */
    public const KINDS = ['service' => 'Υπηρεσία', 'description' => 'Περιγραφή'];

    public static function kind($value): string
    {
        return isset(self::KINDS[(string) $value]) ? (string) $value : 'service';
    }

    public static function kindBadge($value): string
    {
        $kind = self::kind($value);
        return '<span class="sp-kind-badge is-' . $kind . '">' . self::KINDS[$kind] . '</span>';
    }

    public static function lists($raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        $out = [];
        foreach (is_array($raw) ? $raw : [] as $block) {
            if (!is_array($block)) {
                continue;
            }
            if (($block['type'] ?? 'list') === 'text') {
                $body = trim(str_replace(["\r\n", "\r"], "\n", (string) ($block['body'] ?? '')));
                if ($body !== '') {
                    $out[] = ['type' => 'text', 'body' => $body];
                }
                continue;
            }
            $items = $block['items'] ?? [];
            if (is_string($items)) {
                $items = preg_split('/\R/u', $items);
            }
            $clean = [];
            foreach ((array) $items as $item) {
                $item = trim(preg_replace('/^\s*(?:[-*•▪]|\d+[.)])\s+/u', '', (string) $item));
                if ($item !== '') {
                    $clean[] = $item;
                }
            }
            if (!$clean) {
                continue;
            }
            $out[] = [
                'type'  => 'list',
                'title' => trim(strip_tags((string) ($block['title'] ?? ''))),
                'desc'  => trim(str_replace(["\r\n", "\r"], "\n", (string) ($block['desc'] ?? ''))),
                'cols'  => (int) ($block['cols'] ?? 1) === 2 ? 2 : 1,
                'items' => $clean,
            ];
        }
        return $out;
    }

    /**
     * Editor for the extra blocks of a service (library service or offer item).
     * $prefix is the form name prefix, e.g. "lists" or "items[3][lists]".
     */
    public static function listsEditor(string $prefix, array $blocks): string
    {
        $html = '<div class="sp-lists-wrap"><div class="sp-lists" data-lists data-sortable data-prefix="' . htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8') . '">';
        foreach (array_values($blocks) as $k => $block) {
            $html .= ($block['type'] ?? 'list') === 'text' ? self::textBlock($prefix, (string) $k, $block) : self::listBlock($prefix, (string) $k, $block);
        }
        $plus = '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>';
        return $html . '</div><div class="sp-block-add"><button type="button" class="sp-list-add" data-list-add>' . $plus . 'Λίστα</button>'
            . '<button type="button" class="sp-list-add" data-text-add>' . $plus . 'Κείμενο</button></div></div>';
    }

    private static function blockTools(): string
    {
        return '<button type="button" class="sp-grip" data-sort-handle title="Σύρετε για αλλαγή σειράς" aria-label="Αλλαγή σειράς"><svg viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></button>';
    }

    private static function removeButton(string $label): string
    {
        return '<button type="button" class="sp-icon-btn is-danger" data-list-remove title="' . $label . '" aria-label="' . $label . '"><svg viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg></button>';
    }

    /** One list block of the editor; also used (with placeholders) as the template for new blocks. */
    public static function listBlock(string $prefix, string $k, array $list = []): string
    {
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $name = $e($prefix . '[' . $k . ']');
        $two = (int) ($list['cols'] ?? 1) === 2;
        return '<div class="sp-list-block" data-list-block data-sort-item>'
            . '<input type="hidden" name="' . $name . '[type]" value="list">'
            . '<div class="sp-list-head">' . self::blockTools()
            . '<span class="sp-block-kind">Λίστα</span>'
            . '<input class="sp-list-title" name="' . $name . '[title]" value="' . $e($list['title'] ?? '') . '" placeholder="Τίτλος λίστας (προαιρετικός)" aria-label="Τίτλος λίστας">'
            . '<div class="sp-seg" role="radiogroup" aria-label="Στήλες">'
            . '<label><input type="radio" name="' . $name . '[cols]" value="1"' . ($two ? '' : ' checked') . '><span>1 στήλη</span></label>'
            . '<label><input type="radio" name="' . $name . '[cols]" value="2"' . ($two ? ' checked' : '') . '><span>2 στήλες</span></label>'
            . '</div>' . self::removeButton('Αφαίρεση λίστας')
            . '</div>'
            . '<div class="sp-list-field"><span>Περιγραφή λίστας (προαιρετική)</span><textarea name="' . $name . '[desc]" rows="2" data-sp-editor="inline">' . $e($list['desc'] ?? '') . '</textarea></div>'
            . '<div class="sp-list-field"><span>Στοιχεία — ένα ανά γραμμή</span><textarea name="' . $name . '[items]" rows="5" data-sp-editor="inline">' . $e(implode("\n", (array) ($list['items'] ?? []))) . '</textarea></div>'
            . '</div>';
    }

    /** One text block of the editor. */
    public static function textBlock(string $prefix, string $k, array $block = []): string
    {
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $name = $e($prefix . '[' . $k . ']');
        return '<div class="sp-list-block is-text" data-list-block data-sort-item>'
            . '<input type="hidden" name="' . $name . '[type]" value="text">'
            . '<div class="sp-list-head">' . self::blockTools() . '<span class="sp-block-kind">Κείμενο</span><span class="sp-block-spacer"></span>' . self::removeButton('Αφαίρεση κειμένου') . '</div>'
            . '<textarea name="' . $name . '[body]" rows="5" data-sp-editor>' . $e($block['body'] ?? '') . '</textarea>'
            . '</div>';
    }

    /** One structured list as a card (same look as the bank accounts card); two columns fill top-to-bottom. */
    public static function renderListCard(array $list, array $vars = []): string
    {
        $cols = $list['cols'] === 2 ? 2 : 1;
        $items = array_map(static fn($i) => self::inline(self::vars($i, $vars)), $list['items']);
        $rowsCount = (int) ceil(count($items) / $cols);
        $title = (string) ($list['title'] ?? '');
        $desc = (string) ($list['desc'] ?? '');
        $html = '<table class="sp-od-card sp-od-list sp-od-list-' . $cols . '">';
        if ($title !== '' || $desc !== '') {
            $html .= '<thead>';
            if ($title !== '') {
                $html .= '<tr class="sp-od-card-title"><th colspan="' . $cols . '">' . htmlspecialchars(self::vars($title, $vars), ENT_QUOTES, 'UTF-8') . '</th></tr>';
            }
            if ($desc !== '') {
                $lines = array_filter(array_map('trim', explode("\n", self::vars($desc, $vars))), 'strlen');
                $html .= '<tr class="sp-od-card-desc"><th colspan="' . $cols . '">' . implode('<br>', array_map([self::class, 'inline'], $lines)) . '</th></tr>';
            }
            $html .= '</thead>';
        }
        $html .= '<tbody>';
        for ($r = 0; $r < $rowsCount; $r++) {
            $html .= '<tr>';
            for ($c = 0; $c < $cols; $c++) {
                $html .= '<td>' . ($items[$c * $rowsCount + $r] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody></table>';
    }

    /** Extra blocks of a service as document HTML (text blocks rendered like the main text). */
    public static function renderBlocks(array $blocks, array $vars = []): string
    {
        $html = '';
        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                // Mark the first element of an extra text block so it gets the same gap as a list card.
                $html .= preg_replace_callback('/^<([a-z0-9]+)( class="([^"]*)")?/i', static fn($m) => '<' . $m[1] . ' class="sp-o-blockstart' . (isset($m[3]) && $m[3] !== '' ? ' ' . $m[3] : '') . '"', self::render($block['body'], $vars), 1);
            } else {
                $html .= self::renderListCard($block, $vars);
            }
        }
        return $html;
    }

    /** Built-in variables, always available in offer texts. */
    public const BUILTIN_VARIABLES = [
        'customer' => 'Πελάτης',
        'project'  => 'Έργο / εταιρεία πελάτη',
        'company'  => 'Η επωνυμία σας',
        'number'   => 'Αριθμός προσφοράς',
        'date'     => 'Ημερομηνία προσφοράς',
    ];

    /** Replace {placeholders} in plain text. */
    public static function vars(string $text, array $vars): string
    {
        return preg_replace_callback('/\{([\p{L}\p{N}_]+)\}/u', static fn($m) => array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : $m[0], $text);
    }

    /** Normalises a variable name typed by the user ("Πακέτο φιλοξενίας" -> "πακέτο_φιλοξενίας"). */
    public static function variableKey(string $key): string
    {
        $key = mb_strtolower(trim(trim($key), '{}'));
        $key = preg_replace('/\s+/u', '_', $key);
        return preg_replace('/[^\p{L}\p{N}_]/u', '', $key);
    }

    /**
     * Custom variables as an ordered list of ['key' => …, 'value' => …], from stored JSON
     * or from parallel key/value arrays posted by a form. Built-in names are ignored.
     */
    public static function customVariables($json = null, ?array $keys = null, ?array $values = null): array
    {
        $rows = [];
        if ($keys !== null) {
            foreach ($keys as $i => $key) {
                $rows[] = ['key' => (string) $key, 'value' => (string) ($values[$i] ?? '')];
            }
        } else {
            $decoded = json_decode((string) $json, true);
            $rows = is_array($decoded) ? $decoded : [];
        }
        $out = [];
        foreach ($rows as $row) {
            $key = self::variableKey((string) ($row['key'] ?? ''));
            if ($key === '' || self::isReservedVariable($key) || isset($out[$key])) {
                continue;
            }
            $out[$key] = ['key' => $key, 'value' => trim(str_replace(["\r\n", "\r"], "\n", (string) ($row['value'] ?? '')))];
        }
        return array_values($out);
    }

    /** Alternative names for built-in variables. */
    public const VARIABLE_ALIASES = ['client' => 'customer', 'project_name' => 'project'];

    /** True for names that are built-in variables or their aliases (they cannot be redefined). */
    public static function isReservedVariable(string $key): bool
    {
        return isset(self::BUILTIN_VARIABLES[$key]) || isset(self::VARIABLE_ALIASES[$key]);
    }

    /**
     * Offer fields defined in the Μεταβλητές tab: each one is a variable such as {intro_service}
     * that becomes an input on every offer.
     *
     * @return array<int, array{key: string, label: string, type: string, default: string}>
     */
    public static function fields($params = null): array
    {
        $params = $params ?: ComponentHelper::getParams('com_supportplans');
        $decoded = json_decode((string) $params->get('offer_fields', ''), true);
        $out = [];
        foreach (is_array($decoded) ? $decoded : [] as $row) {
            $key = self::variableKey((string) ($row['key'] ?? ''));
            if ($key === '' || self::isReservedVariable($key) || isset($out[$key])) {
                continue;
            }
            $out[$key] = [
                'key'     => $key,
                'label'   => trim((string) ($row['label'] ?? '')) ?: self::labelFromKey($key),
                'type'    => ($row['type'] ?? '') === 'textarea' ? 'textarea' : 'text',
                'default' => (string) ($row['default'] ?? ''),
            ];
        }
        return array_values($out);
    }

    /** "intro_service" -> "Intro service" */
    public static function labelFromKey(string $key): string
    {
        $label = trim(str_replace('_', ' ', $key));
        return mb_strtoupper(mb_substr($label, 0, 1)) . mb_substr($label, 1);
    }

    /**
     * Values of the offer fields for one offer: every defined field (stored value, or the
     * field's default when the offer has none) followed by any older stored variables
     * that are no longer defined, so nothing is lost.
     *
     * @return array<int, array{key: string, label: string, type: string, default: string, value: string, defined: bool}>
     */
    public static function offerFields(?object $offer, $params = null): array
    {
        $stored = [];
        foreach (self::customVariables($offer->variables ?? '') as $row) {
            $stored[$row['key']] = $row['value'];
        }
        $out = [];
        foreach (self::fields($params) as $field) {
            $out[] = $field + ['value' => array_key_exists($field['key'], $stored) ? $stored[$field['key']] : $field['default'], 'defined' => true];
            unset($stored[$field['key']]);
        }
        foreach ($stored as $key => $value) {
            $out[] = ['key' => $key, 'label' => self::labelFromKey($key), 'type' => strpos($value, "\n") !== false ? 'textarea' : 'text', 'default' => '', 'value' => $value, 'defined' => false];
        }
        return $out;
    }

    /** All variable values for an offer: built-ins, their aliases and the offer fields. */
    public static function offerVars(object $offer, array $brand, $params = null): array
    {
        $vars = [
            'customer' => (string) $offer->customer_name,
            'project'  => (string) $offer->project_name !== '' ? (string) $offer->project_name : (string) $offer->customer_name,
            'company'  => $brand['name'],
            'number'   => (string) $offer->offer_number,
            'date'     => self::dateGr($offer->offer_date ?? ''),
        ];
        foreach (self::VARIABLE_ALIASES as $alias => $target) {
            $vars[$alias] = $vars[$target];
        }
        foreach (self::offerFields($offer, $params) as $field) {
            $vars[$field['key']] = $field['value'];
        }
        return $vars;
    }

    /** Variable names used as {name} in the given texts. */
    public static function usedVariables(array $texts): array
    {
        $found = [];
        foreach ($texts as $text) {
            if (preg_match_all('/\{([\p{L}\p{N}_]+)\}/u', (string) $text, $m)) {
                foreach ($m[1] as $key) {
                    $found[$key] = true;
                }
            }
        }
        return array_keys($found);
    }

    /** Stores one value in the component parameters. */
    public static function saveParam(string $name, $value): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select([$db->quoteName('extension_id'), $db->quoteName('params')])->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . '=' . $db->quote('component'))
            ->where($db->quoteName('element') . '=' . $db->quote('com_supportplans'));
        $row = $db->setQuery($q)->loadObject();
        if (!$row) {
            throw new \RuntimeException('com_supportplans not found');
        }
        $params = json_decode((string) $row->params, true);
        $params = is_array($params) ? $params : [];
        $params[$name] = $value;
        $q = $db->getQuery(true)->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . '=' . $db->quote(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
            ->where($db->quoteName('extension_id') . '=' . (int) $row->extension_id);
        $db->setQuery($q)->execute();
    }

    /**
     * Render the light markup used in service descriptions and terms into HTML blocks.
     *
     *   ## Τίτλος           underlined sub-heading
     *   ### Τίτλος          bold sub-heading
     *   - στοιχείο          bullet list (also "* ")
     *   1. στοιχείο         numbered list (10+ items are shown in two columns)
     *   [[2 στήλες]]        on the line before a list: show it in two columns ([[1 στήλη]] forces one)
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
                // Explicit [[2 στήλες]] / [[1 στήλη]] wins; otherwise numbered lists of 10+ items use two columns.
                $cols = $list['cols'] ?? ($tag === 'ol' && count($list['items']) >= 10 ? 2 : 1);
                $class = $cols === 2 ? ' class="sp-o-cols"' : '';
                $html[] = '<' . $tag . $class . '><li>' . implode('</li><li>', $list['items']) . '</li></' . $tag . '>';
                $list = null;
            }
        };
        $pendingCols = null;
        foreach ($lines as $line) {
            $trim = trim($line);
            if (preg_match('/^\[\[\s*(?:([12])\s*στ[ήη]λ(?:η|ες)|cols\s*[=:]\s*([12]))\s*\]\]$/iu', $trim, $cm)) {
                $flushPara();
                $flushList();
                $pendingCols = (int) ($cm[1] !== '' ? $cm[1] : $cm[2]);
                continue;
            }
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
                if (!$list) {
                    $list = ['type' => $type, 'items' => [], 'cols' => $pendingCols];
                    $pendingCols = null;
                }
                $list['items'][] = self::inline($content);
                continue;
            }
            $flushList();
            $pendingCols = null;
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

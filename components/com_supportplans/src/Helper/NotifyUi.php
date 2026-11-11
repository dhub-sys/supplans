<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** Small rendering helpers shared by the tmpl/notify templates. */
final class NotifyUi
{
    public static function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }

    public static function url(string $query = '', bool $xhtml = true): string
    {
        return OfferHelper::url('view=notify' . ($query !== '' ? '&' . ltrim($query, '&') : ''), $xhtml);
    }

    /** POST form with one button (every change goes through POST + form token). */
    public static function button(string $task, array $fields, string $label, string $confirm = '', string $class = 'btn btn-sm btn-outline-primary'): string
    {
        $html = '<form class="sp-inline-form" method="post" action="' . self::e(OfferHelper::url('task=notify.' . $task, false)) . '"'
            . ($confirm !== '' ? ' onsubmit="return confirm(' . self::e(json_encode($confirm, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) . ');"' : '') . '>';
        foreach ($fields as $k => $v) {
            $html .= '<input type="hidden" name="' . self::e($k) . '" value="' . self::e($v) . '">';
        }
        return $html . HTMLHelper::_('form.token') . '<button class="' . self::e($class) . '">' . self::e($label) . '</button></form>';
    }

    public static function status(string $status, array $labels): string
    {
        $map = ['active' => 'accepted', 'sent' => 'accepted', 'queued' => 'sent', 'sending' => 'sent', 'failed' => 'rejected', 'suspended' => 'rejected', 'draft' => 'draft'];
        return '<span class="sp-offer-status is-' . ($map[$status] ?? 'draft') . '">' . self::e($labels[$status] ?? $status) . '</span>';
    }

    public static function expiry(?string $ymd, string $status = 'active'): string
    {
        if (!$ymd) {
            return '<span class="sp-offer-status">χωρίς λήξη</span>';
        }
        $d = Notify::daysUntil($ymd);
        $date = self::e(Notify::dateGr($ymd));
        if ($status !== 'active' || $d === null) {
            return '<span class="sp-offer-status">' . $date . '</span>';
        }
        if ($d < 0) {
            return '<span class="sp-offer-status is-rejected">' . $date . ' · έληξε πριν ' . abs($d) . ' ημ.</span>';
        }
        if ($d === 0) {
            return '<span class="sp-offer-status sp-n-warn">' . $date . ' · σήμερα</span>';
        }
        if ($d === 1) {
            return '<span class="sp-offer-status sp-n-warn">' . $date . ' · αύριο</span>';
        }
        return '<span class="sp-offer-status ' . ($d <= 30 ? 'sp-n-warn' : 'is-accepted') . '">' . $date . ' · σε ' . $d . ' ημ.</span>';
    }

    /** Sandboxed preview of an e-mail (no scripts, no access to the Service Hub). */
    public static function preview(string $html): string
    {
        return '<iframe class="sp-n-preview" sandbox="" srcdoc="' . self::e($html) . '" title="Προεπισκόπηση email"></iframe>';
    }

    public static function select(string $name, array $options, $current, ?string $placeholder = null, string $attrs = ''): string
    {
        $html = '<select name="' . self::e($name) . '" ' . $attrs . '>';
        if ($placeholder !== null) {
            $html .= '<option value="">' . self::e($placeholder) . '</option>';
        }
        foreach ($options as $v => $l) {
            $html .= '<option value="' . self::e($v) . '"' . ((string) $current === (string) $v ? ' selected' : '') . '>' . self::e($l) . '</option>';
        }
        return $html . '</select>';
    }
}

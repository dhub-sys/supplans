<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** Side navigation of the Service Hub (used by the Client Management and activity log pages). */
final class Sidebar
{
    public const ICON_CLIENTS = '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4.5-6.2"/></svg>';
    public const ICON_ACTIVITY = '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3 2"/></svg>';

    public static function nav(string $active): string
    {
        $itemid = OfferHelper::itemid();
        $items = [
            'plans' => ['Πακέτα', '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg>'],
            'serviceplans' => ['Πλάνα', '<svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg>'],
            'offers' => ['Προσφορές', '<svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M14.2 11.6a2.6 2.6 0 1 0 0 3.8M9.5 12.8h3.2M9.5 14.4h3.2"/></svg>'],
            'notify' => ['Client Management', self::ICON_CLIENTS],
            'settings' => ['Ρυθμίσεις', '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg>'],
            'activity' => ['Ιστορικό ενεργειών', self::ICON_ACTIVITY],
            'help' => ['Οδηγίες', '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg>'],
        ];
        $html = '<nav class="sp-side-nav">';
        foreach ($items as $view => [$label, $icon]) {
            $html .= '<a class="sp-side-link' . ($view === $active ? ' is-active' : '') . '" href="' . Route::_('index.php?option=com_supportplans&view=' . $view . '&tmpl=component&spapp=1&Itemid=' . $itemid) . '">'
                . '<span class="sp-side-icon">' . $icon . '</span><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span></a>';
        }
        return $html . '</nav>';
    }

    /** Links added to the older templates, which keep their own static navigation. */
    public static function extraLink(string $view): string
    {
        $itemid = OfferHelper::itemid();
        $icon = $view === 'activity' ? self::ICON_ACTIVITY : self::ICON_CLIENTS;
        $label = $view === 'activity' ? 'Ιστορικό ενεργειών' : 'Client Management';
        return '<a class="sp-side-link" href="' . Route::_('index.php?option=com_supportplans&view=' . $view . '&tmpl=component&spapp=1&Itemid=' . $itemid) . '"><span class="sp-side-icon">' . $icon . '</span><span>' . $label . '</span></a>';
    }
}

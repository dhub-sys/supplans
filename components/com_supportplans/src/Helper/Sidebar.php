<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** Side navigation of the Service Hub (used by the Client Management and activity log pages). */
final class Sidebar
{
    public const ICON_CLIENTS = '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4.5-6.2"/></svg>';
    public const ICON_DOCUMENTS = '<svg viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M8 13h8M8 16h5"/></svg>';
    public const ICON_STRUCTURES = '<svg viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="4" rx="1"/><rect x="3" y="16" width="6" height="4" rx="1"/><rect x="15" y="16" width="6" height="4" rx="1"/><path d="M12 7v4M6 16v-2.5a1.5 1.5 0 0 1 1.5-1.5h9a1.5 1.5 0 0 1 1.5 1.5V16"/></svg>';
    public const ICON_ACTIVITY = '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3 2"/></svg>';

    public static function nav(string $active): string
    {
        $itemid = OfferHelper::itemid();
        $icons = [
            'plans' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg>',
            'serviceplans' => '<svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg>',
            'offers' => '<svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M14.2 11.6a2.6 2.6 0 1 0 0 3.8M9.5 12.8h3.2M9.5 14.4h3.2"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg>',
            'help' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg>',
        ];
        // Πλάνα, then Έγγραφα with its document types as a sub menu, then the rest.
        $items = [
            'serviceplans' => ['Πλάνα', $icons['serviceplans']],
            'documents' => ['Έγγραφα', self::ICON_DOCUMENTS, [
                'plans' => ['Πακέτα', $icons['plans']],
                'offers' => ['Προσφορές', $icons['offers']],
                'structures' => ['Προτάσεις δομής', self::ICON_STRUCTURES],
            ]],
            'notify' => ['Client Management', self::ICON_CLIENTS],
            'settings' => ['Ρυθμίσεις', $icons['settings']],
            'activity' => ['Ιστορικό ενεργειών', self::ICON_ACTIVITY],
            'help' => ['Οδηγίες', $icons['help']],
        ];
        $link = static function (string $view, string $label, string $icon, string $class) use ($itemid): string {
            return '<a class="sp-side-link' . $class . '" href="' . Route::_('index.php?option=com_supportplans&view=' . $view . '&tmpl=component&spapp=1&Itemid=' . $itemid) . '">'
                . '<span class="sp-side-icon">' . $icon . '</span><span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span></a>';
        };
        $html = '<nav class="sp-side-nav">';
        foreach ($items as $view => $item) {
            [$label, $icon] = $item;
            $children = $item[2] ?? [];
            $inGroup = isset($children[$active]);
            $html .= $link($view, $label, $icon, ($view === $active ? ' is-active' : '') . ($inGroup ? ' is-parent-active' : ''));
            if ($children) {
                $html .= '<div class="sp-side-sub">';
                foreach ($children as $cv => [$cl, $ci]) {
                    $html .= $link($cv, $cl, $ci, ' is-sub' . ($cv === $active ? ' is-active' : ''));
                }
                $html .= '</div>';
            }
        }
        return $html . '</nav>';
    }

    /**
     * Logo of the sidebar: the "Λογότυπο sidebar" setting (as is, or white when chosen), else the
     * business logo in white, because the sidebar is dark. ['url' => …, 'white' => bool]
     */
    public static function logo(): array
    {
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans');
        $own = trim((string) $params->get('sidebar_logo', ''));
        if ($own !== '') {
            return ['url' => OfferHelper::assetUrl($own), 'white' => (bool) (int) $params->get('sidebar_logo_white', 0)];
        }
        return ['url' => OfferHelper::brand()['logo'], 'white' => true];
    }

    /**
     * Top of the sidebar: section label and the burger button that collapses the sidebar to its
     * icons. The state is kept per browser (localStorage) and applied before the page is painted,
     * so the sidebar does not jump on every page load.
     */
    public static function head(): string
    {
        // The logo moves from the white top bar into the top of the sidebar (wide screens).
        $brand = OfferHelper::brand();
        $l = self::logo();
        $logo = $l['url'] !== ''
            ? '<img src="' . htmlspecialchars($l['url'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') . '"' . ($l['white'] ? ' class="is-white"' : '') . '>'
            : '<strong>' . htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') . '</strong>';
        return '<script>(function(){var r=document.documentElement;r.classList.add("sp-sb-layout");try{if(localStorage.getItem("spSidebar")==="collapsed")r.classList.add("sp-sb-collapsed");}catch(e){}})();</script>'
            . '<div class="sp-sb-head"><span class="sp-sb-brand">' . $logo . '</span>'
            . '<button type="button" class="sp-sb-toggle" aria-label="Σύμπτυξη μενού" title="Σύμπτυξη / ανάπτυξη μενού" aria-expanded="true"><span></span><span></span><span></span></button></div>'
            . Year::picker()
            . '<div class="sp-sidebar-section-label sp-sb-label">Διαχείριση</div>'
            . '<script>(function(){'
            . 'var root=document.documentElement,btn=document.currentScript.parentNode.querySelector(".sp-sb-toggle");'
            . 'function sync(){var c=root.classList.contains("sp-sb-collapsed");btn.setAttribute("aria-expanded",c?"false":"true");btn.setAttribute("aria-label",c?"Ανάπτυξη μενού":"Σύμπτυξη μενού");}'
            // Labels as tooltips of the icons when the sidebar is collapsed.
            . 'function titles(){document.querySelectorAll(".sp-sidebar .sp-side-link,.sp-sidebar .sp-side-joomla").forEach(function(a){if(!a.title){var t=a.classList.contains("sp-side-link")?a.querySelector("span:last-child"):a;if(t)a.title=t.textContent.replace(/[↪]/g,"").trim();}});}'
            . 'if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",titles);else titles();'
            . 'btn.addEventListener("click",function(){root.classList.add("sp-sb-anim");var c=root.classList.toggle("sp-sb-collapsed");try{localStorage.setItem("spSidebar",c?"collapsed":"expanded");}catch(e){}sync();});'
            . 'sync();})();</script>';
    }

    /** Links added to the older templates, which keep their own static navigation. */
    public static function extraLink(string $view, bool $active = false): string
    {
        $itemid = OfferHelper::itemid();
        [$label, $icon] = [
            'activity' => ['Ιστορικό ενεργειών', self::ICON_ACTIVITY],
            'structures' => ['Προτάσεις δομής', self::ICON_STRUCTURES],
            'documents' => ['Έγγραφα', self::ICON_DOCUMENTS],
        ][$view] ?? ['Client Management', self::ICON_CLIENTS];
        return '<a class="sp-side-link' . ($active ? ' is-active' : '') . '" href="' . Route::_('index.php?option=com_supportplans&view=' . $view . '&tmpl=component&spapp=1&Itemid=' . $itemid) . '"><span class="sp-side-icon">' . $icon . '</span><span>' . $label . '</span></a>';
    }
}

<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Prints the component's stylesheets and scripts inline in the page body.
 *
 * The Service Hub must not depend on the site template rendering the document head, on
 * page/CSS optimisers or on the web server serving /media: any of these leaves the pages
 * without styling. Only when a file cannot be read does it fall back to a normal <head> asset.
 */
final class Assets
{
    private static array $done = [];

    public static function css(string ...$files): string
    {
        return self::emit('css', $files);
    }

    public static function js(string ...$files): string
    {
        return self::emit('js', $files);
    }

    private static function emit(string $type, array $files): string
    {
        $html = '';
        foreach ($files as $file) {
            if (isset(self::$done[$type . $file])) continue;
            self::$done[$type . $file] = true;
            $path = JPATH_ROOT . '/media/com_supportplans/' . $type . '/' . $file;
            $code = is_readable($path) ? (string) file_get_contents($path) : '';
            if ($code === '') {
                $type === 'css'
                    ? HTMLHelper::_('stylesheet', 'com_supportplans/' . $file, ['version' => 'auto', 'relative' => true])
                    : HTMLHelper::_('script', 'com_supportplans/' . $file, ['version' => 'auto', 'relative' => true], ['defer' => true]);
                continue;
            }
            $html .= $type === 'css'
                ? '<style data-sp-asset="' . $file . '">' . str_ireplace('</style', '<\/style', $code) . "</style>\n"
                : '<script data-sp-asset="' . $file . '">' . str_ireplace('</script', '<\/script', $code) . "</script>\n";
        }
        return $html;
    }
}

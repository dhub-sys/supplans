<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Private, read-only link to a package report for the customer.
 *
 * The token is 48 random bytes (96 hex characters): it cannot be guessed and it is the only
 * thing that grants access. The page is served with noindex/nofollow headers so search engines
 * never list it, and regenerating or disabling the link invalidates the previous one.
 */
final class ShareLink
{
    public const PATTERN = '/^[a-f0-9]{96}$/';

    public static function newToken(): string
    {
        return bin2hex(random_bytes(48));
    }

    public static function isToken(string $value): bool
    {
        return (bool) preg_match(self::PATTERN, $value);
    }

    public static function url(string $token): string
    {
        return rtrim(Uri::root(), '/') . '/index.php?option=com_supportplans&view=report&tmpl=component&t=' . $token;
    }

    /** Plan for a token, or null. */
    public static function plan(string $token): ?object
    {
        if (!self::isToken($token)) {
            return null;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select('id, share_token')->from($db->quoteName('#__supportplans_plans'))
            ->where($db->quoteName('share_token') . ' = :token')->bind(':token', $token);
        $row = $db->setQuery($q)->loadObject();
        return $row && hash_equals((string) $row->share_token, $token) ? $row : null;
    }

    /** Headers and meta tags that keep the shared page out of search engines and referrers. */
    public static function protectPage(): void
    {
        $app = Factory::getApplication();
        $app->setHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet', true);
        $app->setHeader('Referrer-Policy', 'no-referrer', true);
        $app->setHeader('Cache-Control', 'private, no-store, max-age=0', true);
        $doc = $app->getDocument();
        if ($doc && method_exists($doc, 'setMetaData')) {
            $doc->setMetaData('robots', 'noindex, nofollow, noarchive, nosnippet');
            $doc->setMetaData('referrer', 'no-referrer');
        }
    }
}

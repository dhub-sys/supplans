<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * "Stay signed in" cookie of the Service Hub.
 *
 * After a successful OTP the browser keeps a signed cookie for the duration set in
 * Settings (login_lifetime, hours). While it is valid, opening the Service Hub again
 * (e.g. from the Joomla administrator) restores the session without username/OTP.
 * The signature covers the user's password hash, so changing the password or blocking
 * the user invalidates every cookie.
 */
final class AuthCookie
{
    private const NAME = 'sp_hub_auth';
    private const DEFAULT_HOURS = 12;
    private const MAX_HOURS = 24 * 90;

    /** Lifetime in hours; 0 means "only for the browser session". */
    public static function hours(): int
    {
        $hours = ComponentHelper::getParams('com_supportplans')->get('login_lifetime', self::DEFAULT_HOURS);
        return max(0, min(self::MAX_HOURS, (int) $hours));
    }

    public static function issue(int $userId): void
    {
        $hours = self::hours();
        $user = self::user($userId);
        if ($hours < 1 || !$user) {
            self::clear();
            return;
        }
        $expires = time() + $hours * 3600;
        $value = $userId . '.' . $expires . '.' . self::sign($userId, $expires, (string) $user->password);
        self::write($value, $expires);
    }

    /** Restores the Service Hub session from a valid cookie. */
    public static function restore(): bool
    {
        $app = Factory::getApplication();
        $raw = (string) $app->input->cookie->getString(self::NAME, '');
        if (!preg_match('/^(\d+)\.(\d+)\.([a-f0-9]{64})$/', $raw, $m)) {
            return false;
        }
        [, $userId, $expires, $signature] = $m;
        $userId = (int) $userId;
        $expires = (int) $expires;
        $user = self::user($userId);
        // A shorter lifetime set later in Settings also applies to cookies already issued.
        $limit = time() + self::hours() * 3600;
        if (!$user || $expires < time() || $expires > $limit + 60
            || !hash_equals(self::sign($userId, $expires, (string) $user->password), $signature)) {
            self::clear();
            return false;
        }

        $session = $app->getSession();
        $session->set('supportplans.auth.user_id', $userId);
        $session->set('supportplans.auth.username', (string) $user->username);
        $session->set('supportplans.auth.name', (string) $user->name);
        $session->set('supportplans.auth.email', (string) $user->email);
        $session->set('supportplans.auth.authenticated_at', time());
        $session->set('supportplans.otp.verified', true);
        return true;
    }

    public static function clear(): void
    {
        if (isset($_COOKIE[self::NAME])) {
            self::write('', time() - 3600);
        }
    }

    private static function user(int $userId): ?object
    {
        if ($userId < 1) {
            return null;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)
            ->select($db->quoteName(['id', 'username', 'name', 'email', 'password', 'block', 'requireReset']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . '=' . $userId);
        $user = $db->setQuery($q)->loadObject();
        return $user && !(int) $user->block && !(int) $user->requireReset ? $user : null;
    }

    private static function sign(int $userId, int $expires, string $passwordHash): string
    {
        $secret = (string) Factory::getApplication()->get('secret', '');
        return hash_hmac('sha256', 'supportplans|' . $userId . '|' . $expires . '|' . $passwordHash, $secret);
    }

    private static function write(string $value, int $expires): void
    {
        $path = rtrim((string) Uri::root(true), '/') . '/';
        setcookie(self::NAME, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'secure'   => Uri::getInstance()->isSsl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

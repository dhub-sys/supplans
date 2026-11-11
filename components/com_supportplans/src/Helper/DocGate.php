<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Protection of the private document links sent by email:
 *  - expiry: after share_expires the link stops working;
 *  - one-time code (OTP): before the document is shown, a 6-digit code is emailed to one of the
 *    addresses the document was sent to (share_emails); the code is valid for 10 minutes and 5
 *    tries. A correct code unlocks this link in this browser for 12 hours (signed cookie).
 * The addresses are never shown in full on the page (masked), and the gate page reveals nothing
 * about the document beyond the company name.
 */
final class DocGate
{
    private const TABLES = ['offer' => '#__supportplans_offers', 'structure' => '#__supportplans_structures', 'plan' => '#__supportplans_plans'];
    private const CODE_TTL = 600;
    private const MAX_TRIES = 5;
    private const UNLOCK_TTL = 43200;
    private const RESEND_WAIT = 60;
    private const MAX_CODES = 5;

    /** Document of a link: [type, row] or null. */
    public static function doc(string $type, string $token): ?object
    {
        return match ($type) {
            'offer' => ShareLink::offer($token),
            'structure' => ShareLink::structure($token),
            'plan' => ($p = ShareLink::plan($token)) ? DocMail::find('plan', (int) $p->id) : null,
            default => null,
        };
    }

    public static function expired(object $doc): bool
    {
        $exp = (string) ($doc->share_expires ?? '');
        return $exp !== '' && $exp !== '0000-00-00 00:00:00' && strtotime($exp . ' UTC') < time();
    }

    public static function emails(object $doc): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) ($doc->share_emails ?? ''))), static fn($m) => filter_var($m, FILTER_VALIDATE_EMAIL)));
    }

    public static function needsCode(object $doc): bool
    {
        return (int) ($doc->share_otp ?? 0) === 1 && self::emails($doc);
    }

    /** "ma***@ex***.gr" */
    public static function mask(string $email): string
    {
        [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $dot = strrpos($domain, '.');
        $host = $dot === false ? $domain : substr($domain, 0, $dot);
        $tld = $dot === false ? '' : substr($domain, $dot);
        $part = static fn(string $s) => mb_substr($s, 0, min(2, max(1, mb_strlen($s) - 1))) . '***';
        return $part($user) . '@' . $part($host) . $tld;
    }

    private static function key(string $token): string
    {
        return substr(hash('sha256', $token), 0, 24);
    }

    private static function sign(string $data): string
    {
        return hash_hmac('sha256', 'supportplans-docgate|' . $data, (string) Factory::getApplication()->get('secret', ''));
    }

    private static function cookieName(string $token): string
    {
        return 'spdg_' . substr(self::key($token), 0, 12);
    }

    public static function unlocked(string $token): bool
    {
        $raw = (string) Factory::getApplication()->input->cookie->getString(self::cookieName($token), '');
        [$until, $sig] = array_pad(explode('.', $raw, 2), 2, '');
        return ctype_digit($until) && (int) $until > time() && hash_equals(self::sign(self::key($token) . '|' . $until), $sig);
    }

    private static function unlock(string $token): void
    {
        $until = (string) (time() + self::UNLOCK_TTL);
        $app = Factory::getApplication();
        $app->input->cookie->set(self::cookieName($token), $until . '.' . self::sign(self::key($token) . '|' . $until), [
            'expires' => (int) $until, 'path' => '/', 'secure' => \Joomla\CMS\Uri\Uri::getInstance()->isSsl(), 'httponly' => true, 'samesite' => 'Lax',
        ]);
    }

    /**
     * Called by the document views for a link: '' when the document may be shown, else the
     * HTML of the page to show instead (expired link or code request).
     */
    public static function guard(string $type, object $doc, string $token): string
    {
        if (self::expired($doc)) {
            Factory::getApplication()->setHeader('Status', '410', true);
            return self::page('Ο σύνδεσμος έληξε', '<p>Ο σύνδεσμος για αυτό το έγγραφο δεν είναι πλέον ενεργός. Επικοινωνήστε μαζί μας για να σας το στείλουμε ξανά.</p>');
        }
        if (!self::needsCode($doc) || self::unlocked($token)) {
            return '';
        }
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $session = Factory::getApplication()->getSession();
        $state = (array) $session->get('supportplans.docgate.' . self::key($token), []);
        $sent = !empty($state['hash']) && (int) ($state['until'] ?? 0) > time();
        $emails = self::emails($doc);
        $action = $e(OfferHelper::url('', false));
        $hidden = '<input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1">'
            . '<input type="hidden" name="Itemid" value="' . (int) OfferHelper::itemid() . '"><input type="hidden" name="doc_type" value="' . $e($type) . '"><input type="hidden" name="t" value="' . $e($token) . '">'
            . HTMLHelper::_('form.token');
        $html = '<p>Το έγγραφο είναι προστατευμένο. Για να το δείτε, θα σας στείλουμε έναν κωδικό μίας χρήσης στο email σας.</p>';
        foreach (Factory::getApplication()->getMessageQueue(true) as $m) {
            $html .= '<div class="sp-dg-msg is-' . $e($m['type'] ?? 'message') . '">' . $e(strip_tags((string) ($m['message'] ?? ''))) . '</div>';
        }
        if ($sent) {
            $html .= '<form method="post" action="' . $action . '" class="sp-dg-form">' . $hidden . '<input type="hidden" name="task" value="docgate.verify">'
                . '<label>Κωδικός που λάβατε στο ' . $e(self::mask((string) ($state['email'] ?? ''))) . '<input name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="••••••"></label>'
                . '<button>Προβολή εγγράφου</button></form>'
                . '<form method="post" action="' . $action . '" class="sp-dg-again">' . $hidden . '<input type="hidden" name="task" value="docgate.send"><input type="hidden" name="i" value="' . (int) ($state['i'] ?? 0) . '"><button class="is-link">Αποστολή νέου κωδικού</button></form>';
        } else {
            $html .= '<form method="post" action="' . $action . '" class="sp-dg-form">' . $hidden . '<input type="hidden" name="task" value="docgate.send">';
            if (count($emails) > 1) {
                $html .= '<label>Αποστολή κωδικού στο<select name="i">';
                foreach ($emails as $i => $m) $html .= '<option value="' . $i . '">' . $e(self::mask($m)) . '</option>';
                $html .= '</select></label>';
            } else {
                $html .= '<input type="hidden" name="i" value="0"><p class="sp-dg-to">' . $e(self::mask($emails[0])) . '</p>';
            }
            $html .= '<button>Αποστολή κωδικού</button></form>';
        }
        return self::page('Προστατευμένο έγγραφο', $html);
    }

    /** Emails a new code to the chosen address of the document. Returns an error or ''. */
    public static function sendCode(string $type, string $token, int $index): string
    {
        $doc = self::doc($type, $token);
        if (!$doc || self::expired($doc) || !self::needsCode($doc)) return 'Ο σύνδεσμος δεν είναι ενεργός.';
        $emails = self::emails($doc);
        $email = $emails[$index] ?? $emails[0];
        $session = Factory::getApplication()->getSession();
        $key = 'supportplans.docgate.' . self::key($token);
        $state = (array) $session->get($key, []);
        $times = array_values(array_filter((array) ($state['times'] ?? []), static fn($t) => $t > time() - 3600));
        if ($times && max($times) > time() - self::RESEND_WAIT) return 'Περιμένετε ένα λεπτό πριν ζητήσετε νέο κωδικό.';
        if (count($times) >= self::MAX_CODES) return 'Ζητήθηκαν πολλοί κωδικοί. Δοκιμάστε ξανά σε λίγο.';
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $inner = '<p>Ο κωδικός για την προβολή του εγγράφου που σας στείλαμε είναι:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:6px;margin:18px 0;">' . $esc($code) . '</p>'
            . '<p style="font-size:13px;color:#6b7280;">Ισχύει για 10 λεπτά. Αν δεν τον ζητήσατε εσείς, αγνοήστε αυτό το μήνυμα.</p>';
        try {
            $mailer = Notify::senderMailer($type);
            $mailer->addRecipient($email);
            $mailer->setSubject('Κωδικός προβολής εγγράφου: ' . $code);
            $mailer->isHtml(true);
            $mailer->setBody(Notify::wrap($inner));
            $mailer->AltBody = 'Κωδικός προβολής εγγράφου: ' . $code . "\nΙσχύει για 10 λεπτά.";
            if ($mailer->Send() !== true) throw new \RuntimeException('send');
        } catch (\Throwable $e) {
            return 'Ο κωδικός δεν στάλθηκε. Δοκιμάστε ξανά αργότερα.';
        }
        $times[] = time();
        $session->set($key, ['hash' => password_hash($code, PASSWORD_DEFAULT), 'until' => time() + self::CODE_TTL, 'tries' => 0, 'email' => $email, 'i' => $index, 'times' => $times]);
        Activity::note(self::mask($email));
        return '';
    }

    /** Checks the code; unlocks the link for this browser. Returns an error or ''. */
    public static function verify(string $type, string $token, string $code): string
    {
        $doc = self::doc($type, $token);
        if (!$doc || self::expired($doc)) return 'Ο σύνδεσμος δεν είναι ενεργός.';
        $session = Factory::getApplication()->getSession();
        $key = 'supportplans.docgate.' . self::key($token);
        $state = (array) $session->get($key, []);
        if (empty($state['hash']) || (int) ($state['until'] ?? 0) < time()) {
            $session->set($key, ['times' => $state['times'] ?? []]);
            return 'Ο κωδικός έληξε. Ζητήστε νέο κωδικό.';
        }
        if (!preg_match('/^\d{6}$/', trim($code)) || !password_verify(trim($code), (string) $state['hash'])) {
            $state['tries'] = (int) ($state['tries'] ?? 0) + 1;
            if ($state['tries'] >= self::MAX_TRIES) {
                $session->set($key, ['times' => $state['times'] ?? []]);
                return 'Πολλές λανθασμένες προσπάθειες. Ζητήστε νέο κωδικό.';
            }
            $session->set($key, $state);
            return 'Λάθος κωδικός. Απομένουν ' . (self::MAX_TRIES - $state['tries']) . ' προσπάθειες.';
        }
        $session->set($key, []);
        self::unlock($token);
        return '';
    }

    /** URL of the document link (after the gate). */
    public static function linkUrl(string $type, string $token): string
    {
        return ['offer' => ShareLink::offerUrl($token), 'structure' => ShareLink::structureUrl($token), 'plan' => ShareLink::url($token)][$type] ?? '';
    }

    /** Minimal branded page (no document content). */
    private static function page(string $title, string $body): string
    {
        $brand = OfferHelper::brand();
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        Factory::getApplication()->getDocument()->setTitle($title);
        $logo = $brand['logo'] !== '' ? '<img src="' . $e($brand['logo']) . '" alt="' . $e($brand['name']) . '">' : '<strong>' . $e($brand['name']) . '</strong>';
        $accent = $e($brand['accent'] ?: '#367d22');
        return '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            . '<link href="https://fonts.googleapis.com/css2?family=Geom:ital,wght@0,300..900;1,300..900&amp;display=swap" rel="stylesheet"><style>
.sp-dg{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f2f4f7;padding:24px;font-family:"Geom",Arial,Helvetica,sans-serif;color:#101828;box-sizing:border-box}
.sp-dg *{box-sizing:border-box;font-family:inherit}
.sp-dg-card{width:min(440px,100%);background:#fff;border-radius:16px;box-shadow:0 20px 50px rgba(16,24,40,.12);padding:30px 30px 26px}
.sp-dg-logo img{max-height:46px;max-width:200px;display:block}.sp-dg-logo strong{font-size:20px}
.sp-dg h1{font-size:21px;font-weight:600;margin:22px 0 8px}
.sp-dg p{font-size:14.5px;line-height:1.55;color:#475467;margin:0 0 14px}
.sp-dg-to{font-weight:600;color:#101828!important}
.sp-dg-form{display:grid;gap:12px;margin-top:6px}
.sp-dg label{display:grid;gap:6px;font-size:13px;font-weight:600;color:#344054}
.sp-dg input,.sp-dg select{padding:11px 12px;border:1px solid #d0d5dd;border-radius:9px;font-size:16px;width:100%}
.sp-dg input[name=code]{font-size:24px;letter-spacing:8px;text-align:center}
.sp-dg button{background:' . $accent . ';color:#fff;border:0;border-radius:9px;padding:12px 16px;font-size:15px;font-weight:600;cursor:pointer}
.sp-dg button.is-link{background:none;color:#475467;padding:6px 0;font-weight:500;text-decoration:underline;font-size:13.5px}
.sp-dg-again{margin-top:6px;text-align:center}
.sp-dg-msg{padding:10px 12px;border-radius:9px;font-size:13.5px;margin:0 0 12px;background:#ecfdf3;color:#067647}
.sp-dg-msg.is-error,.sp-dg-msg.is-warning{background:#fef3f2;color:#b42318}
</style><div class="sp-dg"><div class="sp-dg-card"><div class="sp-dg-logo">' . $logo . '</div><h1>' . $e($title) . '</h1>' . $body . '</div></div>';
    }
}

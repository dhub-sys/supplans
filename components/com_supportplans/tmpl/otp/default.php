<?php
defined('_JEXEC') or die;

$app = \Joomla\CMS\Factory::getApplication();
$spDb = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
$spQ = $spDb->getQuery(true)->select($spDb->quoteName('params'))->from($spDb->quoteName('#__extensions'))
    ->where($spDb->quoteName('type') . '=' . $spDb->quote('component'))
    ->where($spDb->quoteName('element') . '=' . $spDb->quote('com_supportplans'));
$spRawParams = json_decode((string) $spDb->setQuery($spQ)->loadResult(), true);
$spItemid = max(1, (int) ($spRawParams['standalone_itemid'] ?? 785));
$loginIcon = trim((string) (($spRawParams['login_icon'] ?? '') ?: ''));
if ($loginIcon !== '' && !preg_match('#^https?://#i', $loginIcon)) {
    $loginIcon = \Joomla\CMS\Uri\Uri::root() . ltrim($loginIcon, '/');
}

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
$email=(string)Factory::getApplication()->getSession()->get('supportplans.pending.email','');
$masked=preg_replace('/(^.).*(@.*$)/', '$1••••$2', $email);
?>
<!doctype html><html lang="el"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Support Plans - Επιβεβαίωση</title>
<style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Arial,sans-serif;background:linear-gradient(145deg,#eef4ff,#f8fafc 52%,#eef2ff);color:#172033}
body{min-height:100vh;display:block;padding:28px 24px 24px}
.brand{text-align:center;margin-bottom:22px}.mark.has-image{background:transparent!important;box-shadow:none!important;width:auto!important;max-width:120px!important;height:58px!important}.mark.has-image img{display:block;max-width:120px;max-height:58px;width:auto;height:auto;object-fit:contain;margin:auto}.mark{width:54px;height:54px;border-radius:16px;background:#2563eb;color:#fff;display:grid;place-items:center;margin:0 auto 12px;font-size:22px;font-weight:800;box-shadow:0 12px 28px rgba(37,99,235,.2)}
.card{background:#fff;border:1px solid #e5e9f0;border-radius:18px;padding:30px;box-shadow:0 24px 70px rgba(30,64,175,.12)}
h1{font-size:23px;margin:0 0 8px}p{font-size:14px;color:#667085;line-height:1.55;margin:0 0 22px}
.otp{width:100%;height:58px;text-align:center;letter-spacing:10px;font-size:25px;font-weight:700;border:1px solid #d7dde7;border-radius:11px;outline:none}
.otp:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.btn{width:100%;height:46px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;margin-top:16px}.btn:hover{background:#1d4ed8}
.resend{margin-top:14px;text-align:center}.resend button{border:0;background:none;color:#2563eb;cursor:pointer;font-weight:650}.hint{text-align:center;font-size:12px;color:#98a2b3;margin-top:18px}

/* v1044-login-position: stable position even when browser password UI appears */
@media(max-width:720px){body{padding-top:20px!important}}
.shell{width:min(100%,430px);margin:0 auto}
</style></head><body><main class="shell">
<div class="brand"><div class="mark<?php echo $loginIcon ? ' has-image' : ''; ?>"><?php if ($loginIcon): ?><img src="<?php echo htmlspecialchars($loginIcon, ENT_QUOTES, 'UTF-8'); ?>" alt="Support Plans"><?php else: ?>SP<?php endif; ?></div><strong>Support Plans</strong></div>
<section class="card"><h1>Έλεγξε το email σου</h1><p>Στείλαμε έναν 6ψήφιο κωδικό στο <strong><?php echo htmlspecialchars($masked,ENT_QUOTES,'UTF-8'); ?></strong>. Ισχύει για 5 λεπτά.</p>
<form action="<?php echo Route::_('index.php?option=com_supportplans&task=otp.verify&tmpl=component&Itemid=' . (int) $spItemid,false); ?>" method="post">
<input class="otp" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus aria-label="6ψήφιος κωδικός">
<button class="btn" type="submit">Επιβεβαίωση</button><?php echo HTMLHelper::_('form.token'); ?></form>
<form class="resend" action="<?php echo Route::_('index.php?option=com_supportplans&task=otp.send&tmpl=component&Itemid=' . (int) $spItemid,false); ?>" method="post">
<button type="submit">Αποστολή νέου κωδικού</button><?php echo HTMLHelper::_('form.token'); ?></form>
<div class="hint">Για λόγους ασφαλείας επιτρέπονται έως 5 προσπάθειες ανά κωδικό.</div></section>
</main></body></html>

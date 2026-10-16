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

$app = Factory::getApplication();
HTMLHelper::_('behavior.keepalive');
?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($app->getLanguage()->getTag(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Support Plans</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geom:opsz,wght@9..72,300..800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:'Geom',Arial,sans-serif;background:linear-gradient(145deg,#eef4ff 0%,#f8fafc 52%,#eef2ff 100%);color:#172033}
body{min-height:100vh;display:block;padding:28px 24px 24px;overflow-x:hidden}
.login-shell{width:min(100%,430px);position:relative;z-index:2;margin:0 auto}
.brand{display:flex;align-items:center;justify-content:center;gap:10px;margin:0 0 24px}
.brand-icon{width:42px;height:42px;border-radius:12px;background:#2563eb;color:#fff;display:grid;place-items:center}
.brand-icon svg{width:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.login-brand-image{display:flex;align-items:center;justify-content:center;max-width:140px;height:54px}.login-brand-image img{display:block;max-width:140px;max-height:54px;width:auto;height:auto;object-fit:contain}.login-brand-fallback{width:48px;height:48px;border-radius:14px;background:#2563eb;color:#fff;display:grid;place-items:center;font-weight:800;font-size:18px}
.brand-text strong{display:block;font-size:16px}.brand-text span{font-size:12px;color:#7a8493}
.card{background:#fff;border:1px solid #e5e9f0;border-radius:18px;padding:30px;box-shadow:0 24px 70px rgba(30,64,175,.12)}
h1{font-size:24px;letter-spacing:-.5px;margin:0 0 7px}p{font-size:14px;color:#667085;line-height:1.55;margin:0 0 24px}
.field{margin:0 0 16px}.field label{display:block;font-size:13px;font-weight:650;color:#344054;margin:0 0 7px}
.field input{width:100%;height:46px;border:1px solid #d7dde7;border-radius:10px;padding:0 13px;font:inherit;color:#172033;background:#fff;outline:none}
.field input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.submit{width:100%;height:46px;border:0;border-radius:10px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer;margin-top:4px}
.submit:hover{background:#1d4ed8}.note{text-align:center;margin:18px 0 0;font-size:11px;color:#98a2b3}
@media(max-width:480px){body{padding:20px 15px 15px}.card{padding:24px 20px;border-radius:15px}}

/* v1044-login-position: stable position even when browser password UI appears */
@media(max-width:720px){body{padding-top:20px!important}}
</style>
</head>
<body>
<main class="login-shell">
 <div class="brand">
  <?php if($loginIcon): ?>
  <span class="login-brand-image"><img src="<?php echo htmlspecialchars($loginIcon, ENT_QUOTES, 'UTF-8'); ?>" alt="Support Plans"></span>
  <?php else: ?><span class="login-brand-fallback">SP</span><?php endif; ?>
  <span class="brand-text"><strong>Support Plans</strong><span>Workspace</span></span>
 </div>
 <section class="card">
  <h1>Σύνδεση</h1>
  <p>Χρησιμοποίησε τον λογαριασμό σου για πρόσβαση στο Support Plans.</p>
  <form action="<?php echo Route::_('index.php?option=com_supportplans&task=auth.authenticate&tmpl=component&Itemid=' . (int) $spItemid, false); ?>" method="post">
   <div class="field"><label for="sp-user">Username</label><input id="sp-user" name="username" type="text" autocomplete="username" required autofocus></div>
   <div class="field"><label for="sp-pass">Password</label><input id="sp-pass" name="password" type="password" autocomplete="current-password" required></div>
   <button class="submit" type="submit">Σύνδεση</button>
   <?php echo HTMLHelper::_('form.token'); ?>
  </form>
  <div class="note">Μετά το username και password θα σταλεί υποχρεωτικά 6ψήφιος κωδικός στο email του λογαριασμού.</div>
 </section>
</main>
</body>
</html>

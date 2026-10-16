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
$spSession=Factory::getApplication()->getSession();
$email=(string)$spSession->get('supportplans.pending.email','');
$masked=preg_replace('/(^.).*(@.*$)/', '$1••••$2', $email);
// Countdown values come from the session so a page reload keeps the real remaining time.
$spOtpTtl=120;
$spOtpLeft=$spSession->get('supportplans.otp.hash','')!=='' ? max(0,(int)$spSession->get('supportplans.otp.expires',0)-time()) : 0;
$spResendLeft=max(0,60-(time()-(int)$spSession->get('supportplans.otp.last_sent',0)));
?>
<!doctype html><html lang="el"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Support Plans - Επιβεβαίωση</title>
<style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Arial,sans-serif;background:linear-gradient(145deg,#eef4ff,#f8fafc 52%,#eef2ff);color:#172033}
body{min-height:100vh;display:block;padding:28px 24px 24px}
.brand{text-align:center;margin-bottom:22px}.mark.has-image{background:transparent!important;box-shadow:none!important;width:auto!important;max-width:120px!important;height:58px!important}.mark.has-image img{display:block;max-width:120px;max-height:58px;width:auto;height:auto;object-fit:contain;margin:auto}.mark{width:54px;height:54px;border-radius:16px;background:#2563eb;color:#fff;display:grid;place-items:center;margin:0 auto 12px;font-size:22px;font-weight:800;box-shadow:0 12px 28px rgba(37,99,235,.2)}
.card{background:#fff;border:1px solid #e5e9f0;border-radius:18px;padding:30px;box-shadow:0 24px 70px rgba(30,64,175,.12)}
h1{font-size:23px;margin:0 0 8px}p{font-size:14px;color:#667085;line-height:1.55;margin:0 0 22px}
.otp-boxes{display:grid;grid-template-columns:repeat(6,1fr);gap:9px}
.otp-box{width:100%;min-width:0;height:60px;text-align:center;font-size:26px;font-weight:700;color:#172033;border:1px solid #d7dde7;border-radius:12px;outline:none;background:#fff;transition:border-color .15s,box-shadow .15s,background .15s;-moz-appearance:textfield}
.otp-box:nth-child(3){margin-right:6px}.otp-box:nth-child(4){margin-left:6px}
.otp-box:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.14)}
.otp-box.is-filled{background:#f5f8ff;border-color:#b9cdf7}
.otp-boxes.is-expired .otp-box{background:#f2f4f7;color:#98a2b3;border-color:#e4e7ec}
.timer{margin-top:18px}
.timer-track{height:7px;border-radius:99px;background:#eef2f7;overflow:hidden}
.timer-bar{height:100%;width:100%;border-radius:99px;background:#16a34a;transform-origin:left center;transition:background-color .4s}
.timer-bar.is-warn{background:#f59e0b}.timer-bar.is-danger{background:#dc2626}
.timer-text{display:flex;justify-content:space-between;margin-top:7px;font-size:12.5px;color:#667085}
.timer-text strong{font-variant-numeric:tabular-nums;color:#172033}
.timer.is-expired .timer-text strong{color:#dc2626}
.btn:disabled{background:#98a2b3;cursor:not-allowed}
.resend button:disabled{color:#98a2b3;cursor:default}
.expired-note{display:none;margin-top:14px;padding:10px 12px;border-radius:10px;background:#fef3f2;color:#b42318;font-size:13px;text-align:center}
.is-expired-card .expired-note{display:block}
@media(max-width:380px){.otp-boxes{gap:6px}.otp-box{height:52px;font-size:22px}}
.btn{width:100%;height:46px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;margin-top:16px}.btn:hover{background:#1d4ed8}
.resend{margin-top:14px;text-align:center}.resend button{border:0;background:none;color:#2563eb;cursor:pointer;font-weight:650}.hint{text-align:center;font-size:12px;color:#98a2b3;margin-top:18px}

/* v1044-login-position: stable position even when browser password UI appears */
@media(max-width:720px){body{padding-top:20px!important}}
.shell{width:min(100%,430px);margin:0 auto}
</style></head><body><main class="shell">
<div class="brand"><div class="mark<?php echo $loginIcon ? ' has-image' : ''; ?>"><?php if ($loginIcon): ?><img src="<?php echo htmlspecialchars($loginIcon, ENT_QUOTES, 'UTF-8'); ?>" alt="Support Plans"><?php else: ?>SP<?php endif; ?></div><strong>Support Plans</strong></div>
<section class="card" id="otp-card"><h1>Έλεγξε το email σου</h1><p>Στείλαμε έναν 6ψήφιο κωδικό στο <strong><?php echo htmlspecialchars($masked,ENT_QUOTES,'UTF-8'); ?></strong>. Ισχύει για 2 λεπτά.</p>
<form id="otp-form" action="<?php echo Route::_('index.php?option=com_supportplans&task=otp.verify&tmpl=component&Itemid=' . (int) $spItemid,false); ?>" method="post">
<div class="otp-boxes" id="otp-boxes" role="group" aria-label="6ψήφιος κωδικός">
<?php for ($i = 0; $i < 6; $i++): ?><input class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="<?php echo $i === 0 ? 6 : 1; ?>" <?php echo $i === 0 ? 'autocomplete="one-time-code" autofocus' : 'autocomplete="off"'; ?> aria-label="Ψηφίο <?php echo $i + 1; ?>"><?php endfor; ?>
</div>
<input type="hidden" name="otp" id="otp-value">
<div class="timer" id="otp-timer" data-left="<?php echo (int) $spOtpLeft; ?>" data-total="<?php echo (int) $spOtpTtl; ?>">
 <div class="timer-track"><div class="timer-bar" id="otp-timer-bar"></div></div>
 <div class="timer-text"><span id="otp-timer-label">Ο κωδικός λήγει σε</span><strong id="otp-timer-value">2:00</strong></div>
</div>
<button class="btn" type="submit" id="otp-submit">Επιβεβαίωση</button><?php echo HTMLHelper::_('form.token'); ?></form>
<div class="expired-note">Ο κωδικός έληξε. Ζήτησε νέο κωδικό.</div>
<form class="resend" action="<?php echo Route::_('index.php?option=com_supportplans&task=otp.send&tmpl=component&Itemid=' . (int) $spItemid,false); ?>" method="post">
<button type="submit" id="otp-resend" data-wait="<?php echo (int) $spResendLeft; ?>">Αποστολή νέου κωδικού</button><?php echo HTMLHelper::_('form.token'); ?></form>
<div class="hint">Για λόγους ασφαλείας επιτρέπονται έως 5 προσπάθειες ανά κωδικό.</div>
<script>
(function () {
  var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
  var hidden = document.getElementById('otp-value');
  var form = document.getElementById('otp-form');
  var submitBtn = document.getElementById('otp-submit');
  var expired = false, submitted = false;

  function code() { return boxes.map(function (b) { return b.value; }).join(''); }
  function refresh() {
    boxes.forEach(function (b) { b.classList.toggle('is-filled', b.value !== ''); });
    hidden.value = code();
    if (!expired && !submitted && /^\d{6}$/.test(hidden.value)) { submitted = true; form.submit(); }
  }
  // Spreads several digits (paste or SMS/e-mail autofill) over the boxes starting at `from`.
  function fill(from, digits) {
    digits = digits.replace(/\D/g, '');
    for (var i = 0; i < digits.length && from + i < boxes.length; i++) boxes[from + i].value = digits[i];
    var next = Math.min(from + digits.length, boxes.length - 1);
    boxes[next].focus();
    boxes[next].select();
    refresh();
  }
  boxes.forEach(function (box, i) {
    box.addEventListener('input', function () {
      var v = box.value.replace(/\D/g, '');
      if (v.length > 1) { box.value = ''; fill(i, v); return; }
      box.value = v;
      if (v && i < boxes.length - 1) boxes[i + 1].focus();
      refresh();
    });
    box.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !box.value && i > 0) { boxes[i - 1].value = ''; boxes[i - 1].focus(); refresh(); e.preventDefault(); }
      else if (e.key === 'ArrowLeft' && i > 0) { boxes[i - 1].focus(); e.preventDefault(); }
      else if (e.key === 'ArrowRight' && i < boxes.length - 1) { boxes[i + 1].focus(); e.preventDefault(); }
      else if (e.key === 'Enter') { e.preventDefault(); if (/^\d{6}$/.test(code()) && !expired) form.submit(); }
    });
    box.addEventListener('focus', function () { box.select(); });
    box.addEventListener('paste', function (e) {
      var t = (e.clipboardData || window.clipboardData).getData('text');
      if (!t) return;
      e.preventDefault();
      fill(i, t);
    });
  });
  form.addEventListener('submit', function (e) {
    hidden.value = code();
    if (expired || !/^\d{6}$/.test(hidden.value)) { e.preventDefault(); boxes[Math.min(hidden.value.length, 5)].focus(); }
  });

  // Countdown bar
  var timer = document.getElementById('otp-timer');
  var bar = document.getElementById('otp-timer-bar');
  var value = document.getElementById('otp-timer-value');
  var total = parseInt(timer.dataset.total, 10) || 120;
  var endAt = Date.now() + (parseInt(timer.dataset.left, 10) || 0) * 1000;
  function tick() {
    var left = Math.max(0, (endAt - Date.now()) / 1000);
    var secs = Math.ceil(left);
    value.textContent = Math.floor(secs / 60) + ':' + String(secs % 60).padStart(2, '0');
    bar.style.width = (left / total * 100) + '%';
    bar.classList.toggle('is-warn', left <= 60 && left > 20);
    bar.classList.toggle('is-danger', left <= 20);
    if (left <= 0) {
      expired = true;
      document.getElementById('otp-timer-label').textContent = 'Ο κωδικός έληξε';
      timer.classList.add('is-expired');
      document.getElementById('otp-card').classList.add('is-expired-card');
      document.getElementById('otp-boxes').classList.add('is-expired');
      boxes.forEach(function (b) { b.disabled = true; });
      submitBtn.disabled = true;
      return;
    }
    requestAnimationFrame(function () { setTimeout(tick, 250); });
  }
  tick();

  // "Send new code" becomes available after the resend wait.
  var resend = document.getElementById('otp-resend');
  var resendAt = Date.now() + (parseInt(resend.dataset.wait, 10) || 0) * 1000;
  var resendLabel = resend.textContent;
  (function resendTick() {
    var w = Math.ceil((resendAt - Date.now()) / 1000);
    if (w > 0 && !expired) { resend.disabled = true; resend.textContent = resendLabel + ' (' + w + '″)'; setTimeout(resendTick, 500); }
    else { resend.disabled = false; resend.textContent = resendLabel; }
  })();
})();
</script>
</section>
</main></body></html>

<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$v = static fn($k) => (string) Notify::setting($k);
[$fromName, $fromEmail] = Notify::senderOf('greeting');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Ευχές &amp; Αποστολές</span><h1>Ρυθμίσεις ευχών</h1><p>Αποστολέας των ευχών και των μαζικών αποστολών, μήνυμα ονομαστικής εορτής και χρώματα του πλαισίου «Χρόνια Πολλά!».</p></div>
</section>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveGreetings')">Αποθήκευση</button>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveGreetings', false)); ?>" method="post" id="adminForm" enctype="multipart/form-data">
 <div class="sp-card" id="sp-greeting-sender">
  <h2>Αποστολέας ευχών &amp; αποστολών</h2>
  <div class="sp-grid">
   <label>Όνομα αποστολέα<input name="doc_greeting_from_name" value="<?php echo $e($v('doc_greeting_from_name')); ?>" placeholder="<?php echo $e(Notify::brandName()); ?>"></label>
   <label>Email αποστολέα<input type="email" name="doc_greeting_from_email" value="<?php echo $e($v('doc_greeting_from_email')); ?>" placeholder="π.χ. info@<?php echo $e(preg_replace('/^.*@/', '', $fromEmail) ?: 'example.gr'); ?>"></label>
   <label>Απαντήσεις (Reply-To)<input type="email" name="doc_greeting_reply_to" value="<?php echo $e($v('doc_greeting_reply_to')); ?>" placeholder="προαιρετικό"></label>
  </div>
  <p class="sp-n-help">Ισχύει για τις ευχές ονομαστικής εορτής, τις μαζικές αποστολές (κάρτες, ενημερώσεις) και τα δοκιμαστικά τους. Αν μείνει κενό, χρησιμοποιείται ο γενικός αποστολέας των Ρυθμίσεων email. Τώρα φεύγουν από: <b><?php echo $e($fromName . ' <' . $fromEmail . '>'); ?></b>. Το email πρέπει να επιτρέπεται από τον SMTP server σας (ίδιο domain, SPF/DKIM).</p>
 </div>
 <div class="sp-card" id="sp-nameday-template">
  <h2>Πρότυπο: Ευχές ονομαστικής εορτής</h2>
  <label class="sp-check"><input type="hidden" name="notify_nameday_enabled" value="0"><input type="checkbox" name="notify_nameday_enabled" value="1"<?php echo (int) Notify::setting('notify_nameday_enabled') ? ' checked' : ''; ?>> Αυτόματη αποστολή ευχών την ημέρα της ονομαστικής εορτής του πελάτη (στην ώρα αποστολής)</label>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα<input name="notify_nameday_subject" value="<?php echo $e($v('notify_nameday_subject')); ?>"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="notify_nameday_body" rows="8" data-sp-editor><?php echo $e($v('notify_nameday_body')); ?></textarea></div>
   <div class="sp-wide sp-field sp-n-card">
    <span class="sp-field-label">Κάρτα (προαιρετικά)</span>
    <div class="sp-n-card-row">
     <?php if ((string) Notify::setting('notify_nameday_image') !== ''): ?><img src="<?php echo $e(Notify::absUrl((string) Notify::setting('notify_nameday_image'))); ?>" alt=""><?php endif; ?>
     <div><input name="notify_nameday_image" value="<?php echo $e((string) Notify::setting('notify_nameday_image')); ?>" placeholder="images/supportplans/cards/xronia-polla.jpg ή URL">
      <label class="sp-n-upload">ή ανεβάστε εικόνα <input type="file" name="notify_nameday_upload" accept="image/png,image/jpeg,image/gif"></label>
      <small class="sp-n-help">Εμφανίζεται σε όλο το πλάτος του email πάνω από το κείμενο. Για αφαίρεση αδειάστε το πεδίο.</small></div>
    </div>
   </div>
  </div>
  <p class="sp-n-help">Μεταβλητές: <code>{banner}</code> (χρωματιστό πλαίσιο «Χρόνια Πολλά!» με emojis, σε δική του γραμμή) <code>{greeting}</code> <code>{client_name}</code> <code>{client_company}</code> <code>{company}</code>. Emojis μπορείτε να γράψετε απευθείας στο κείμενο (Windows: <kbd>Win</kbd>+<kbd>.</kbd>, Mac: <kbd>Ctrl</kbd>+<kbd>⌘</kbd>+<kbd>Space</kbd>). Η ημερομηνία ορίζεται στην καρτέλα κάθε πελάτη· στέλνεται μία φορά τον χρόνο και όχι σε όσους έχουν διαγραφεί από τις ενημερώσεις.</p>
  <?php
  $bc = static function (string $k): string { $v = strtolower(trim((string) Notify::setting($k))); return preg_match('/^#[0-9a-f]{6}$/', $v) ? $v : (string) Notify::defaults()[$k]; };
  $use3 = preg_match('/^#[0-9a-f]{6}$/i', trim((string) Notify::setting('notify_banner_bg3'))) === 1;
  $grad = (int) Notify::setting('notify_banner_gradient') === 1;
  ?>
  <fieldset class="sp-n-banner" id="sp-n-banner">
   <legend>Χρώματα πλαισίου <code>{banner}</code></legend>
   <div class="sp-n-banner-grid">
    <div class="sp-n-banner-fields">
     <label class="sp-check"><input type="hidden" name="notify_banner_gradient" value="0"><input type="checkbox" name="notify_banner_gradient" value="1" data-b="grad"<?php echo $grad ? ' checked' : ''; ?>> Διαβάθμιση (gradient)</label>
     <div class="sp-n-colors">
      <label><span class="sp-n-solid-only">Φόντο</span><span class="sp-n-grad-only">Χρώμα 1</span><input type="color" name="notify_banner_bg1" data-b="bg1" value="<?php echo $e($bc('notify_banner_bg1')); ?>"></label>
      <label class="sp-n-grad-only">Χρώμα 2<input type="color" name="notify_banner_bg2" data-b="bg2" value="<?php echo $e($bc('notify_banner_bg2')); ?>"></label>
      <label class="sp-n-grad-only"><span><input type="checkbox" name="notify_banner_use3" value="1" data-b="use3"<?php echo $use3 ? ' checked' : ''; ?>> Χρώμα 3</span><input type="color" name="notify_banner_bg3" data-b="bg3" value="<?php echo $e($use3 ? strtolower(trim((string) Notify::setting('notify_banner_bg3'))) : '#ffc93c'); ?>"></label>
      <label>Γράμματα<input type="color" name="notify_banner_text" data-b="text" value="<?php echo $e($bc('notify_banner_text')); ?>"></label>
     </div>
     <button type="button" class="btn btn-sm btn-outline-secondary" data-b="reset">Επαναφορά αρχικών χρωμάτων</button>
    </div>
    <div class="sp-n-banner-demo" data-b="demo"><div>🎈🎉🎂🎁🎊</div><strong>Χρόνια Πολλά!</strong><span>Χαρούμενη γιορτή, γεμάτη χαμόγελα! 🌟</span></div>
   </div>
   <small class="sp-n-help">Στο Outlook για Windows η διαβάθμιση εμφανίζεται ως ένα χρώμα (το μεσαίο). Διαλέξτε γράμματα με αρκετή αντίθεση ώστε να διαβάζονται.</small>
  </fieldset>
  <script>(function(){var r=document.getElementById('sp-n-banner');if(!r)return;var q=function(k){return r.querySelector('[data-b="'+k+'"]');};
  var paint=function(){var g=q('grad').checked,u=q('use3').checked;r.classList.toggle('is-solid',!g);q('bg3').disabled=!u;
   var bg=g?'linear-gradient(135deg,'+q('bg1').value+' 0%,'+q('bg2').value+(u?' 55%,'+q('bg3').value+' 100%':' 100%')+')':q('bg1').value;
   var d=q('demo');d.style.background=bg;d.style.color=q('text').value;};
  r.addEventListener('input',paint);r.addEventListener('change',paint);
  q('reset').addEventListener('click',function(){q('grad').checked=true;q('use3').checked=true;q('bg1').value='#ff5f8f';q('bg2').value='#ff9a3c';q('bg3').value='#ffc93c';q('text').value='#ffffff';paint();});
  paint();})();</script>
  <div class="sp-n-actions">
   <button type="button" class="btn btn-outline-primary" onclick="var f=document.getElementById('adminForm');f.querySelector('input[name=after]').value='nameday_preview';spSubmit('notify.saveGreetings')">Αποθήκευση &amp; προεπισκόπηση</button>
   <button type="button" class="btn btn-outline-secondary" onclick="var f=document.getElementById('adminForm');f.querySelector('input[name=after]').value='nameday_test';spSubmit('notify.saveGreetings')">Αποθήκευση &amp; δοκιμή στο email μου</button>
  </div>
  <?php if ($this->namedayPreview): ?>
  <div class="sp-n-nameday-preview"><dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($this->namedayPreview['to']); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->namedayPreview['subject']); ?></dd></dl>
  <?php echo NotifyUi::preview($this->namedayPreview['body']); ?></div>
  <?php endif; ?>
 </div>
 <input type="hidden" name="after" value=""><input type="hidden" name="task" value="notify.saveGreetings">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

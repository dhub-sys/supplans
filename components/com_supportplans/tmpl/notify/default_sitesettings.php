<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$v = static fn($k) => (string) Notify::setting($k);
[$fromName, $fromEmail] = Notify::senderOf('maintenance');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Ρυθμίσεις</h1><p>Αποστολέας, συχνότητα της σύνοψης και κείμενο του μηνύματος. Κάθε πελάτης λαμβάνει ένα μήνυμα με όλα τα sites του (μία ενότητα και ένας πίνακας ανά site).</p></div>
</section>
<div class="sp-standalone-actions sp-offer-actions"><button type="button" class="btn btn-primary" onclick="spSubmit('sites.saveSettings')">Αποθήκευση</button></div>
<form action="<?php echo $e(OfferHelper::url('task=sites.saveSettings', false)); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2>Αποστολέας</h2>
  <div class="sp-grid">
   <label>Όνομα αποστολέα<input name="doc_maintenance_from_name" value="<?php echo $e($v('doc_maintenance_from_name')); ?>" placeholder="<?php echo $e(Notify::brandName()); ?>"></label>
   <label>Email αποστολέα<input type="email" name="doc_maintenance_from_email" value="<?php echo $e($v('doc_maintenance_from_email')); ?>" placeholder="π.χ. support@…"></label>
   <label>Απαντήσεις (Reply-To)<input type="email" name="doc_maintenance_reply_to" value="<?php echo $e($v('doc_maintenance_reply_to')); ?>" placeholder="προαιρετικό"></label>
  </div>
  <p class="sp-n-help">Κενό = ο γενικός αποστολέας των Ρυθμίσεων email. Τώρα φεύγουν από: <b><?php echo $e($fromName . ' <' . $fromEmail . '>'); ?></b>.</p>
 </div>
 <?php $freq = \Support\Component\Supportplans\Site\Helper\Sites::frequency(); $opts = static function (string $key, int $sel) use ($e) { $h = ''; for ($d = 1; $d <= 28; $d++) $h .= '<option value="' . $d . '"' . ($d === $sel ? ' selected' : '') . '>' . $d . 'η του μήνα</option>'; return $h; }; ?>
 <div class="sp-card" id="sp-digest-freq">
  <h2>Σύνοψη πελατών · συχνότητα</h2>
  <div class="sp-grid">
   <label>Συχνότητα<select name="notify_sitedigest_freq" id="sp-df"><?php foreach (\Support\Component\Supportplans\Site\Helper\Sites::FREQUENCIES as $k => $l): ?><option value="<?php echo $k; ?>"<?php echo $freq === $k ? ' selected' : ''; ?>><?php echo $e($l); ?></option><?php endforeach; ?></select></label>
   <label data-f="weekly">Ημέρα της εβδομάδας<select name="notify_sitedigest_weekday"><?php foreach (\Support\Component\Supportplans\Site\Helper\Sites::WEEKDAYS as $k => $l): ?><option value="<?php echo $k; ?>"<?php echo (int) ($v('notify_sitedigest_weekday') ?: 1) === $k ? ' selected' : ''; ?>><?php echo $e($l); ?></option><?php endforeach; ?></select></label>
   <label data-f="monthly twice quarterly"><span data-f="twice">1η αποστολή</span><span data-f="monthly quarterly">Ημέρα</span><select name="notify_sitedigest_day"><?php echo $opts('d1', max(1, (int) $v('notify_sitedigest_day'))); ?></select></label>
   <label data-f="twice">2η αποστολή<select name="notify_sitedigest_day2"><?php echo $opts('d2', max(1, (int) ($v('notify_sitedigest_day2') ?: 15))); ?></select></label>
  </div>
  <p class="sp-n-help">Από την ώρα αποστολής (<?php echo $e(Notify::sendTimeLabel() ?: '—'); ?>) της ημέρας που ορίζετε, κάθε πελάτης λαμβάνει ένα μήνυμα με όσα καταγράφηκαν στα sites του από την προηγούμενη σύνοψη έως την προηγούμενη ημέρα. Κάθε update εμφανίζεται με την ημερομηνία του και στο τέλος υπάρχει το σύνολο. Η περίοδος (<code>{period}</code>) γράφεται «Νοέμβριος 2026» όταν όλα είναι μέσα στον ίδιο μήνα, αλλιώς «01/11/2026 – 14/11/2026». Η σύνοψη στέλνεται και χειροκίνητα, από την καρτέλα «Σύνοψη πελατών».</p>
 </div>
 <script>(function(){var s=document.getElementById('sp-df');if(!s)return;var f=function(){document.querySelectorAll('#sp-digest-freq [data-f]').forEach(function(el){el.hidden=(' '+el.getAttribute('data-f')+' ').indexOf(' '+s.value+' ')<0;});};s.addEventListener('change',f);f();})();</script>
 <div class="sp-card">
  <h2>Μήνυμα ενημερώσεων</h2>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα<input name="notify_siteupdate_subject" value="<?php echo $e($v('notify_siteupdate_subject')); ?>"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="notify_siteupdate_body" rows="12" data-sp-editor><?php echo $e($v('notify_siteupdate_body')); ?></textarea></div>
  </div>
  <p class="sp-n-help">Μεταβλητές: <code>{period}</code> (π.χ. «Νοέμβριος 2026» ή «01/11/2026 – 14/11/2026») <code>{updates}</code> (πίνακας με τις ενημερώσεις του site, σε δική του γραμμή) <code>{site_phrase}</code> («στην ιστοσελίδα σας example.gr» ή «στις ιστοσελίδες σας») <code>{site_of}</code> («της ιστοσελίδας σας» / «των ιστοσελίδων σας») <code>{site}</code> (το domain ή «a.gr και b.gr») <code>{cms}</code> <code>{updates_count}</code> <code>{date}</code> <code>{greeting}</code> <code>{client_name}</code> <code>{client_company}</code> <code>{company}</code>.</p>
 </div>
 <input type="hidden" name="task" value="sites.saveSettings">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="sp-card">
  <h2>Προεπισκόπηση <small class="text-muted">(παράδειγμα για το <?php echo $e($this->sitePreview['site']); ?>)</small></h2>
  <dl class="sp-n-meta"><dt>Θέμα</dt><dd><?php echo $e($this->sitePreview['subject']); ?></dd></dl>
  <?php echo NotifyUi::preview($this->sitePreview['body']); ?>
</div>

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
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Ρυθμίσεις</h1><p>Αποστολέας, μηνιαία σύνοψη και κείμενο του μηνύματος. Κάθε πελάτης λαμβάνει ένα μήνυμα με όλα τα sites του (μία ενότητα και ένας πίνακας ανά site).</p></div>
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
 <div class="sp-card">
  <h2>Μηνιαία σύνοψη</h2>
  <div class="sp-grid">
   <label>Αυτόματη αποστολή την ημέρα του μήνα<select name="notify_sitedigest_day"><option value="0"<?php echo (int) $v('notify_sitedigest_day') === 0 ? ' selected' : ''; ?>>Ποτέ (μόνο χειροκίνητα)</option><?php for ($d = 1; $d <= 28; $d++): ?><option value="<?php echo $d; ?>"<?php echo (int) $v('notify_sitedigest_day') === $d ? ' selected' : ''; ?>><?php echo $d; ?>η του μήνα</option><?php endfor; ?></select></label>
  </div>
  <p class="sp-n-help">Την ημέρα αυτή, από την ώρα αποστολής (<?php echo $e(Notify::sendTimeLabel() ?: '—'); ?>), κάθε πελάτης λαμβάνει ένα μήνυμα ανά site με όλες τις ενημερώσεις του προηγούμενου μήνα. Κάθε update εμφανίζεται με την ημερομηνία του (π.χ. 03/11 YOOtheme Pro 5.5.3 → 5.5.4, 17/11 5.5.4 → 5.5.5) και στο τέλος το σύνολο των ενημερώσεων. Η σύνοψη μπορεί να σταλεί και χειροκίνητα από την καρτέλα «Μηνιαία σύνοψη».</p>
 </div>
 <div class="sp-card">
  <h2>Μήνυμα ενημερώσεων</h2>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα<input name="notify_siteupdate_subject" value="<?php echo $e($v('notify_siteupdate_subject')); ?>"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="notify_siteupdate_body" rows="12" data-sp-editor><?php echo $e($v('notify_siteupdate_body')); ?></textarea></div>
  </div>
  <p class="sp-n-help">Μεταβλητές: <code>{period}</code> (π.χ. «Νοέμβριος 2026» στη μηνιαία σύνοψη) <code>{updates}</code> (πίνακας με τις ενημερώσεις του site, σε δική του γραμμή) <code>{site_phrase}</code> («στην ιστοσελίδα σας example.gr» ή «στις ιστοσελίδες σας») <code>{site_of}</code> («της ιστοσελίδας σας» / «των ιστοσελίδων σας») <code>{site}</code> (το domain ή «a.gr και b.gr») <code>{cms}</code> <code>{updates_count}</code> <code>{date}</code> <code>{greeting}</code> <code>{client_name}</code> <code>{client_company}</code> <code>{company}</code>.</p>
 </div>
 <input type="hidden" name="task" value="sites.saveSettings">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="sp-card">
  <h2>Προεπισκόπηση <small class="text-muted">(παράδειγμα για το <?php echo $e($this->sitePreview['site']); ?>)</small></h2>
  <dl class="sp-n-meta"><dt>Θέμα</dt><dd><?php echo $e($this->sitePreview['subject']); ?></dd></dl>
  <?php echo NotifyUi::preview($this->sitePreview['body']); ?>
</div>

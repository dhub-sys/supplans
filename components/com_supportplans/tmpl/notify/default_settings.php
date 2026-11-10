<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$v = static fn($k) => (string) Notify::setting($k);
$me = (string) Factory::getApplication()->getSession()->get('supportplans.auth.email', '');
$vars = '<code>{client_name}</code> <code>{client_company}</code> <code>{company}</code> <code>{service}</code> <code>{label}</code> <code>{expiry_date}</code> <code>{days_left}</code> <code>{expiry_phrase}</code> <code>{price}</code> <code>{duration}</code> <code>{price_line}</code>';
?>
<div class="sp-standalone-actions sp-offer-actions"><button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveSettings')">Αποθήκευση ρυθμίσεων</button></div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveSettings')); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2>Αποστολέας</h2>
  <div class="sp-grid">
   <label>Όνομα αποστολέα<input name="notify_from_name" value="<?php echo $e($v('notify_from_name')); ?>" placeholder="<?php echo $e(Notify::brandName()); ?>"></label>
   <label>Email αποστολέα<input type="email" name="notify_from_email" value="<?php echo $e($v('notify_from_email')); ?>" placeholder="<?php echo $e(Factory::getApplication()->get('mailfrom')); ?>"></label>
   <label>Reply-To<input type="email" name="notify_reply_to" value="<?php echo $e($v('notify_reply_to')); ?>"></label>
   <label>Κρυφή κοινοποίηση υπενθυμίσεων<input type="email" name="notify_bcc" value="<?php echo $e($v('notify_bcc')); ?>" placeholder="αντίγραφο σε εσάς"></label>
  </div>
  <p class="sp-n-help">Κενά πεδία = οι ρυθμίσεις email του Joomla (Καθολική ρύθμιση → Διακομιστής → Ταχυδρομείο). Χρησιμοποιήστε SMTP και διεύθυνση στο domain σας, με σωστά SPF, DKIM και DMARC, ώστε τα μηνύματα να μην πηγαίνουν στα spam.</p>
 </div>
 <div class="sp-card">
  <h2>Υπενθυμίσεις &amp; ουρά</h2>
  <div class="sp-grid">
   <label>Ημέρες υπενθύμισης (προεπιλογή)<input name="notify_reminder_days" value="<?php echo $e($v('notify_reminder_days')); ?>"></label>
   <label>Μηνύματα ανά εκτέλεση<input type="number" min="1" max="200" name="notify_batch_size" value="<?php echo (int) $v('notify_batch_size'); ?>"></label>
   <label class="sp-wide">Υποσέλιδο email<textarea name="notify_footer" rows="3" placeholder="Κενό = τα στοιχεία επιχείρησης από τις Ρυθμίσεις"><?php echo $e($v('notify_footer')); ?></textarea></label>
  </div>
  <p class="sp-n-help">Ημέρες χωρισμένες με κόμμα: <code>0</code> = την ημέρα λήξης, αρνητικό = μετά τη λήξη. Κάθε υπηρεσία του καταλόγου ή του πελάτη μπορεί να έχει δικές της. Κάθε υπενθύμιση στέλνεται μία φορά· αν η εργασία δεν τρέξει για μέρες, στέλνεται μόνο η πιο πρόσφατη.</p>
 </div>
 <div class="sp-card">
  <h2>Πρότυπο: Υπενθύμιση ανανέωσης</h2>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα<input name="notify_renewal_subject" value="<?php echo $e($v('notify_renewal_subject')); ?>"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="notify_renewal_body" rows="12" data-sp-editor><?php echo $e($v('notify_renewal_body')); ?></textarea></div>
  </div>
  <p class="sp-n-help">Μεταβλητές: <?php echo $vars; ?>. <code>{expiry_phrase}</code> = «λήγει σε 7 ημέρες», «λήγει σήμερα», «έληξε χθες»… <code>{price_line}</code> = «Κόστος ανανέωσης: …» ή τίποτα αν δεν υπάρχει τιμή.</p>
 </div>
 <div class="sp-card">
  <h2>Πρότυπο: Αναφορά maintenance</h2>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα<input name="notify_report_subject" value="<?php echo $e($v('notify_report_subject')); ?>"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="notify_report_body" rows="12" data-sp-editor><?php echo $e($v('notify_report_body')); ?></textarea></div>
  </div>
  <p class="sp-n-help">Μεταβλητές: <?php echo $vars; ?> <code>{period}</code>, και σε δική τους γραμμή <code>{summary}</code> (σύνοψη εργασιών) και <code>{updates}</code> (πίνακας ενημερώσεων).</p>
 </div>
 <input type="hidden" name="task" value="notify.saveSettings">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="sp-card">
  <h2>Δοκιμαστική αποστολή</h2>
  <form method="post" action="<?php echo $e(OfferHelper::url('task=notify.sendTest')); ?>" class="sp-n-filters">
    <input type="email" name="to" required value="<?php echo $e($me); ?>">
    <?php echo NotifyUi::select('template', ['renewal' => 'Υπενθύμιση ανανέωσης', 'report' => 'Αναφορά maintenance'], 'renewal'); ?>
    <?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-primary">Αποστολή δοκιμαστικού</button>
  </form>
  <p class="sp-n-help">Στέλνει το αποθηκευμένο πρότυπο με ενδεικτικά στοιχεία.</p>
</div>
<div class="sp-card">
  <h2>Αυτόματη εκτέλεση</h2>
  <ol>
    <li>Joomla Administrator → <b>Σύστημα → Χρονοπρογραμματισμένες εργασίες → Νέα</b> → <b>Support Plans – Ειδοποιήσεις</b>. Επανάληψη: κάθε 5 λεπτά.</li>
    <li>Για αξιόπιστη εκτέλεση (ανεξάρτητα από επισκέψεις), cron στο hosting κάθε 5 λεπτά:<br><code>php <?php echo $e(JPATH_ROOT); ?>/cli/joomla.php scheduler:run --all</code><br>
    ή, αν δεν υπάρχει πρόσβαση σε PHP CLI, ενεργοποιήστε το Web Cron στις επιλογές των Χρονοπρογραμματισμένων εργασιών και καλέστε το URL του.</li>
  </ol>
  <p class="sp-n-help">Τελευταία εκτέλεση: <?php echo $e(Notify::datetimeGr((string) $this->params->get('notify_last_run', ''))); ?>.</p>
</div>

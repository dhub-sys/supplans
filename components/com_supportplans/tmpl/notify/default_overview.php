<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$lastRun = (string) $this->params->get('notify_last_run', '');
$stale = $lastRun === '' || strtotime($lastRun . ' UTC') < time() - 2 * 3600;
$expired = count(array_filter($this->rows, static fn($s) => Notify::daysUntil($s->expiry_date) < 0));
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Client Management</span><h1>Επισκόπηση</h1><p>Υπηρεσίες που λήγουν, αυτόματες υπενθυμίσεις ανανέωσης, αναφορές maintenance και ενημερώσεις προς πελάτες.</p></div>
  <div class="sp-head-actions">
    <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=services&mode=new'); ?>">＋ Ανάθεση υπηρεσίας</a>
    <a class="sp-primary-action is-secondary" href="<?php echo NotifyUi::url('section=clients&mode=new'); ?>">＋ Νέος πελάτης</a>
  </div>
</section>
<?php if (!$this->catalog): ?>
<div class="sp-card"><h2>Ξεκινήστε από τον κατάλογο</h2><p>Δημιουργήστε πρώτα τις υπηρεσίες που προσφέρετε (π.χ. «Web Hosting Green», «Ανανέωση Domain .gr») με τιμή, διάρκεια και ημέρες υπενθύμισης. Μετά προσθέστε πελάτες και αναθέστε τους υπηρεσίες· οι υπενθυμίσεις προγραμματίζονται αυτόματα.</p>
<a class="btn btn-primary" href="<?php echo NotifyUi::url('section=catalog&mode=new'); ?>">＋ Νέα υπηρεσία καταλόγου</a></div>
<?php endif; ?>
<?php if ($stale): ?>
<div class="sp-n-warning"><strong>Οι αυτόματες ειδοποιήσεις δεν έχουν τρέξει <?php echo $lastRun === '' ? 'ακόμα' : 'τις τελευταίες 2 ώρες'; ?>.</strong>
Στο Joomla Administrator: <em>Σύστημα → Χρονοπρογραμματισμένες εργασίες → Νέα → «Back Office – Ειδοποιήσεις»</em>, εκτέλεση κάθε 5 λεπτά. Για αξιόπιστη λειτουργία ρυθμίστε και cron στο hosting (δείτε τις Οδηγίες).</div>
<?php endif; ?>
<section class="sp-kpis sp-n-kpis">
  <article><span>Ενεργοί πελάτες</span><strong><?php echo count($this->clients); ?></strong><small>με δυνατότητα αποστολής</small></article>
  <article><span>Λήγουν σε 60 ημέρες</span><strong><?php echo count($this->rows) - $expired; ?></strong><small>ενεργές υπηρεσίες</small></article>
  <article><span>Ληγμένες</span><strong><?php echo $expired; ?></strong><small>χωρίς ανανέωση</small></article>
  <article><span>Σε αναμονή / Απέτυχαν</span><strong><?php echo (int) $this->counts['queued']; ?> / <?php echo (int) $this->counts['failed']; ?></strong><small>τελευταία εκτέλεση: <?php echo $e(Notify::datetimeGr($lastRun)); ?></small></article>
</section>
<div class="sp-n-actions"><?php echo NotifyUi::button('runNow', ['back' => 'section=overview'], 'Έλεγχος λήξεων & αποστολή τώρα', '', 'btn btn-outline-primary'); ?>
<?php if (Notify::sendTimeLabel() !== ''): ?><span class="sp-n-help" style="align-self:center">Αυτόματες υπενθυμίσεις καθημερινά από τις <b><?php echo $e(Notify::sendTimeLabel()); ?></b> · <?php echo Notify::sendTimeReached() ? 'οι σημερινές έχουν προγραμματιστεί' : 'σήμερα στις ' . $e(Notify::sendTimeLabel()); ?></span><?php endif; ?></div>
<div class="sp-card">
  <h2>Λήγουν τις επόμενες 60 ημέρες ή έχουν λήξει</h2>
  <?php echo $this->loadTemplate('servicestable'); ?>
</div>

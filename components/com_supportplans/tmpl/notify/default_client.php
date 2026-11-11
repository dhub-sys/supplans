<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$c = $this->selected;
$v = static fn($k, $d = '') => $c ? $c->$k : $d;
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveClient')">Αποθήκευση</button>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=clients'); ?>">Κλείσιμο</a>
 <?php if ($c): ?><a class="btn btn-outline-primary" href="<?php echo NotifyUi::url('section=services&mode=new&client_id=' . (int) $c->id); ?>">＋ Ανάθεση υπηρεσίας</a><?php endif; ?>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveClient', false)); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $c ? $e(Notify::clientLabel($c)) : 'Νέος πελάτης'; ?></h2>
  <div class="sp-grid">
   <label>Ονοματεπώνυμο<input name="name" value="<?php echo $e($v('name')); ?>"></label>
   <label>Εταιρεία<input name="company" value="<?php echo $e($v('company')); ?>"></label>
   <label>Email *<input type="email" name="email" required value="<?php echo $e($v('email')); ?>"></label>
   <label>Κατάσταση<?php echo NotifyUi::select('status', Notify::CLIENT_STATUSES, $v('status', 'active')); ?></label>
   <label class="sp-wide">Προσφώνηση στα email<input name="salutation" maxlength="190" value="<?php echo $e($v('salutation')); ?>" placeholder="<?php echo $e(Notify::setting('notify_greeting')); ?> — π.χ. Αγαπητέ κύριε Δημητρίου"><small class="sp-n-help">Χωρίς κόμμα. Αν μείνει κενό, χρησιμοποιείται η γενική προσφώνηση των ρυθμίσεων email.</small></label>
   <label class="sp-wide">Σημειώσεις<textarea name="notes" rows="3"><?php echo $e($v('notes')); ?></textarea></label>
  </div>
  <p class="sp-foot">Σε ανενεργούς πελάτες δεν αποστέλλεται κανένα μήνυμα.</p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($c->id ?? 0); ?>"><input type="hidden" name="task" value="notify.saveClient">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($c): ?>
<div class="sp-card">
  <h2>Ενημερωτικά μηνύματα</h2>
  <p><?php if ($c->unsubscribed_at): ?>
    <span class="sp-offer-status">Διαγράφηκε στις <?php echo $e(Notify::datetimeGr($c->unsubscribed_at)); ?></span>
    <?php echo NotifyUi::button('unsubscribe', ['id' => $c->id, 'value' => 0], 'Επανεγγραφή', 'Επανεγγραφή μόνο αν ο πελάτης το ζήτησε. Συνέχεια;', 'btn btn-sm btn-outline-secondary'); ?>
  <?php else: ?>
    <span class="sp-offer-status is-accepted">Λαμβάνει</span>
    <?php echo NotifyUi::button('unsubscribe', ['id' => $c->id, 'value' => 1], 'Διαγραφή από ενημερώσεις', '', 'btn btn-sm btn-outline-secondary'); ?>
  <?php endif; ?></p>
  <p class="sp-n-help">Οι υπενθυμίσεις ανανέωσης και οι αναφορές maintenance αφορούν υπηρεσίες του πελάτη και στέλνονται πάντα· η διαγραφή ισχύει μόνο για τις «Αποστολές».</p>
</div>
<div class="sp-card">
  <div class="sp-title-row"><h2>Υπηρεσίες</h2><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=services&mode=new&client_id=' . (int) $c->id); ?>">＋ Ανάθεση υπηρεσίας</a></div>
  <?php echo $this->loadTemplate('servicestable'); ?>
</div>
<div class="sp-card">
  <h2>Υπενθυμίσεις ανανέωσης</h2>
  <p class="sp-n-help">Οι υπηρεσίες με λήξεις σε απόσταση έως <?php echo (int) Notify::setting('notify_group_days'); ?> ημερών μεταξύ τους στέλνονται μαζί, σε ένα μήνυμα. Όταν ο πελάτης πληρώσει, πατήστε «Πληρώθηκε»: η λήξη μετακινείται και οι υπενθυμίσεις σταματούν. Για ημερομηνία πληρωμής, ποσό ή διαφορετική νέα λήξη, ανοίξτε την υπηρεσία.</p>
  <?php if (!$this->groups): ?><p class="text-muted">Δεν υπάρχουν υπηρεσίες με αυτόματες υπενθυμίσεις στο επόμενο διάστημα.</p><?php endif; ?>
  <?php foreach ($this->groups as $i => $g): ?><?php $next = Notify::nextReminderDate($g); ?>
  <div class="sp-n-group">
    <div><strong><?php echo count($g) > 1 ? 'Συνδυαστική υπενθύμιση' : 'Υπενθύμιση'; ?></strong> · επόμενη αποστολή: <strong><?php echo $e($next === null ? 'καμία' : (($next === Notify::today() ? 'σήμερα' : Notify::dateGr($next)) . (Notify::sendTimeLabel() !== '' ? ' ' . Notify::sendTimeLabel() : ''))); ?></strong>
      <ul><?php foreach ($g as $x): ?><li><?php echo $e(Notify::serviceFull($x)); ?> — <?php echo NotifyUi::expiry($x->expiry_date, $x->status); ?></li><?php endforeach; ?></ul></div>
    <div class="sp-row-actions"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=clients&id=' . (int) $c->id . '&preview=' . ($i + 1)); ?>#sp-n-preview">Προεπισκόπηση</a>
      <?php echo NotifyUi::button('remindNow', ['id' => $g[0]->id, 'back' => 'section=clients&id=' . (int) $c->id], 'Αποστολή τώρα', 'Αποστολή της υπενθύμισης στο ' . $c->email . ' τώρα;', 'btn btn-sm btn-outline-secondary'); ?>
      <form class="sp-inline-form" method="post" action="<?php echo $e(OfferHelper::url('task=notify.renewGroup', false)); ?>" onsubmit="return confirm(<?php echo $e(json_encode('Καταχώρηση πληρωμής για ' . (count($g) > 1 ? 'όλες τις ' . count($g) . ' υπηρεσίες' : 'την υπηρεσία') . '; Κάθε μία ανανεώνεται κατά τη δική της διάρκεια και οι υπενθυμίσεις σταματούν.', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>);">
        <?php foreach ($g as $x): ?><input type="hidden" name="ids[]" value="<?php echo (int) $x->id; ?>"><?php endforeach; ?>
        <input type="hidden" name="client_id" value="<?php echo (int) $c->id; ?>"><?php echo HTMLHelper::_('form.token'); ?>
        <button class="btn btn-sm btn-primary"><?php echo count($g) > 1 ? 'Πληρώθηκαν όλες' : 'Πληρώθηκε'; ?></button></form></div>
  </div>
  <?php endforeach; ?>
  <?php if ($this->preview): ?>
  <div id="sp-n-preview"><dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($this->preview['to']); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->preview['subject']); ?></dd></dl>
  <?php echo NotifyUi::preview($this->preview['body']); ?></div>
  <?php endif; ?>
</div>
<div class="sp-card" id="sp-n-remind">
  <h2>Χειροκίνητη υπενθύμιση ανανέωσης</h2>
  <?php if (!$this->remindable): ?><p class="text-muted">Ο πελάτης δεν έχει ενεργές υπηρεσίες με ημερομηνία λήξης.</p><?php else: ?>
  <p class="sp-n-help">Επιλέξτε ποιες υπηρεσίες θα μπουν <b>σε ένα μήνυμα</b>: όσες τσεκάρετε, ανεξάρτητα από το πόσο απέχουν οι λήξεις τους. Κάθε υπηρεσία γράφεται με τη σωστή διατύπωση (λήγει / λήγει σήμερα / έληξε) και μπαίνει στην οικονομική σύνοψη.</p>
  <form method="post" action="<?php echo $e(OfferHelper::url('task=notify.remindNow', false)); ?>">
   <div class="sp-n-checklist">
    <?php foreach ($this->remindable as $x): ?>
    <label class="sp-check"><input type="checkbox" name="ids[]" value="<?php echo (int) $x->id; ?>" <?php echo in_array((int) $x->id, $this->remindSelected, true) ? 'checked' : ''; ?>>
      <span><strong><?php echo $e(Notify::serviceFull($x)); ?></strong> <?php echo NotifyUi::expiry($x->expiry_date, $x->status); ?><?php if ($x->price !== null): ?> <small class="text-muted"><?php echo $e(Notify::money($x->price)); ?> + ΦΠΑ</small><?php endif; ?></span></label>
    <?php endforeach; ?>
   </div>
   <input type="hidden" name="client_id" value="<?php echo (int) $c->id; ?>"><input type="hidden" name="back" value="section=clients&id=<?php echo (int) $c->id; ?>">
   <?php echo HTMLHelper::_('form.token'); ?>
   <div class="sp-n-actions">
    <button class="btn btn-outline-primary" formaction="<?php echo $e(NotifyUi::url('section=clients&id=' . (int) $c->id, false) . '#sp-n-remind'); ?>" onclick="this.form.querySelectorAll('input[name=&quot;ids[]&quot;]').forEach(function(i){i.name='rp_ids[]';});">Προεπισκόπηση</button>
    <button class="btn btn-primary" onclick="return this.form.querySelector('input[name=&quot;ids[]&quot;]:checked') ? confirm('Αποστολή της υπενθύμισης στο <?php echo $e(addslashes($c->email)); ?>;') : (alert('Επιλέξτε τουλάχιστον μία υπηρεσία.'), false);">Αποστολή τώρα</button>
   </div>
  </form>
  <?php if ($this->remindPreview): ?>
  <dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($c->email); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->remindPreview['subject']); ?></dd></dl>
  <?php echo NotifyUi::preview($this->remindPreview['body']); ?>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php $payments = Notify::clientRenewals((int) $c->id); ?>
<?php if ($payments): ?>
<div class="sp-card" id="sp-n-payments">
  <h2>Πρόσφατες πληρωμές</h2>
  <p class="sp-n-help">Αν καταχωρήσατε πληρωμή κατά λάθος, πατήστε «Αναίρεση»: η υπηρεσία επιστρέφει στην προηγούμενη λήξη και οι υπενθυμίσεις συνεχίζουν. Αναιρείται η τελευταία πληρωμή κάθε υπηρεσίας, εφόσον η λήξη δεν άλλαξε από τότε.</p>
  <div class="table-responsive"><table class="table sp-offer-table">
   <thead><tr><th>Πληρωμή</th><th>Υπηρεσία</th><th>Λήξη πριν</th><th>Νέα λήξη</th><th class="sp-num">Ποσό</th><th></th></tr></thead>
   <tbody><?php foreach ($payments as $r): ?><tr><td><?php echo $e(Notify::dateGr($r->paid_on)); ?></td><td><?php echo $e(Notify::serviceFull($r)); ?><?php if ($r->note !== ''): ?><br><small class="text-muted"><?php echo $e($r->note); ?></small><?php endif; ?></td>
    <td><?php echo $e(Notify::dateGr($r->old_expiry)); ?></td><td><?php echo $e(Notify::dateGr($r->new_expiry)); ?></td><td class="sp-num"><?php echo $e(Notify::money($r->amount) ?: '—'); ?></td>
    <td class="sp-num sp-row-actions"><?php if (Notify::canUndo($r, $r->current_expiry)): ?><?php echo NotifyUi::button('undoRenewal', ['id' => $r->id, 'back' => 'section=clients&id=' . (int) $c->id], 'Αναίρεση', 'Αναίρεση της πληρωμής για ' . Notify::serviceFull($r) . '; Η λήξη θα επανέλθει στις ' . Notify::dateGr($r->old_expiry) . '.', 'btn btn-sm btn-outline-danger'); ?><?php endif; ?></td></tr><?php endforeach; ?></tbody>
  </table></div>
</div>
<?php endif; ?>
<div class="sp-card">
  <h2>Τελευταία μηνύματα</h2>
  <?php echo $this->loadTemplate('logtable'); ?>
</div>
<div class="sp-card">
  <h2>Διαγραφή πελάτη</h2>
  <p class="sp-n-help">Διαγράφονται μαζί οι υπηρεσίες, οι αναφορές και το ιστορικό μηνυμάτων του πελάτη.</p>
  <?php echo NotifyUi::button('deleteClient', ['id' => $c->id], 'Διαγραφή πελάτη', 'Οριστική διαγραφή του πελάτη και όλων των δεδομένων του;', 'btn btn-outline-danger'); ?>
</div>
<?php endif; ?>

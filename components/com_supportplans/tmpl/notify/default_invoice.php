<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$i = $this->selected;
$v = static fn($k, $d = '') => $i ? (string) ($i->$k ?? $d) : $d;
$interval = (int) ($i->interval_days ?? Notify::setting('notify_invoice_interval'));
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveInvoice')"><?php echo $i ? 'Αποθήκευση' : 'Καταχώρηση'; ?></button>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=invoices'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveInvoice', false)); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <div class="sp-title-row"><h2><?php echo $i ? 'Παραστατικό ' . $e($i->invoice_number) : 'Νέο παραστατικό'; ?></h2><?php if ($i): ?><?php echo $i->status === 'paid' ? '<span class="sp-offer-status is-accepted">Εξοφλήθηκε ' . $e(Notify::dateGr($i->paid_on)) . '</span>' : '<span class="sp-offer-status sp-n-warn">Ανεξόφλητο</span>'; ?><?php endif; ?></div>
  <div class="sp-grid">
   <label>Email πελάτη *<input type="email" name="email" value="<?php echo $e($v('email')); ?>" list="sp-inv-clients" required autocomplete="off"></label>
   <datalist id="sp-inv-clients"><?php foreach ($this->clients as $c): if (trim((string) $c->email) === '') continue; ?><option value="<?php echo $e($c->email); ?>"><?php echo $e(Notify::clientLabel($c)); ?></option><?php endforeach; ?></datalist>
   <label>Πελάτης<input name="name" value="<?php echo $e($v('name')); ?>" placeholder="προαιρετικό — όνομα ή επωνυμία"></label>
   <label>Αριθμός παραστατικού *<input name="invoice_number" value="<?php echo $e($v('invoice_number')); ?>" required></label>
   <label>Ημερομηνία παραστατικού *<input type="date" name="invoice_date" value="<?php echo $e($v('invoice_date', Notify::today())); ?>" required></label>
   <label>Ποσό (€) *<input name="amount" inputmode="decimal" value="<?php echo $i ? $e(number_format((float) $i->amount, 2, ',', '')) : ''; ?>" required placeholder="π.χ. 248,00"></label>
   <label>Υπενθύμιση κάθε (ημέρες)<input type="number" name="interval_days" min="1" max="365" value="<?php echo $interval; ?>" required></label>
   <?php if (!$i): ?>
   <div class="sp-wide sp-field"><span class="sp-field-label">1η υπενθύμιση</span>
    <div class="sp-inv-start">
     <label class="sp-check"><input type="radio" name="start" value="now" checked> Αποστολή τώρα</label>
     <label class="sp-check"><input type="radio" name="start" value="date"> Στις <input type="date" name="first_send" value="<?php echo $e(Notify::addDays(Notify::today(), 1)); ?>" onfocus="this.form.start.value='date'"></label>
    </div>
   </div>
   <?php elseif ($i->status === 'open'): ?>
   <label>Επόμενη υπενθύμιση<input type="date" name="next_send" value="<?php echo $e($v('next_send')); ?>"></label>
   <?php endif; ?>
   <label class="sp-wide">Σημειώσεις (δεν εμφανίζονται στο email)<textarea name="notes" rows="2"><?php echo $e($v('notes')); ?></textarea></label>
  </div>
  <p class="sp-n-help">Οι αυτόματες υπενθυμίσεις φεύγουν<?php echo Notify::sendTimeLabel() !== '' ? ' καθημερινά από τις ' . $e(Notify::sendTimeLabel()) : ''; ?>, ανά τις ημέρες που ορίσατε από την προηγούμενη, μέχρι να πατήσετε «Πληρώθηκε». Το κείμενο του email και ο αποστολέας ορίζονται στις Ανανεώσεις → Ρυθμίσεις email.</p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($i->id ?? 0); ?>"><input type="hidden" name="task" value="notify.saveInvoice">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($i): ?>
<div class="sp-card">
 <div class="sp-title-row"><h3>Υπενθυμίσεις που στάλθηκαν</h3>
  <div class="sp-row-actions">
   <?php if ($i->status === 'open'): ?>
    <?php echo NotifyUi::button('sendInvoice', ['id' => $i->id, 'back' => 'section=invoices&id=' . (int) $i->id], 'Αποστολή τώρα', 'Αποστολή υπενθύμισης στο ' . $i->email . ' τώρα;', 'btn btn-sm btn-outline-secondary'); ?>
    <?php echo NotifyUi::button('invoicePaid', ['id' => $i->id, 'back' => 'section=invoices&id=' . (int) $i->id], 'Πληρώθηκε', 'Να σημειωθεί ως εξοφλημένο; Οι υπενθυμίσεις σταματούν.', 'btn btn-sm btn-primary'); ?>
   <?php else: ?>
    <?php echo NotifyUi::button('reopenInvoice', ['id' => $i->id, 'back' => 'section=invoices&id=' . (int) $i->id], 'Επαναφορά σε ανεξόφλητο', '', 'btn btn-sm btn-outline-secondary'); ?>
   <?php endif; ?>
  </div>
 </div>
 <?php if ($this->messages): ?>
 <div class="table-responsive"><table class="table sp-offer-table">
  <thead><tr><th>Ημερομηνία</th><th>Προς</th><th>Θέμα</th><th>Κατάσταση</th></tr></thead>
  <tbody><?php foreach ($this->messages as $m): ?><tr><td class="sp-n-nowrap"><?php echo $e(Notify::datetimeGr($m->sent ?: $m->created)); ?></td><td><?php echo $e($m->to_email); ?></td><td><a href="<?php echo NotifyUi::url('section=log&id=' . (int) $m->id); ?>"><?php echo $e($m->subject); ?></a></td><td><?php echo NotifyUi::status($m->status, Notify::MESSAGE_STATUSES); ?><?php if ((string) $m->last_error !== '' && $m->status !== 'sent'): ?><br><small class="text-muted"><?php echo $e($m->last_error); ?></small><?php endif; ?></td></tr><?php endforeach; ?></tbody>
 </table></div>
 <?php else: ?><p class="sp-n-help">Δεν έχει σταλεί ακόμα υπενθύμιση<?php echo $i->status === 'open' && $i->next_send ? '· η 1η είναι προγραμματισμένη για ' . $e(Notify::dateGr($i->next_send)) : ''; ?>.</p><?php endif; ?>
 <div class="sp-n-actions"><?php echo NotifyUi::button('deleteInvoice', ['id' => $i->id], 'Διαγραφή από τη λίστα', 'Διαγραφή του ' . $i->invoice_number . ';', 'btn btn-outline-danger'); ?></div>
</div>
<?php endif; ?>

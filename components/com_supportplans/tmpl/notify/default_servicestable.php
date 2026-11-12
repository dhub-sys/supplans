<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$rows = $this->section === 'clients' ? $this->services : $this->rows;
$showClient = $this->section !== 'clients';
$back = $this->section === 'clients' ? 'section=clients&id=' . (int) $this->selected->id : ($this->section === 'overview' ? 'section=overview' : 'section=services');
// Back to the same page after "Πληρώθηκε", with its filters (only simple values).
if ($this->section === 'services') {
    foreach (['q', 'catalog_id', 'status', 'expiring', 'sp', 'sf', 'st'] as $bk) {
        $bv = (string) \Joomla\CMS\Factory::getApplication()->input->getString($bk, '');
        if ($bv !== '' && preg_match('/^[a-z0-9_-]+$/i', $bv)) $back .= '&' . $bk . '=' . $bv;
    }
}
$vatOn = 1 + Notify::vatRate() / 100;
?>
<div class="table-responsive"><table class="table sp-offer-table">
  <thead><tr><?php if ($showClient): ?><th>Πελάτης</th><?php endif; ?><th>Υπηρεσία</th><th>Λήξη</th><th class="sp-num">Τιμή (προ ΦΠΑ)</th><th>Κατάσταση</th><th>Επόμενη υπενθύμιση</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $s): ?>
    <tr>
      <?php if ($showClient): ?><td><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id); ?>"><?php echo $e(Notify::clientLabel($s, 'client_')); ?></a></td><?php endif; ?>
      <td><a href="<?php echo NotifyUi::url('section=services&id=' . (int) $s->id); ?>"><strong><?php echo $e($s->service_name ?: '(διαγραμμένη υπηρεσία)'); ?></strong></a><?php if ($s->label !== ''): ?><br><small class="text-muted"><?php echo $e($s->label); ?></small><?php endif; ?></td>
      <td><?php echo NotifyUi::expiry($s->expiry_date, $s->status); ?>
        <?php if (!empty($s->last_paid_on) && (string) $s->last_new_expiry === (string) $s->expiry_date): ?><br><small class="text-muted">με «Πληρώθηκε» <?php echo $e(Notify::dateGr($s->last_paid_on)); ?>, από <?php echo $e(Notify::dateGr($s->last_old_expiry)); ?> · <a href="<?php echo NotifyUi::url('section=services&id=' . (int) $s->id); ?>#sp-n-pay">αναίρεση</a></small><?php endif; ?></td>
      <td class="sp-num"><?php echo $e(Notify::money($s->price)); ?><?php if ($s->price !== null): ?><br><small class="text-muted"><?php echo $e(Notify::durationLabel((int) $s->duration_months)); ?></small><?php endif; ?></td>
      <td><?php echo NotifyUi::status($s->status, Notify::SERVICE_STATUSES); ?></td>
      <td><?php $sch = $this->schedule[(int) $s->id] ?? null; ?>
        <?php if (!(int) $s->auto_remind || !$s->expiry_date): ?><span class="text-muted">—</span>
        <?php elseif (!$sch || !$sch['date']): ?><span class="text-muted">καμία</span>
        <?php else: ?><?php echo $e(($sch['date'] === Notify::today() ? 'σήμερα' : Notify::dateGr($sch['date'])) . (Notify::sendTimeLabel() !== '' ? ' ' . Notify::sendTimeLabel() : '')); ?><?php if ($sch['size'] > 1): ?><br><small class="text-muted">μαζί με <?php echo $sch['size'] - 1; ?> ακόμα</small><?php endif; ?><?php endif; ?>
        <br><small class="text-muted" title="Ημέρες υπενθύμισης"><?php echo (int) $s->auto_remind && $s->expiry_date ? $e(implode(', ', Notify::reminderDays($s))) : ''; ?></small></td>
      <td class="sp-num sp-row-actions">
        <?php if ($s->expiry_date): ?>
          <?php $pay = ['id' => (int) $s->id, 'name' => Notify::serviceFull($s), 'client' => $showClient ? Notify::clientLabel($s, 'client_') : '', 'expiry' => (string) $s->expiry_date,
              'expiryGr' => Notify::dateGr($s->expiry_date), 'next' => Notify::nextExpiry($s), 'min' => Notify::addDays($s->expiry_date, 1), 'duration' => Notify::durationLabel((int) $s->duration_months),
              'vat' => $s->price !== null ? number_format(round((float) $s->price * $vatOn, 2), 2, ',', '') . ' με ΦΠΑ' : '']; ?>
          <button type="button" class="btn btn-sm btn-outline-primary" data-pay="<?php echo $e(json_encode($pay, JSON_UNESCAPED_UNICODE)); ?>" title="Καταχώρηση πληρωμής: η λήξη μετακινείται και οι υπενθυμίσεις σταματούν">Πληρώθηκε…</button>
          <?php if ($s->status === 'active'): ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id . '&remind=' . (int) $s->id); ?>#sp-n-remind">Υπενθύμιση…</a><?php endif; ?>
        <?php endif; ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=reports&mode=new&service=' . (int) $s->id); ?>">Αναφορά</a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν υπηρεσίες.</td></tr><?php endif; ?>
  </tbody>
</table></div>
<?php if (empty($GLOBALS['spPayDialog'])): $GLOBALS['spPayDialog'] = true; ?>
<dialog class="sp-n-paydlg" id="sp-n-paydlg" aria-label="Πληρώθηκε">
 <form method="post" action="<?php echo $e(\Support\Component\Supportplans\Site\Helper\OfferHelper::url('task=notify.renew', false)); ?>">
  <div class="sp-n-paydlg-head"><div><strong>Πληρώθηκε / Ανανέωση</strong><span data-pay-name></span></div><button type="button" class="sp-n-paydlg-x" aria-label="Κλείσιμο" onclick="this.closest('dialog').close()">×</button></div>
  <div class="sp-n-paydlg-body">
   <label>Τρέχουσα λήξη<input data-pay-expiry disabled></label>
   <label>Ημερομηνία πληρωμής<input type="date" name="paid_on" value="<?php echo $e(Notify::today()); ?>" required></label>
   <label>Νέα λήξη<input type="date" name="new_expiry" required></label>
   <label>Ποσό (€, προαιρετικά)<input name="amount" inputmode="decimal"></label>
   <label class="sp-n-paydlg-wide">Σημείωση<input name="note" placeholder="π.χ. τιμολόγιο 123, κατάθεση Eurobank"></label>
   <p class="sp-n-help sp-n-paydlg-wide" data-pay-help></p>
  </div>
  <div class="sp-n-paydlg-foot">
   <button type="button" class="btn btn-outline-secondary" onclick="this.closest('dialog').close()">Άκυρο</button>
   <button class="btn btn-primary" data-pay-submit>Πληρώθηκε</button>
  </div>
  <input type="hidden" name="id" value=""><input type="hidden" name="back" value="<?php echo $e($back); ?>">
  <?php echo \Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
 </form>
</dialog>
<script>
(function () {
  var dlg = document.getElementById('sp-n-paydlg');
  var f = dlg.querySelector('form');
  var gr = function (ymd) { return ymd ? ymd.split('-').reverse().join('/') : ''; };
  var label = function () { dlg.querySelector('[data-pay-submit]').textContent = 'Πληρώθηκε – ανανέωση έως ' + gr(f.new_expiry.value); };
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-pay]');
    if (!b) return;
    var d = JSON.parse(b.getAttribute('data-pay'));
    f.id.value = d.id;
    dlg.querySelector('[data-pay-name]').textContent = d.name + (d.client ? ' · ' + d.client : '');
    dlg.querySelector('[data-pay-expiry]').value = d.expiryGr;
    f.new_expiry.value = d.next; f.new_expiry.min = d.min;
    f.amount.value = ''; f.amount.placeholder = d.vat; f.note.value = '';
    dlg.querySelector('[data-pay-help]').textContent = 'Η νέα λήξη προτείνεται +' + d.duration + ' από την τρέχουσα λήξη. Οι υπενθυμίσεις για την τρέχουσα λήξη σταματούν αμέσως· αν γίνει κατά λάθος, υπάρχει «Αναίρεση» στο ιστορικό πληρωμών της υπηρεσίας.';
    label();
    dlg.showModal();
    f.paid_on.focus();
  });
  f.new_expiry.addEventListener('input', label);
  f.addEventListener('submit', function () { dlg.querySelector('[data-pay-submit]').disabled = true; });
})();
</script>
<?php endif; ?>

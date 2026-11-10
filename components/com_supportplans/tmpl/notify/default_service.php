<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$s = $this->selected;
$in = Factory::getApplication()->input;
$clientId = $s ? (int) $s->client_id : $in->getInt('client_id', 0);
$catalogId = $s ? (int) $s->catalog_id : $in->getInt('catalog_id', 0);
$clients = [];
foreach ($this->clients as $c) $clients[$c->id] = Notify::clientLabel($c) . ' — ' . $c->email;
?>
<?php if (!$this->catalog || !$this->clients): ?>
<div class="sp-card"><h2>Λείπουν στοιχεία</h2><p>Για να αναθέσετε υπηρεσία χρειάζεστε τουλάχιστον έναν <a href="<?php echo NotifyUi::url('section=clients&mode=new'); ?>">πελάτη</a> και μία υπηρεσία στον <a href="<?php echo NotifyUi::url('section=catalog&mode=new'); ?>">κατάλογο</a>.</p></div>
<?php return; endif; ?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveService')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-after').value='client';spSubmit('notify.saveService')">Αποθήκευση &amp; μετάβαση στον πελάτη</button>
 <a class="btn btn-outline-secondary" href="<?php echo $clientId ? NotifyUi::url('section=clients&id=' . $clientId) : NotifyUi::url('section=services'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveService', false)); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $s ? $e(($s->service_name ?: 'Υπηρεσία') . ($s->label !== '' ? ' – ' . $s->label : '')) : 'Ανάθεση υπηρεσίας σε πελάτη'; ?></h2>
  <div class="sp-grid">
   <label>Πελάτης *<?php echo NotifyUi::select('client_id', $clients, $clientId ?: '', '— Επιλογή —', 'required'); ?></label>
   <label>Υπηρεσία *<select name="catalog_id" id="sp-n-catalog" required><option value="">— Επιλογή —</option>
    <?php foreach ($this->catalog as $k): ?><?php if (!(int) $k->published && (int) $k->id !== $catalogId) continue; ?>
    <option value="<?php echo (int) $k->id; ?>" data-price="<?php echo $k->price !== null ? $e(number_format((float) $k->price, 2, ',', '')) : ''; ?>" data-duration="<?php echo (int) $k->duration_months; ?>" data-remind="<?php echo (int) $k->auto_remind; ?>" data-days="<?php echo $e($k->reminder_days !== '' ? $k->reminder_days : Notify::setting('notify_reminder_days')); ?>"<?php echo (int) $k->id === $catalogId ? ' selected' : ''; ?>><?php echo $e($k->name); ?></option>
    <?php endforeach; ?></select></label>
   <label>Domain / περιγραφή<input name="label" value="<?php echo $e($s->label ?? ''); ?>" placeholder="π.χ. example.gr"></label>
   <label>Λήξη (τρέχουσα) *<input type="date" name="expiry_date" id="sp-n-expiry" value="<?php echo $e($s->expiry_date ?? ''); ?>"></label>
   <label>Τιμή ανανέωσης (€ προ ΦΠΑ)<input name="price" id="sp-n-price" inputmode="decimal" value="<?php echo $s && $s->price !== null ? $e(number_format((float) $s->price, 2, ',', '')) : ''; ?>"></label>
   <label>Ανανέωση για (μήνες)<input type="number" min="1" max="120" name="duration_months" id="sp-n-duration" value="<?php echo (int) ($s->duration_months ?? 12); ?>"></label>
   <label>Έναρξη (προαιρετικά)<input type="date" name="start_date" id="sp-n-start" value="<?php echo $e($s->start_date ?? ''); ?>"></label>
   <label>Κατάσταση<?php echo NotifyUi::select('status', Notify::SERVICE_STATUSES, $s->status ?? 'active'); ?></label>
   <label>Δικές του ημέρες υπενθύμισης<input name="reminder_days" id="sp-n-days" value="<?php echo $e($s->reminder_days ?? ''); ?>" placeholder="όπως η υπηρεσία"></label>
   <div class="sp-wide sp-check-row"><label class="sp-check"><input type="checkbox" name="auto_remind" id="sp-n-remind" value="1" <?php echo !$s || (int) $s->auto_remind ? 'checked' : ''; ?>> Αυτόματες υπενθυμίσεις ανανέωσης στον πελάτη</label></div>
   <label class="sp-wide">Σημειώσεις<textarea name="notes" rows="3"><?php echo $e($s->notes ?? ''); ?></textarea></label>
  </div>
  <p class="sp-n-help"><b>Λήξη</b> = η ημερομηνία που λήγει <b>τώρα</b> η υπηρεσία· από αυτήν προγραμματίζονται οι υπενθυμίσεις. «Ανανέωση για» είναι η περίοδος που θα προστεθεί όταν πατήσετε «Πληρώθηκε». Αν αφήσετε κενή τη λήξη και συμπληρώσετε έναρξη, η λήξη υπολογίζεται ως έναρξη + περίοδος. <span id="sp-n-days-hint"></span> <b id="sp-n-calc"></b></p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($s->id ?? 0); ?>"><input type="hidden" name="after" id="sp-after" value=""><input type="hidden" name="task" value="notify.saveService">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
(function () {
  var cat = document.getElementById('sp-n-catalog'), isNew = <?php echo $s ? 'false' : 'true'; ?>;
  var price = document.getElementById('sp-n-price'), dur = document.getElementById('sp-n-duration'), remind = document.getElementById('sp-n-remind');
  var start = document.getElementById('sp-n-start'), expiry = document.getElementById('sp-n-expiry'), hint = document.getElementById('sp-n-days-hint');
  function opt() { return cat.options[cat.selectedIndex] || null; }
  function addMonths(ymd, n) {
    var p = ymd.split('-'); if (p.length !== 3) return '';
    var y = +p[0], m = +p[1] - 1 + n, d = +p[2];
    var last = new Date(y, m + 1, 0).getDate(), t = new Date(y, m, Math.min(d, last));
    return t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
  }
  // The expiry is never filled in automatically (it was easy to save a computed date by mistake);
  // only a hint shows what start + period would give.
  function suggest() { var h = document.getElementById('sp-n-calc'); if (h) h.textContent = isNew && start.value && dur.value && !expiry.value ? 'Κενή λήξη → θα αποθηκευτεί ' + addMonths(start.value, +dur.value).split('-').reverse().join('/') + ' (έναρξη + περίοδος).' : ''; }
  function showDays() { var o = opt(); hint.textContent = o && o.value ? 'Ημέρες υπενθύμισης της υπηρεσίας: ' + o.dataset.days + '.' : ''; }
  cat.addEventListener('change', function () {
    var o = opt(); showDays();
    if (!isNew || !o || !o.value) return;
    price.value = o.dataset.price; dur.value = o.dataset.duration; remind.checked = o.dataset.remind === '1'; suggest();
  });
  expiry.addEventListener('input', suggest);
  start.addEventListener('change', suggest); dur.addEventListener('change', suggest);
  showDays();
  if (isNew && opt() && opt().value) cat.dispatchEvent(new Event('change'));
})();
</script>
<?php if ($s): ?>
<?php $next = Notify::nextExpiry($s); $vatPrice = $s->price !== null ? Notify::withVat((float) $s->price) : null; ?>
<div class="sp-card" id="sp-n-pay">
  <h2>Πληρώθηκε / Ανανέωση</h2>
  <p class="sp-n-help">Όταν ο πελάτης πληρώσει, καταχωρήστε το εδώ: η λήξη μετακινείται και <b>οι υπενθυμίσεις για την τρέχουσα λήξη σταματούν αμέσως</b> (και όσες περιμένουν στην ουρά ακυρώνονται). Οι επόμενες υπενθυμίσεις θα έρθουν όταν πλησιάσει η νέα λήξη.</p>
  <form method="post" action="<?php echo $e(OfferHelper::url('task=notify.renew', false)); ?>" onsubmit="return confirm('Καταχώρηση πληρωμής και νέα λήξη ' + this.new_expiry.value.split('-').reverse().join('/') + ';');">
   <div class="sp-grid">
    <label>Τρέχουσα λήξη<input value="<?php echo $e(Notify::dateGr($s->expiry_date)); ?>" disabled></label>
    <label>Ημερομηνία πληρωμής<input type="date" name="paid_on" value="<?php echo $e(Notify::today()); ?>" required></label>
    <label>Νέα λήξη<input type="date" name="new_expiry" value="<?php echo $e($next); ?>" min="<?php echo $e($s->expiry_date ? Notify::addDays($s->expiry_date, 1) : ''); ?>" required></label>
    <label>Ποσό (€, προαιρετικά)<input name="amount" inputmode="decimal" placeholder="<?php echo $vatPrice !== null ? $e(number_format($vatPrice, 2, ',', '') . ' με ΦΠΑ') : ''; ?>"></label>
    <label class="sp-wide">Σημείωση<input name="note" placeholder="π.χ. τιμολόγιο 123, κατάθεση Eurobank"></label>
   </div>
   <p class="sp-n-help">Η νέα λήξη προτείνεται +<?php echo $e(Notify::durationLabel((int) $s->duration_months)); ?> από την τρέχουσα λήξη (όχι από σήμερα), όπως ανανεώνονται τα domains και το hosting· αλλάξτε την αν χρειάζεται.</p>
   <input type="hidden" name="id" value="<?php echo (int) $s->id; ?>"><input type="hidden" name="back" value="section=services&id=<?php echo (int) $s->id; ?>">
   <?php echo HTMLHelper::_('form.token'); ?>
   <div class="sp-n-actions"><button class="btn btn-primary">Πληρώθηκε – ανανέωση έως <?php echo $e(Notify::dateGr($next)); ?></button></div>
  </form>
  <?php if ($this->renewals): ?>
  <h3>Ιστορικό πληρωμών / ανανεώσεων</h3>
  <div class="table-responsive"><table class="table sp-offer-table">
   <thead><tr><th>Πληρωμή</th><th>Λήξη πριν</th><th>Νέα λήξη</th><th class="sp-num">Ποσό</th><th>Σημείωση</th><th></th></tr></thead>
   <tbody><?php foreach ($this->renewals as $r): ?><tr><td><?php echo $e(Notify::dateGr($r->paid_on)); ?></td><td><?php echo $e(Notify::dateGr($r->old_expiry)); ?></td><td><?php echo $e(Notify::dateGr($r->new_expiry)); ?></td><td class="sp-num"><?php echo $e(Notify::money($r->amount) ?: '—'); ?></td><td><?php echo $e($r->note); ?></td>
   <td class="sp-num sp-row-actions"><?php if (Notify::canUndo($r, $s->expiry_date)): ?><?php echo NotifyUi::button('undoRenewal', ['id' => $r->id, 'back' => 'section=services&id=' . (int) $s->id], 'Αναίρεση', 'Αναίρεση της πληρωμής; Η λήξη θα επανέλθει στις ' . Notify::dateGr($r->old_expiry) . ' και οι υπενθυμίσεις θα συνεχίσουν.', 'btn btn-sm btn-outline-danger'); ?><?php endif; ?></td></tr><?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
</div>
<div class="sp-card">
  <h2>Ενέργειες</h2>
  <div class="sp-n-actions">
    <?php if ($s->expiry_date && $s->status === 'active'): ?><a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id . '&remind=' . (int) $s->id); ?>#sp-n-remind">Αποστολή υπενθύμισης…</a><?php endif; ?>
    <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=reports&mode=new&service=' . (int) $s->id); ?>">Νέα αναφορά maintenance</a>
    <?php echo NotifyUi::button('deleteService', ['id' => $s->id], 'Διαγραφή', 'Διαγραφή της υπηρεσίας του πελάτη, των αναφορών και του ιστορικού πληρωμών της;', 'btn btn-outline-danger'); ?>
  </div>
  <p class="sp-n-help">Για να μη στέλνονται ποτέ υπενθυμίσεις για αυτή την υπηρεσία, ξετσεκάρετε «Αυτόματες υπενθυμίσεις» παραπάνω· αν ο πελάτης δεν θα ανανεώσει, αλλάξτε την κατάσταση σε «Ακυρωμένη».</p>
</div>
<?php endif; ?>

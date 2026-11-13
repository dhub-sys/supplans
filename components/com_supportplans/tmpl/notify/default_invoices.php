<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$f = $this->filters;
$back = 'section=invoices' . ($f['status'] !== 'open' ? '&status=' . ($f['status'] ?: 'all') : '');
$open = array_filter($this->rows, static fn($r) => $r->status === 'open');
$openTotal = array_sum(array_map(static fn($r) => (float) $r->amount, $open));
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Εισπράξεις</span><h1>Υπενθυμίσεις πληρωμών</h1><p>Παραστατικά που έχουν εκδοθεί και δεν έχουν πληρωθεί. Η 1η υπενθύμιση φεύγει αμέσως (ή την ημέρα που ορίζετε) και μετά ξανά ανά τις ημέρες που ορίζετε<?php echo Notify::sendTimeLabel() !== '' ? ', καθημερινά από τις ' . $e(Notify::sendTimeLabel()) : ''; ?>, μέχρι να πατήσετε «Πληρώθηκε».</p></div>
  <div class="sp-head-actions">
    <a class="sp-primary-action is-secondary" href="<?php echo NotifyUi::url('section=settings'); ?>#sp-invoice-template" title="Θέμα, κείμενο και προεπιλεγμένο διάστημα των υπενθυμίσεων πληρωμών">✎ Κείμενο email</a>
    <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=invoices&mode=new'); ?>">＋ Νέο παραστατικό</a>
  </div>
</section>
<?php if ($f['status'] === 'open'): ?>
<section class="sp-kpis sp-n-kpis">
  <article><span>Ανεξόφλητα</span><strong><?php echo count($open); ?></strong><small>παραστατικά</small></article>
  <article><span>Συνολικό ποσό</span><strong><?php echo $e(Notify::money($openTotal)); ?></strong><small>προς είσπραξη</small></article>
  <article><span>Υπενθύμιση σήμερα</span><strong><?php echo count(array_filter($open, static fn($r) => $r->next_send && $r->next_send <= Notify::today())); ?></strong><small><?php echo Notify::sendTimeLabel() !== '' ? 'από τις ' . $e(Notify::sendTimeLabel()) : 'στην επόμενη εκτέλεση'; ?></small></article>
</section>
<?php endif; ?>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(NotifyUi::url('', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="invoices">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
    <input type="search" name="q" value="<?php echo $e($f['search']); ?>" placeholder="Αριθμός, email, πελάτης">
    <?php echo NotifyUi::select('status', ['open' => 'Ανεξόφλητα', 'paid' => 'Εξοφλημένα', 'all' => 'Όλα'], $f['status'] ?: 'all'); ?>
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
  </form>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Παραστατικό</th><th>Πελάτης</th><th class="sp-num">Ποσό</th><th>Κατάσταση</th><th>Υπενθυμίσεις</th><th>Επόμενη</th><th class="sp-num"></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): ?>
      <tr class="<?php echo $r->status === 'paid' ? 'is-off' : ''; ?>">
        <td><a href="<?php echo NotifyUi::url('section=invoices&id=' . (int) $r->id); ?>"><strong><?php echo $e($r->invoice_number); ?></strong></a><br><small class="text-muted"><?php echo $e(Notify::dateGr($r->invoice_date)); ?><?php if ($r->invoice_date): $ago = max(0, -(int) Notify::daysUntil($r->invoice_date)); ?> · <?php echo $ago === 0 ? 'σήμερα' : ($ago === 1 ? 'χθες' : 'πριν ' . $ago . ' ημ.'); ?><?php endif; ?></small></td>
        <td><?php echo $e($r->name !== '' ? $r->name : $r->email); ?><?php if ($r->name !== ''): ?><br><small class="text-muted"><?php echo $e($r->email); ?></small><?php endif; ?></td>
        <td class="sp-num"><strong><?php echo $e(Notify::money((float) $r->amount)); ?></strong></td>
        <td><?php echo $r->status === 'paid' ? '<span class="sp-offer-status is-accepted">Εξοφλήθηκε ' . $e(Notify::dateGr($r->paid_on)) . '</span>' : '<span class="sp-offer-status sp-n-warn">Ανεξόφλητο</span>'; ?></td>
        <td class="sp-n-nowrap"><strong><?php echo (int) $r->sends; ?></strong><?php if ($r->last_sent): ?><br><small class="text-muted">τελ. <?php echo $e(substr(Notify::datetimeGr($r->last_sent), 0, 10)); ?></small><?php endif; ?></td>
        <td><?php if ($r->status === 'open' && $r->next_send): ?><?php echo $e($r->next_send <= Notify::today() ? 'σήμερα' : Notify::dateGr($r->next_send)); ?><br><small class="text-muted">κάθε <?php echo (int) $r->interval_days; ?> ημ.</small><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
        <td class="sp-num sp-row-actions sp-n-nowrap">
          <?php if ($r->status === 'open'): ?>
            <?php echo NotifyUi::button('invoicePaid', ['id' => $r->id, 'back' => $back], 'Πληρώθηκε', 'Να σημειωθεί το ' . $r->invoice_number . ' ως εξοφλημένο; Οι υπενθυμίσεις σταματούν.', 'btn btn-sm btn-primary'); ?>
            <?php echo NotifyUi::button('sendInvoice', ['id' => $r->id, 'back' => $back], 'Αποστολή', 'Αποστολή υπενθύμισης στο ' . $r->email . ' τώρα;', 'btn btn-sm btn-outline-secondary'); ?>
          <?php else: ?>
            <?php echo NotifyUi::button('reopenInvoice', ['id' => $r->id, 'back' => $back], 'Ανεξόφλητο', 'Να επανέλθει το ' . $r->invoice_number . ' σε ανεξόφλητο; Οι υπενθυμίσεις ξαναρχίζουν.', 'btn btn-sm btn-outline-secondary'); ?>
          <?php endif; ?>
          <?php echo NotifyUi::button('deleteInvoice', ['id' => $r->id], 'Διαγραφή', 'Διαγραφή του ' . $r->invoice_number . ' από τη λίστα;', 'btn btn-sm btn-outline-danger'); ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="7" class="text-muted"><?php echo $f['status'] === 'open' ? 'Δεν υπάρχουν ανεξόφλητα παραστατικά.' : 'Δεν βρέθηκαν παραστατικά.'; ?></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$rows = $this->section === 'clients' ? $this->services : $this->rows;
$showClient = $this->section !== 'clients';
$back = $this->section === 'clients' ? 'section=clients&id=' . (int) $this->selected->id : ($this->section === 'overview' ? 'section=overview' : 'section=services');
?>
<div class="table-responsive"><table class="table sp-offer-table">
  <thead><tr><?php if ($showClient): ?><th>Πελάτης</th><?php endif; ?><th>Υπηρεσία</th><th>Λήξη</th><th class="sp-num">Τιμή (προ ΦΠΑ)</th><th>Κατάσταση</th><th>Επόμενη υπενθύμιση</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $s): ?>
    <tr>
      <?php if ($showClient): ?><td><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id); ?>"><?php echo $e(Notify::clientLabel($s, 'client_')); ?></a></td><?php endif; ?>
      <td><a href="<?php echo NotifyUi::url('section=services&id=' . (int) $s->id); ?>"><strong><?php echo $e($s->service_name ?: '(διαγραμμένη υπηρεσία)'); ?></strong></a><?php if ($s->label !== ''): ?><br><small class="text-muted"><?php echo $e($s->label); ?></small><?php endif; ?></td>
      <td><?php echo NotifyUi::expiry($s->expiry_date, $s->status); ?></td>
      <td class="sp-num"><?php echo $e(Notify::money($s->price)); ?><?php if ($s->price !== null): ?><br><small class="text-muted"><?php echo $e(Notify::durationLabel((int) $s->duration_months)); ?></small><?php endif; ?></td>
      <td><?php echo NotifyUi::status($s->status, Notify::SERVICE_STATUSES); ?></td>
      <td><?php $sch = $this->schedule[(int) $s->id] ?? null; ?>
        <?php if (!(int) $s->auto_remind || !$s->expiry_date): ?><span class="text-muted">—</span>
        <?php elseif (!$sch || !$sch['date']): ?><span class="text-muted">καμία</span>
        <?php else: ?><?php echo $e($sch['date'] === Notify::today() ? 'σήμερα' : Notify::dateGr($sch['date'])); ?><?php if ($sch['size'] > 1): ?><br><small class="text-muted">μαζί με <?php echo $sch['size'] - 1; ?> ακόμα</small><?php endif; ?><?php endif; ?>
        <br><small class="text-muted" title="Ημέρες υπενθύμισης"><?php echo (int) $s->auto_remind && $s->expiry_date ? $e(implode(', ', Notify::reminderDays($s))) : ''; ?></small></td>
      <td class="sp-num sp-row-actions">
        <?php if ($s->expiry_date): ?>
          <a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=services&id=' . (int) $s->id); ?>#sp-n-pay" title="Καταχώρηση πληρωμής: η λήξη μετακινείται και οι υπενθυμίσεις σταματούν">Πληρώθηκε…</a>
          <?php if ($s->status === 'active'): ?><?php echo NotifyUi::button('remindNow', ['id' => $s->id, 'back' => $back], 'Υπενθύμιση τώρα', 'Αποστολή υπενθύμισης ανανέωσης στο ' . $s->client_email . ' τώρα;', 'btn btn-sm btn-outline-secondary'); ?><?php endif; ?>
        <?php endif; ?>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=reports&mode=new&service=' . (int) $s->id); ?>">Αναφορά</a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν υπηρεσίες.</td></tr><?php endif; ?>
  </tbody>
</table></div>

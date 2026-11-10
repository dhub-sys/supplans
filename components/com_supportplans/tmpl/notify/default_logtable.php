<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$rows = $this->section === 'log' ? $this->rows : $this->messages;
?>
<div class="table-responsive"><table class="table sp-offer-table">
  <thead><tr><th>Ημερομηνία</th><th>Προς</th><th>Τύπος</th><th>Θέμα</th><th>Κατάσταση</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $m): ?>
    <?php $when = $m->sent ?: ($m->status === 'queued' ? $m->scheduled : $m->created); ?>
    <tr>
      <td><?php echo $e(Notify::datetimeGr($when)); ?></td>
      <td><?php echo $e($m->to_email); ?><?php if (!empty($m->client_name)): ?><br><small class="text-muted"><?php echo $e($m->client_name); ?></small><?php endif; ?></td>
      <td><?php echo $e(Notify::KINDS[$m->kind] ?? $m->kind); ?></td>
      <td><?php echo $e($m->subject); ?></td>
      <td><?php echo NotifyUi::status($m->status, Notify::MESSAGE_STATUSES); ?><?php if ($m->last_error): ?><br><small class="text-muted"><?php echo $e($m->last_error); ?></small><?php endif; ?></td>
      <td class="sp-num sp-row-actions">
        <a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=log&id=' . (int) $m->id); ?>">Προβολή</a>
        <?php if (in_array($m->status, ['failed', 'cancelled'], true)): ?><?php echo NotifyUi::button('retry', ['id' => $m->id], 'Επανάληψη', '', 'btn btn-sm btn-outline-secondary'); ?><?php endif; ?>
        <?php if ($m->status === 'queued'): ?><?php echo NotifyUi::button('cancel', ['id' => $m->id], 'Ακύρωση', 'Ακύρωση του μηνύματος;', 'btn btn-sm btn-outline-danger'); ?><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="text-muted">Δεν υπάρχουν μηνύματα.</td></tr><?php endif; ?>
  </tbody>
</table></div>

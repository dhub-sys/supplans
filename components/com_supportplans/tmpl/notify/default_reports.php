<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Πελάτες &amp; Ειδοποιήσεις</span><h1>Αναφορές maintenance</h1><p>Αναφορά προς τον πελάτη με σύνοψη εργασιών και πίνακα ενημερώσεων (Joomla, extensions, templates κ.λπ.). Προεπισκόπηση, δοκιμή στο email σας και αποστολή.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=reports&mode=new'); ?>">＋ Νέα αναφορά</a>
</section>
<div class="sp-card">
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Πελάτης</th><th>Υπηρεσία</th><th>Περίοδος</th><th>Κατάσταση</th><th>Αποστολή</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): ?>
      <tr>
        <td><?php echo $e(Notify::clientLabel($r, 'client_')); ?></td>
        <td><?php echo $e(($r->service_name ?: 'Υπηρεσία') . ($r->service_label !== '' ? ' – ' . $r->service_label : '')); ?></td>
        <td><strong><?php echo $e($r->period ?: '—'); ?></strong></td>
        <td><?php echo NotifyUi::status($r->status, ['draft' => 'Πρόχειρη', 'sent' => 'Εστάλη']); ?></td>
        <td><?php echo $e(Notify::datetimeGr($r->sent)); ?></td>
        <td class="sp-num sp-row-actions"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=reports&id=' . (int) $r->id); ?>"><?php echo $r->status === 'sent' ? 'Προβολή' : 'Επεξεργασία'; ?></a>
        <?php echo NotifyUi::button('deleteReport', ['id' => $r->id], 'Διαγραφή', 'Διαγραφή της αναφοράς;', 'btn btn-sm btn-outline-danger'); ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="6" class="text-muted">Δεν υπάρχουν αναφορές.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

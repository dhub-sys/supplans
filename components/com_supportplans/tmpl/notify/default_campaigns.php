<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Ανανεώσεις</span><h1>Αποστολές</h1><p>Ελεύθερα μηνύματα σε όλους τους πελάτες, σε όσους έχουν συγκεκριμένη υπηρεσία ή σε επιλεγμένους (ανακοινώσεις, εργασίες στον server, νέες υπηρεσίες). Οι πελάτες που έχουν διαγραφεί από τις ενημερώσεις εξαιρούνται αυτόματα.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=campaigns&mode=new'); ?>">＋ Νέα αποστολή</a>
</section>
<div class="sp-card">
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Θέμα</th><th>Κατάσταση</th><th class="sp-center">Παραλήπτες</th><th class="sp-center">Εστάλησαν</th><th class="sp-center">Απέτυχαν</th><th>Δημιουργία</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $k): ?>
      <tr>
        <td><a href="<?php echo NotifyUi::url('section=campaigns&id=' . (int) $k->id); ?>"><strong><?php echo $e($k->subject ?: '(χωρίς θέμα)'); ?></strong></a></td>
        <td><?php echo NotifyUi::status($k->status, ['draft' => 'Πρόχειρη', 'queued' => 'Σε αποστολή']); ?></td>
        <td class="sp-center"><?php echo (int) $k->recipients; ?></td><td class="sp-center"><?php echo (int) $k->sent_count; ?></td><td class="sp-center"><?php echo (int) $k->failed_count; ?></td>
        <td><?php echo $e(Notify::datetimeGr($k->created)); ?></td>
        <td class="sp-num sp-row-actions"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=campaigns&id=' . (int) $k->id); ?>"><?php echo $k->status === 'draft' ? 'Επεξεργασία' : 'Προβολή'; ?></a>
        <?php if ($k->status === 'draft'): ?><?php echo NotifyUi::button('deleteCampaign', ['id' => $k->id], 'Διαγραφή', 'Διαγραφή του πρόχειρου;', 'btn btn-sm btn-outline-danger'); ?><?php else: ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=log&campaign_id=' . (int) $k->id); ?>">Μηνύματα</a><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν αποστολές.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

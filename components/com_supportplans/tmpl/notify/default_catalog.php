<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$global = (string) Notify::setting('notify_reminder_days');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Πελάτες &amp; Ειδοποιήσεις</span><h1>Κατάλογος υπηρεσιών</h1><p>Οι υπηρεσίες που προσφέρετε (π.χ. «Web Hosting Green», «Ανανέωση Domain .gr»), με προεπιλεγμένη τιμή, διάρκεια και ημέρες υπενθύμισης. Όταν αναθέτετε μια υπηρεσία σε πελάτη, η τιμή και η διάρκεια αντιγράφονται και μπορούν να αλλάξουν για τον συγκεκριμένο πελάτη.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=catalog&mode=new'); ?>">＋ Νέα υπηρεσία</a>
</section>
<div class="sp-card">
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Σειρά</th><th>Υπηρεσία</th><th class="sp-num">Τιμή</th><th>Διάρκεια</th><th>Υπενθυμίσεις (ημέρες πριν)</th><th class="sp-center">Πελάτες</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->catalog as $k): ?>
      <tr class="<?php echo (int) $k->published ? '' : 'is-off'; ?>">
        <td><?php echo (int) $k->ordering; ?></td>
        <td><a href="<?php echo NotifyUi::url('section=catalog&id=' . (int) $k->id); ?>"><strong><?php echo $e($k->name); ?></strong></a><?php if (!(int) $k->published): ?> <small class="text-muted">(ανενεργή)</small><?php endif; ?><?php if (trim((string) $k->description) !== ''): ?><br><small class="text-muted"><?php echo $e(mb_strimwidth((string) $k->description, 0, 90, '…')); ?></small><?php endif; ?></td>
        <td class="sp-num"><?php echo $e(Notify::money($k->price) ?: '—'); ?></td>
        <td><?php echo $e(Notify::durationLabel((int) $k->duration_months)); ?></td>
        <td><?php echo (int) $k->auto_remind ? $e($k->reminder_days !== '' ? $k->reminder_days : $global . ' (προεπιλογή)') : 'Χωρίς αυτόματες'; ?></td>
        <td class="sp-center"><?php echo (int) $k->active_count; ?></td>
        <td class="sp-num sp-row-actions">
          <a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=catalog&id=' . (int) $k->id); ?>">Επεξεργασία</a>
          <a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=services&mode=new&catalog_id=' . (int) $k->id); ?>">Ανάθεση</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->catalog): ?><tr><td colspan="7" class="text-muted">Ο κατάλογος είναι κενός. Προσθέστε την πρώτη σας υπηρεσία.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

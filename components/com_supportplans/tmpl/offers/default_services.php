<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Προσφορές</span><h1>Βιβλιοθήκη υπηρεσιών</h1><p>Κάθε υπηρεσία έχει κείμενο περιγραφής για το έγγραφο και τιμή για τον πίνακα τιμών. Χρησιμοποιούνται στα templates και στις προσφορές.</p></div>
  <a class="sp-primary-action" href="<?php echo OfferHelper::url('view=offers&section=services&mode=new'); ?>">＋ Νέα υπηρεσία</a>
</section>
<div class="sp-card">
  <h2>Υπηρεσίες</h2>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Σειρά</th><th>Υπηρεσία</th><th>Ετικέτα πίνακα τιμών</th><th class="sp-center">Περιγραφή</th><th class="sp-center">Πίνακας</th><th class="sp-num">Τιμή</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->services as $s): ?>
      <tr class="<?php echo (int) $s->published ? '' : 'is-off'; ?>">
        <td><?php echo (int) $s->ordering; ?></td>
        <td><strong><?php echo $e($s->name); ?></strong><?php if (!(int) $s->published): ?> <small class="text-muted">(ανενεργή)</small><?php endif; ?><br><small class="text-muted"><?php echo $e(trim((string) $s->section_title) ?: $s->name); ?></small></td>
        <td><?php echo $e(trim((string) $s->table_label) ?: $s->name); ?></td>
        <td class="sp-center"><?php echo (int) $s->show_description ? '✓' : '—'; ?></td>
        <td class="sp-center"><?php echo (int) $s->in_table ? '✓' : '—'; ?></td>
        <td class="sp-num"><?php echo OfferHelper::money((float) $s->price); ?> €</td>
        <td class="sp-num sp-row-actions">
          <a class="btn btn-sm btn-outline-primary" href="<?php echo OfferHelper::url('view=offers&section=services&id=' . (int) $s->id); ?>">Επεξεργασία</a>
          <form method="post" action="<?php echo OfferHelper::url('task=offerservice.delete'); ?>" onsubmit="return confirm('Να διαγραφεί η υπηρεσία; Οι υπάρχουσες προσφορές δεν επηρεάζονται.');"><input type="hidden" name="id" value="<?php echo (int) $s->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Διαγραφή</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->services): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν υπηρεσίες ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

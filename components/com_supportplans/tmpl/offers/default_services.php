<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Προσφορές</span><h1>Υπηρεσίες / Περιγραφές</h1><p>Οι <b>υπηρεσίες</b> έχουν κείμενο για το έγγραφο και τιμή για τον πίνακα τιμών. Οι <b>περιγραφές</b> είναι ενότητες κειμένου χωρίς τιμή (π.χ. στάδια έργου, όροι προσφοράς). Το <b>block τιμών</b> βάζει τον πίνακα τιμών και τον τρόπο πληρωμής στη θέση που θέλετε. Χρησιμοποιούνται στα templates και στις προσφορές.</p></div>
  <div class="sp-head-actions"><a class="sp-primary-action" href="<?php echo OfferHelper::url('view=offers&section=services&mode=new'); ?>">＋ Νέα υπηρεσία</a><a class="sp-primary-action is-secondary" href="<?php echo OfferHelper::url('view=offers&section=services&mode=new&kind=description'); ?>">＋ Νέα περιγραφή</a><a class="sp-primary-action is-secondary" href="<?php echo OfferHelper::url('view=offers&section=services&mode=new&kind=pricing'); ?>">＋ Block τιμών</a></div>
</section>
<div class="sp-card">
  <h2>Υπηρεσίες / Περιγραφές</h2>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Σειρά</th><th>Τύπος</th><th>Όνομα</th><th>Ετικέτα πίνακα τιμών</th><th class="sp-center">Στο κείμενο</th><th class="sp-center">Πίνακας</th><th class="sp-num">Τιμή</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->services as $s): ?>
      <tr class="<?php echo (int) $s->published ? '' : 'is-off'; ?>">
        <td><?php echo (int) $s->ordering; ?></td>
        <td><?php echo OfferHelper::kindBadge($s->kind ?? 'service'); ?></td>
        <td><strong><?php echo $e($s->name); ?></strong><?php if (!(int) $s->published): ?> <small class="text-muted">(ανενεργή)</small><?php endif; ?><br><small class="text-muted"><?php echo $e(trim((string) $s->section_title) ?: $s->name); ?></small></td>
        <?php $isDesc = OfferHelper::kind($s->kind ?? 'service') !== 'service'; ?>
        <td><?php echo $isDesc ? '<span class="text-muted">—</span>' : $e(trim((string) $s->table_label) ?: $s->name); ?></td>
        <td class="sp-center"><?php echo (int) $s->show_description ? '✓' : '—'; ?></td>
        <td class="sp-center"><?php echo (int) $s->in_table ? '✓' : '—'; ?></td>
        <td class="sp-num"><?php echo $isDesc ? '<span class="text-muted">—</span>' : OfferHelper::money((float) $s->price) . ' €'; ?></td>
        <td class="sp-num sp-row-actions">
          <a class="btn btn-sm btn-outline-primary" href="<?php echo OfferHelper::url('view=offers&section=services&id=' . (int) $s->id); ?>">Επεξεργασία</a>
          <form method="post" action="<?php echo OfferHelper::url('task=offerservice.delete'); ?>" onsubmit="return confirm('Να διαγραφεί; Οι υπάρχουσες προσφορές δεν επηρεάζονται.');"><input type="hidden" name="id" value="<?php echo (int) $s->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Διαγραφή</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->services): ?><tr><td colspan="8" class="text-muted">Δεν υπάρχουν υπηρεσίες ή περιγραφές ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

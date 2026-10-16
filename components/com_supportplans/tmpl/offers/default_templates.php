<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Προσφορές</span><h1>Templates προσφορών</h1><p>Έτοιμα πρότυπα: κείμενα εγγράφου, όροι και ποιες υπηρεσίες περιλαμβάνονται από προεπιλογή.</p></div>
  <a class="sp-primary-action" href="<?php echo OfferHelper::url('view=offers&section=templates&mode=new'); ?>">＋ Νέο template</a>
</section>
<div class="sp-card">
  <h2>Templates</h2>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Template</th><th>Υπηρεσίες</th><th>Φ.Π.Α.</th><th>Κατάσταση</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->templates as $t): $count = count((array) json_decode((string) $t->items, true)); ?>
      <tr>
        <td><strong><?php echo $e($t->name); ?></strong><br><small class="text-muted"><?php echo $e(str_replace("\n", ' · ', (string) $t->doc_title)); ?></small></td>
        <td><?php echo $count; ?></td>
        <td><?php echo $e(rtrim(rtrim(number_format((float) $t->vat_rate, 2, ',', '.'), '0'), ',')); ?>%</td>
        <td><?php echo (int) $t->published ? 'Ενεργό' : 'Ανενεργό'; ?></td>
        <td class="sp-num sp-row-actions">
          <a class="btn btn-sm btn-outline-primary" href="<?php echo OfferHelper::url('view=offers&section=templates&id=' . (int) $t->id); ?>">Επεξεργασία</a>
          <form method="post" action="<?php echo OfferHelper::url('task=offertemplate.duplicate'); ?>"><input type="hidden" name="id" value="<?php echo (int) $t->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-secondary">Αντίγραφο</button></form>
          <form method="post" action="<?php echo OfferHelper::url('task=offertemplate.delete'); ?>" onsubmit="return confirm('Να διαγραφεί το template; Οι υπάρχουσες προσφορές δεν επηρεάζονται.');"><input type="hidden" name="id" value="<?php echo (int) $t->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Διαγραφή</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->templates): ?><tr><td colspan="5" class="text-muted">Δεν υπάρχουν templates ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <form method="post" action="<?php echo OfferHelper::url('task=offertemplate.sample'); ?>" class="sp-sample-form"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-secondary btn-sm">＋ Δημιουργία παραδείγματος (e-shop, φιλοξενία, συντήρηση)</button></form>
</div>

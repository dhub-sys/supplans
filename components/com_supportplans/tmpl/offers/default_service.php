<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$s = $this->selected;
$kind = OfferHelper::kind($s->kind ?? \Joomla\CMS\Factory::getApplication()->input->getCmd('kind', 'service'));
$nextOrdering = ($this->services ? max(array_map(static fn($x) => (int) $x->ordering, $this->services)) : 0) + 1;
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('offerservice.apply')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spSubmit('offerservice.save')">Αποθήκευση &amp; κλείσιμο</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=offers&section=services'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo OfferHelper::url('view=offers&section=services' . ($s ? '&id=' . (int) $s->id : '&mode=new')); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <h2 id="sp-svc-heading"><?php echo $s ? OfferHelper::KINDS[$kind] . ': ' . $e($s->name) : ($kind === 'description' ? 'Νέα περιγραφή' : 'Νέα υπηρεσία'); ?></h2>
  <div class="sp-grid" id="sp-svc-grid" data-kind="<?php echo $kind; ?>">
   <label>Τύπος<select name="kind" id="sp-svc-kind"><?php foreach (OfferHelper::KINDS as $k => $label): ?><option value="<?php echo $k; ?>"<?php echo $kind === $k ? ' selected' : ''; ?>><?php echo $label; ?></option><?php endforeach; ?></select></label>
   <label>Όνομα (εσωτερικό)<input name="name" required value="<?php echo $e($s->name ?? ''); ?>" placeholder="π.χ. Φιλοξενία Green"></label>
   <label>Τίτλος ενότητας στο έγγραφο<input name="section_title" value="<?php echo $e($s->section_title ?? ''); ?>" placeholder="π.χ. Φιλοξενία Ιστοσελίδας"></label>
   <label class="sp-svc-only">Περιγραφή στον πίνακα τιμών<input name="table_label" value="<?php echo $e($s->table_label ?? ''); ?>" placeholder="π.χ. Φιλοξενία – Συντήρηση (1 έτος)"></label>
   <label class="sp-svc-only">Τιμή (€ προ Φ.Π.Α.)<input type="number" step="0.01" name="price" value="<?php echo $e(number_format((float) ($s->price ?? 0), 2, '.', '')); ?>"></label>
   <label>Σειρά<input type="number" step="1" name="ordering" value="<?php echo (int) ($s->ordering ?? $nextOrdering); ?>"></label>
   <label>Κατάσταση<select name="published"><option value="1" <?php echo !$s || (int) $s->published ? 'selected' : ''; ?>>Ενεργή</option><option value="0" <?php echo $s && !(int) $s->published ? 'selected' : ''; ?>>Ανενεργή</option></select></label>
   <div class="sp-wide sp-check-row">
    <label class="sp-check"><input type="checkbox" name="show_description" value="1" <?php echo !$s || (int) $s->show_description ? 'checked' : ''; ?>> Περιγράφεται στο έγγραφο (προεπιλογή)</label>
    <label class="sp-check sp-svc-only"><input type="checkbox" name="in_table" value="1" <?php echo !$s || (int) $s->in_table ? 'checked' : ''; ?>> Στον πίνακα τιμών (προεπιλογή)</label>
   </div>
   <label>Θέση στο Pdf (προεπιλογή)<select name="page_break" id="sp-svc-break"><option value="0"<?php echo empty($s->page_break) ? ' selected' : ''; ?>>Συνέχεια στην ίδια σελίδα</option><option value="1"<?php echo !empty($s->page_break) ? ' selected' : ''; ?>>Ξεκινά σε νέα σελίδα</option></select></label>
   <label id="sp-svc-space">Απόσταση από την προηγούμενη ενότητα (mm)<input type="number" min="0" max="80" step="1" name="space_before" value="<?php echo (int) ($s->space_before ?? 5); ?>"></label>
   <script>(function(){var k=document.getElementById('sp-svc-kind'),g=document.getElementById('sp-svc-grid');k.addEventListener('change',function(){g.dataset.kind=k.value;});})();</script>
   <script>(function(){var b=document.getElementById('sp-svc-break'),sp=document.getElementById('sp-svc-space');function u(){sp.hidden=b.value==='1';}b.addEventListener('change',u);u();})();</script>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο περιγραφής</span><textarea name="body" rows="18" data-sp-editor><?php echo $e($s->body ?? ''); ?></textarea></div>
   <div class="sp-wide sp-field"><span class="sp-field-label">Λίστες &amp; επιπλέον κείμενα (στο Pdf μετά το κείμενο, με αυτή τη σειρά)</span><?php echo OfferHelper::listsEditor('lists', OfferHelper::lists($s->lists ?? '')); ?></div>
  </div>
  <p class="sp-foot">Αν ο τίτλος ενότητας ή η περιγραφή στον πίνακα μείνουν κενά, χρησιμοποιείται το όνομα. Οι αλλαγές εδώ ισχύουν για νέες προσφορές· οι υπάρχουσες κρατούν το δικό τους αντίγραφο.</p>
  <?php echo $this->loadTemplate('markup'); ?>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($s->id ?? 0); ?>">
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

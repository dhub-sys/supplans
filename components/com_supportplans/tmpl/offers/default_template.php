<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$t = $this->selected;
$selected = [];
foreach ((array) json_decode((string) ($t->items ?? ''), true) as $pos => $sel) {
    $selected[(int) ($sel['service_id'] ?? 0)] = $sel + ['order' => ($pos + 1) * 10];
}
// Selected services first (template order), then the rest of the library.
$services = $this->services;
usort($services, static function ($a, $b) use ($selected) {
    $oa = $selected[(int) $a->id]['order'] ?? PHP_INT_MAX;
    $ob = $selected[(int) $b->id]['order'] ?? PHP_INT_MAX;
    return $oa <=> $ob ?: ((int) $a->ordering <=> (int) $b->ordering);
});
$fallbackOrder = (count($selected) + 1) * 10;
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('offertemplate.apply')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spSubmit('offertemplate.save')">Αποθήκευση &amp; κλείσιμο</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=offers&section=templates'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo OfferHelper::url('view=offers&section=templates' . ($t ? '&id=' . (int) $t->id : '&mode=new')); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $t ? 'Template: ' . $e($t->name) : 'Νέο template'; ?></h2>
  <div class="sp-grid">
   <label>Όνομα template<input name="name" required value="<?php echo $e($t->name ?? ''); ?>" placeholder="π.χ. Εταιρική ιστοσελίδα + Φιλοξενία"></label>
   <label>Φ.Π.Α. %<input type="number" step="0.01" min="0" name="vat_rate" value="<?php echo $e($t->vat_rate ?? 24); ?>"></label>
   <label>Κατάσταση<select name="published"><option value="1" <?php echo !$t || (int) $t->published ? 'selected' : ''; ?>>Ενεργό</option><option value="0" <?php echo $t && !(int) $t->published ? 'selected' : ''; ?>>Ανενεργό</option></select></label>
  </div>
 </div>

 <div class="sp-card">
  <h3>Υπηρεσίες του template</h3>
  <p class="sp-foot">Τσεκάρετε ποιες υπηρεσίες περιλαμβάνει το template. Για καθεμία ορίστε αν <b>περιγράφεται</b> στο έγγραφο και αν μπαίνει στον <b>πίνακα τιμών</b>. Η «Σειρά» καθορίζει τη σειρά στο Pdf (μικρότερο = νωρίτερα). Οι υπηρεσίες επεξεργάζονται στην καρτέλα <a href="<?php echo OfferHelper::url('view=offers&section=services'); ?>">Υπηρεσίες</a>.</p>
  <?php if (!$services): ?><p class="text-muted">Δεν υπάρχουν υπηρεσίες. Δημιουργήστε πρώτα υπηρεσίες στη βιβλιοθήκη.</p><?php else: ?>
  <div class="table-responsive"><table class="table sp-offer-table sp-template-services">
   <thead><tr><th>Περιλαμβάνεται</th><th>Υπηρεσία</th><th class="sp-center">Περιγραφή</th><th class="sp-center">Πίνακας τιμών</th><th class="sp-num">Τιμή</th><th>Σειρά</th></tr></thead>
   <tbody>
   <?php foreach ($services as $s): $sid = (int) $s->id; $sel = $selected[$sid] ?? null; ?>
    <tr class="<?php echo $sel ? '' : 'is-off'; ?><?php echo (int) $s->published ? '' : ' is-unpublished'; ?>">
     <td><input type="checkbox" name="tpl[<?php echo $sid; ?>][on]" value="1" <?php echo $sel ? 'checked' : ''; ?> onchange="this.closest('tr').classList.toggle('is-off',!this.checked)"></td>
     <td><strong><?php echo $e($s->name); ?></strong><?php if (!(int) $s->published): ?> <small class="text-muted">(ανενεργή)</small><?php endif; ?><br><small class="text-muted"><?php echo $e(trim((string) $s->section_title) ?: $s->name); ?></small></td>
     <td class="sp-center"><input type="checkbox" name="tpl[<?php echo $sid; ?>][desc]" value="1" <?php echo ($sel ? !empty($sel['show_description']) : (int) $s->show_description) ? 'checked' : ''; ?>></td>
     <td class="sp-center"><input type="checkbox" name="tpl[<?php echo $sid; ?>][table]" value="1" <?php echo ($sel ? !empty($sel['in_table']) : (int) $s->in_table) ? 'checked' : ''; ?>></td>
     <td class="sp-num"><?php echo OfferHelper::money((float) $s->price); ?> €</td>
     <td><input type="number" class="sp-order-input" name="tpl[<?php echo $sid; ?>][order]" value="<?php echo (int) ($sel['order'] ?? $fallbackOrder); ?>" step="1"></td>
    </tr>
   <?php if (!$sel) $fallbackOrder += 10; endforeach; ?>
   </tbody>
  </table></div>
  <?php endif; ?>
 </div>

 <div class="sp-card">
  <h3>Κείμενα εγγράφου</h3>
  <div class="sp-grid">
   <label class="sp-wide">Τίτλος εξωφύλλου (μία γραμμή ανά σειρά — το Έργο της προσφοράς προστίθεται ως τελευταία γραμμή)<textarea name="doc_title" rows="2" placeholder="Σχεδιασμός – Υλοποίηση e-shop&#10;Υπηρεσίες Φιλοξενίας &amp; Συμβόλαιο Συντήρησης"><?php echo $e($t->doc_title ?? ''); ?></textarea></label>
   <label>Υπότιτλος<input name="doc_subtitle" value="<?php echo $e($t->doc_subtitle ?? 'ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ'); ?>"></label>
   <label>Κείμενο footer<input name="footer_title" value="<?php echo $e($t->footer_title ?? ''); ?>" placeholder="Αν μείνει κενό, χρησιμοποιείται ο τίτλος"></label>
   <label>Τίτλος ενότητας τιμών<input name="pricing_title" value="<?php echo $e($t->pricing_title ?? 'Αξία Έργου – Τρόπος Πληρωμής'); ?>"></label>
   <label class="sp-check sp-check-inline"><input type="checkbox" name="show_banks" value="1" <?php echo !$t || (int) $t->show_banks ? 'checked' : ''; ?>> Εμφάνιση τραπεζικών λογαριασμών</label>
   <label class="sp-wide">Κείμενο μετά τον πίνακα τιμών (όροι πληρωμής)<textarea name="payment_text" rows="6"><?php echo $e($t->payment_text ?? "Για την έναρξη των εργασιών απαιτείται προκαταβολή 40% επί του συνόλου, και η εξόφληση γίνεται με την παράδοση του έργου.\n\nΓια να κάνετε πληρωμές μπορείτε να χρησιμοποιήσετε κάποιον από τους ακόλουθους τραπεζικούς λογαριασμούς:"); ?></textarea></label>
   <label class="sp-wide">Τίτλος όρων<input name="terms_title" value="<?php echo $e($t->terms_title ?? 'Όροι Προσφοράς / Παραπομπές'); ?>"></label>
   <label class="sp-wide">Όροι προσφοράς / παραπομπές<textarea name="terms_body" rows="10"><?php echo $e($t->terms_body ?? 'Η ισχύς της παρούσας προσφοράς έχει διάρκεια 1 (ενός) μήνα από την ημερομηνία σύνταξής της.'); ?></textarea></label>
  </div>
  <?php echo $this->loadTemplate('markup'); ?>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($t->id ?? 0); ?>">
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

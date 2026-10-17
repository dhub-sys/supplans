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
  <h3>Υπηρεσίες / Περιγραφές του template</h3>
  <p class="sp-foot">Τσεκάρετε ποιες υπηρεσίες και περιγραφές περιλαμβάνει το template. Για καθεμία ορίστε αν <b>περιγράφεται</b> στο έγγραφο και αν μπαίνει στον <b>πίνακα τιμών</b>. Σύρετε από τη λαβή <span class="sp-inline-grip">⋮⋮</span> για να αλλάξετε τη σειρά στο Pdf. Οι υπηρεσίες επεξεργάζονται στην καρτέλα <a href="<?php echo OfferHelper::url('view=offers&section=services'); ?>">Υπηρεσίες</a>.</p>
  <?php if (!$services): ?><p class="text-muted">Δεν υπάρχουν υπηρεσίες. Δημιουργήστε πρώτα υπηρεσίες στη βιβλιοθήκη.</p><?php else: ?>
  <div class="table-responsive"><table class="table sp-offer-table sp-template-services">
   <thead><tr><th class="sp-grip-cell"></th><th>Περιλαμβάνεται</th><th>Τύπος</th><th>Όνομα</th><th class="sp-center">Στο κείμενο</th><th class="sp-center">Πίνακας τιμών</th><th class="sp-num">Τιμή</th></tr></thead>
   <tbody data-sortable id="sp-template-services">
   <?php foreach ($services as $s): $sid = (int) $s->id; $sel = $selected[$sid] ?? null; ?>
    <tr data-sort-item class="<?php echo $sel ? '' : 'is-off'; ?><?php echo (int) $s->published ? '' : ' is-unpublished'; ?>">
     <td class="sp-grip-cell"><button type="button" class="sp-grip" data-sort-handle title="Σύρετε για αλλαγή σειράς" aria-label="Αλλαγή σειράς (σύρετε ή χρησιμοποιήστε τα βελάκια)"><svg viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></button></td>
     <td><input type="checkbox" name="tpl[<?php echo $sid; ?>][on]" value="1" <?php echo $sel ? 'checked' : ''; ?> onchange="this.closest('tr').classList.toggle('is-off',!this.checked)"></td>
     <?php $isDesc = OfferHelper::kind($s->kind ?? 'service') !== 'service'; $isPricing = OfferHelper::kind($s->kind ?? 'service') === 'pricing'; ?>
     <td><?php echo OfferHelper::kindBadge($s->kind ?? 'service'); ?></td>
     <td><strong><?php echo $e($s->name); ?></strong><?php if (!(int) $s->published): ?> <small class="text-muted">(ανενεργή)</small><?php endif; ?><br><small class="text-muted"><?php echo $e(trim((string) $s->section_title) ?: $s->name); ?></small></td>
     <td class="sp-center"><?php if ($isPricing): ?><span class="text-muted">—</span><input type="hidden" name="tpl[<?php echo $sid; ?>][desc]" value="1"><?php else: ?><input type="checkbox" name="tpl[<?php echo $sid; ?>][desc]" value="1" <?php echo ($sel ? !empty($sel['show_description']) : (int) $s->show_description) ? 'checked' : ''; ?>><?php endif; ?></td>
     <td class="sp-center"><?php if ($isDesc): ?><span class="text-muted">—</span><?php else: ?><input type="checkbox" name="tpl[<?php echo $sid; ?>][table]" value="1" <?php echo ($sel ? !empty($sel['in_table']) : (int) $s->in_table) ? 'checked' : ''; ?>><?php endif; ?></td>
     <td class="sp-num"><?php echo $isDesc ? '<span class="text-muted">—</span>' : OfferHelper::money((float) $s->price) . ' €'; ?><input type="hidden" class="sp-order-input" name="tpl[<?php echo $sid; ?>][order]" value="<?php echo (int) ($sel['order'] ?? $fallbackOrder); ?>"></td>
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
   <label class="sp-check sp-check-inline"><input type="checkbox" name="show_banks" value="1" <?php echo !$t || (int) $t->show_banks ? 'checked' : ''; ?>> Τραπεζικοί λογαριασμοί στο block τιμών</label>
   <label class="sp-check sp-check-inline"><input type="checkbox" name="show_header" value="1" <?php echo !$t || (int) ($t->show_header ?? 1) ? 'checked' : ''; ?>> Header σε κάθε σελίδα (εκτός εξωφύλλου)</label>
   <label>Μέγεθος λογοτύπου header (px)<input type="number" min="12" max="120" step="1" name="header_logo" value="<?php echo (int) ($t->header_logo ?? 28); ?>"></label>
   <label>Απόσταση περιεχομένου από το header (mm)<input type="number" min="0" max="40" step="1" name="header_gap" value="<?php echo (int) ($t->header_gap ?? 6); ?>"></label>
   <label class="sp-wide">Κείμενο header<input name="header_text" value="<?php echo $e($t->header_text ?? ''); ?>" placeholder="Αν μείνει κενό: Οικονομική προσφορά + αριθμός · έργο — δέχεται και μεταβλητές"></label>
   <label class="sp-wide">Τίτλος εισαγωγής<input name="intro_title" value="<?php echo $e($t->intro_title ?? 'Εισαγωγή'); ?>" placeholder="Εισαγωγή"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Εισαγωγή (πρώτη ενότητα μετά το εξώφυλλο — αν μείνει κενή δεν εμφανίζεται)</span><textarea name="intro_body" rows="6" data-sp-editor><?php echo $e($t->intro_body ?? ''); ?></textarea></div>
   <div class="sp-wide sp-field"><span class="sp-field-label">Λίστες &amp; επιπλέον κείμενα εισαγωγής (στο Pdf μετά το κείμενο, με αυτή τη σειρά)</span><?php echo OfferHelper::listsEditor('intro_lists', OfferHelper::lists($t->intro_lists ?? '')); ?></div>
  </div>
  <?php echo $this->loadTemplate('markup'); ?>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($t->id ?? 0); ?>">
 <script>
 // Row order in the table = order in the PDF.
 (function () {
   var body = document.getElementById('sp-template-services');
   if (!body) return;
   body.addEventListener('sp-sorted', function () {
     body.querySelectorAll('.sp-order-input').forEach(function (input, i) { input.value = (i + 1) * 10; });
   });
 })();
 </script>
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

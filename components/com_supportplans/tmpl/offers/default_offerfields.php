<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Inputs for the offer fields ({intro_service}, …). Expects $this->fieldValues from OfferHelper::offerFields(). */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="sp-offer-fields">
 <div class="sp-offer-fields-head">
  <h3>Πεδία προσφοράς</h3>
  <a href="<?php echo OfferHelper::url('view=offers&section=variables'); ?>" class="sp-offer-fields-manage">Διαχείριση μεταβλητών →</a>
 </div>
 <?php if (!$this->fieldValues): ?>
 <p class="sp-foot">Δεν έχετε ορίσει μεταβλητές. Στην καρτέλα <a href="<?php echo OfferHelper::url('view=offers&section=variables'); ?>">Μεταβλητές</a> δημιουργήστε π.χ. <code>{intro_service}</code> και εδώ θα εμφανιστεί πεδίο για να το συμπληρώνετε σε κάθε προσφορά.</p>
 <?php else: ?>
 <div class="sp-grid">
  <?php foreach ($this->fieldValues as $f): ?>
  <label class="<?php echo $f['type'] === 'textarea' ? 'sp-wide ' : ''; ?>sp-offer-field<?php echo $f['defined'] ? '' : ' is-orphan'; ?>">
   <span class="sp-offer-field-label"><?php echo $e($f['label']); ?> <code>{<?php echo $e($f['key']); ?>}</code><?php if (!$f['defined']): ?> <small>δεν υπάρχει πλέον στις Μεταβλητές</small><?php endif; ?></span>
   <?php if ($f['type'] === 'textarea'): ?>
   <textarea name="field[<?php echo $e($f['key']); ?>]" rows="3" placeholder="<?php echo $e($f['default']); ?>"><?php echo $e($f['value']); ?></textarea>
   <?php else: ?>
   <input name="field[<?php echo $e($f['key']); ?>]" value="<?php echo $e($f['value']); ?>" placeholder="<?php echo $e($f['default']); ?>">
   <?php endif; ?>
  </label>
  <?php endforeach; ?>
 </div>
 <?php endif; ?>
 <p class="sp-foot sp-builtin-hint">Επίσης διαθέσιμες: <code>{customer}</code>/<code>{client}</code>, <code>{project}</code>/<code>{project_name}</code>, <code>{company}</code>, <code>{number}</code>, <code>{date}</code>.</p>
</div>

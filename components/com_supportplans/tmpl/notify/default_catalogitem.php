<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$k = $this->selected;
$v = static fn($key, $d = '') => $k ? $k->$key : $d;
$next = ($this->catalog ? max(array_map(static fn($x) => (int) $x->ordering, $this->catalog)) : 0) + 1;
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveCatalog')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-after').value='close';spSubmit('notify.saveCatalog')">Αποθήκευση &amp; κλείσιμο</button>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=catalog'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveCatalog')); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $k ? $e($k->name) : 'Νέα υπηρεσία καταλόγου'; ?></h2>
  <div class="sp-grid">
   <label class="sp-wide">Όνομα *<input name="name" required value="<?php echo $e($v('name')); ?>" placeholder="π.χ. Web Hosting Green"></label>
   <label>Τιμή (€)<input name="price" inputmode="decimal" value="<?php echo $k && $k->price !== null ? $e(number_format((float) $k->price, 2, ',', '')) : ''; ?>" placeholder="π.χ. 120,00"></label>
   <label>Διάρκεια (μήνες)<input type="number" min="1" max="120" name="duration_months" value="<?php echo (int) $v('duration_months', 12); ?>"></label>
   <label>Ημέρες υπενθύμισης<input name="reminder_days" value="<?php echo $e($v('reminder_days')); ?>" placeholder="<?php echo $e(Notify::setting('notify_reminder_days')); ?>"></label>
   <label>Σειρά<input type="number" name="ordering" value="<?php echo (int) $v('ordering', $next); ?>"></label>
   <label>Κατάσταση<?php echo NotifyUi::select('published', [1 => 'Ενεργή', 0 => 'Ανενεργή'], (int) $v('published', 1)); ?></label>
   <div class="sp-wide sp-check-row"><label class="sp-check"><input type="checkbox" name="auto_remind" value="1" <?php echo (int) $v('auto_remind', 1) ? 'checked' : ''; ?>> Αυτόματες υπενθυμίσεις ανανέωσης (προεπιλογή για νέες αναθέσεις)</label></div>
   <label class="sp-wide">Περιγραφή (εσωτερική)<textarea name="description" rows="3"><?php echo $e($v('description')); ?></textarea></label>
  </div>
  <p class="sp-n-help">Ημέρες υπενθύμισης: χωρισμένες με κόμμα, π.χ. <code>30,14,7,1,0,-3</code>. <code>0</code> = την ημέρα λήξης, αρνητικό = μετά τη λήξη. Κενό = η γενική ρύθμιση (<?php echo $e(Notify::setting('notify_reminder_days')); ?>). Αλλαγές εδώ ισχύουν για όλους τους πελάτες με αυτή την υπηρεσία, εκτός αν έχουν δικές τους ημέρες.</p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($k->id ?? 0); ?>"><input type="hidden" name="after" id="sp-after" value=""><input type="hidden" name="task" value="notify.saveCatalog">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($k): ?>
<div class="sp-card"><h2>Διαγραφή</h2><p class="sp-n-help">Μια υπηρεσία που έχει ανατεθεί σε πελάτες δεν διαγράφεται· απενεργοποιήστε την.</p>
<?php echo NotifyUi::button('deleteCatalog', ['id' => $k->id], 'Διαγραφή υπηρεσίας', 'Διαγραφή της υπηρεσίας από τον κατάλογο;', 'btn btn-outline-danger'); ?></div>
<?php endif; ?>

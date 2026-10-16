<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('offer.create')">Δημιουργία προσφοράς</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=offers'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo OfferHelper::url('view=offers&mode=new'); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <h2>Νέα προσφορά</h2>
  <div class="sp-grid">
   <label class="sp-wide">Template
    <select name="template_id" required>
     <option value="">— Επιλογή —</option>
     <?php foreach ($this->templates as $t): if (!(int) $t->published) continue; $count = count((array) json_decode((string) $t->items, true)); ?>
     <option value="<?php echo (int) $t->id; ?>"><?php echo $e($t->name . ' — ' . $count . ' υπηρεσίες'); ?></option>
     <?php endforeach; ?>
    </select>
   </label>
   <label>Πελάτης<input name="customer_name" required placeholder="π.χ. Νίκος Δημοσθένους"></label>
   <label>Έργο / Εταιρεία<input name="project_name" placeholder="π.χ. Irida Music"></label>
   <label>Αρ. προσφοράς<input name="offer_number" value="<?php echo $e($this->nextNumber); ?>"></label>
   <label>Ημερομηνία<input type="date" name="offer_date" value="<?php echo date('Y-m-d'); ?>" required></label>
  </div>
  <?php $this->fieldValues = OfferHelper::offerFields(null, $this->params); echo $this->loadTemplate('offerfields'); ?>
  <p class="sp-foot">Τα κείμενα, οι υπηρεσίες και οι τιμές αντιγράφονται από το template. Στο επόμενο βήμα επιλέγετε ποιες υπηρεσίες περιγράφονται στο έγγραφο, ποιες μπαίνουν στον πίνακα τιμών και τυχόν εκπτώσεις.</p>
 </div>
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

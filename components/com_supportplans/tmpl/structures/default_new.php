<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('structure.create')">Δημιουργία πρότασης</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=structures'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('view=structures&mode=new', false)); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <h2>Νέα πρόταση δομής</h2>
  <div class="sp-grid">
   <label>Πελάτης<input name="customer_name" required placeholder="Επωνυμία"></label>
   <label>Έργο / Ιστοσελίδα<input name="project_name" placeholder="π.χ. www.example.gr"></label>
   <label>Αρ. πρότασης<input name="number" value="<?php echo $e($this->nextNumber); ?>"></label>
   <label>Ημερομηνία<input type="date" name="proposal_date" value="<?php echo date('Y-m-d'); ?>" required></label>
   <label class="sp-wide">Αρχική δομή
    <select name="starter">
     <option value="basic">Εταιρική ιστοσελίδα (Αρχική, Η εταιρεία, Υπηρεσίες, Portfolio, Blog, Επικοινωνία)</option>
     <option value="eshop">E-shop (Κατάστημα με κατηγορίες, Εξυπηρέτηση πελατών, Λογαριασμός…)</option>
     <option value="empty">Κενή (μόνο Αρχική)</option>
    </select>
   </label>
  </div>
  <p class="sp-foot">Η αρχική δομή είναι σημείο εκκίνησης: στο επόμενο βήμα προσθέτετε, μετονομάζετε, μετακινείτε ή διαγράφετε ενότητες και υποενότητες.</p>
 </div>
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

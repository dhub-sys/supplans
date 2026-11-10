<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$c = $this->selected;
$v = static fn($k, $d = '') => $c ? $c->$k : $d;
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('notify.saveClient')">Αποθήκευση</button>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=clients'); ?>">Κλείσιμο</a>
 <?php if ($c): ?><a class="btn btn-outline-primary" href="<?php echo NotifyUi::url('section=services&mode=new&client_id=' . (int) $c->id); ?>">＋ Ανάθεση υπηρεσίας</a><?php endif; ?>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveClient')); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $c ? $e(Notify::clientLabel($c)) : 'Νέος πελάτης'; ?></h2>
  <div class="sp-grid">
   <label>Ονοματεπώνυμο<input name="name" value="<?php echo $e($v('name')); ?>"></label>
   <label>Εταιρεία<input name="company" value="<?php echo $e($v('company')); ?>"></label>
   <label>Email *<input type="email" name="email" required value="<?php echo $e($v('email')); ?>"></label>
   <label>Τηλέφωνο<input name="phone" value="<?php echo $e($v('phone')); ?>"></label>
   <label>Κατάσταση<?php echo NotifyUi::select('status', Notify::CLIENT_STATUSES, $v('status', 'active')); ?></label>
   <label class="sp-wide">Σημειώσεις<textarea name="notes" rows="3"><?php echo $e($v('notes')); ?></textarea></label>
  </div>
  <p class="sp-foot">Σε ανενεργούς πελάτες δεν αποστέλλεται κανένα μήνυμα.</p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($c->id ?? 0); ?>"><input type="hidden" name="task" value="notify.saveClient">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($c): ?>
<div class="sp-card">
  <h2>Ενημερωτικά μηνύματα</h2>
  <p><?php if ($c->unsubscribed_at): ?>
    <span class="sp-offer-status">Διαγράφηκε στις <?php echo $e(Notify::datetimeGr($c->unsubscribed_at)); ?></span>
    <?php echo NotifyUi::button('unsubscribe', ['id' => $c->id, 'value' => 0], 'Επανεγγραφή', 'Επανεγγραφή μόνο αν ο πελάτης το ζήτησε. Συνέχεια;', 'btn btn-sm btn-outline-secondary'); ?>
  <?php else: ?>
    <span class="sp-offer-status is-accepted">Λαμβάνει</span>
    <?php echo NotifyUi::button('unsubscribe', ['id' => $c->id, 'value' => 1], 'Διαγραφή από ενημερώσεις', '', 'btn btn-sm btn-outline-secondary'); ?>
  <?php endif; ?></p>
  <p class="sp-n-help">Οι υπενθυμίσεις ανανέωσης και οι αναφορές maintenance αφορούν υπηρεσίες του πελάτη και στέλνονται πάντα· η διαγραφή ισχύει μόνο για τις «Αποστολές».</p>
</div>
<div class="sp-card">
  <div class="sp-title-row"><h2>Υπηρεσίες</h2><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=services&mode=new&client_id=' . (int) $c->id); ?>">＋ Ανάθεση υπηρεσίας</a></div>
  <?php echo $this->loadTemplate('servicestable'); ?>
</div>
<div class="sp-card">
  <h2>Τελευταία μηνύματα</h2>
  <?php echo $this->loadTemplate('logtable'); ?>
</div>
<div class="sp-card">
  <h2>Διαγραφή πελάτη</h2>
  <p class="sp-n-help">Διαγράφονται μαζί οι υπηρεσίες, οι αναφορές και το ιστορικό μηνυμάτων του πελάτη.</p>
  <?php echo NotifyUi::button('deleteClient', ['id' => $c->id], 'Διαγραφή πελάτη', 'Οριστική διαγραφή του πελάτη και όλων των δεδομένων του;', 'btn btn-outline-danger'); ?>
</div>
<?php endif; ?>

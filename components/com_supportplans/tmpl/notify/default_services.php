<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$catalogOptions = [];
foreach ($this->catalog as $k) $catalogOptions[$k->id] = $k->name;
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Client Management</span><h1>Υπηρεσίες πελατών</h1><p>Οι υπηρεσίες του καταλόγου που έχετε αναθέσει σε πελάτες, ταξινομημένες κατά ημερομηνία λήξης. Με «Ανανεώθηκε» η λήξη μετακινείται μία περίοδο και οι εκκρεμείς υπενθυμίσεις ακυρώνονται.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=services&mode=new'); ?>">＋ Ανάθεση υπηρεσίας</a>
</section>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(NotifyUi::url('', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="services">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
    <input type="search" name="q" value="<?php echo $e($this->filters['search']); ?>" placeholder="Domain, πελάτης, υπηρεσία">
    <?php echo NotifyUi::select('catalog_id', $catalogOptions, $this->filters['catalog_id'] ?: '', 'Όλες οι υπηρεσίες'); ?>
    <?php echo NotifyUi::select('status', Notify::SERVICE_STATUSES, $this->filters['status'], 'Όλες οι καταστάσεις'); ?>
    <?php echo NotifyUi::select('expiring', ['30' => 'Λήγουν σε 30 ημέρες', '60' => 'Λήγουν σε 60 ημέρες', '90' => 'Λήγουν σε 90 ημέρες'], $this->filters['expiring'], 'Οποιαδήποτε λήξη'); ?>
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
  </form>
  <?php echo $this->loadTemplate('servicestable'); ?>
</div>

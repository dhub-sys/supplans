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
  <div><span class="sp-eyebrow">Ανανεώσεις</span><h1>Υπηρεσίες πελατών</h1><p>Οι υπηρεσίες του καταλόγου που έχετε αναθέσει σε πελάτες, ταξινομημένες κατά ημερομηνία λήξης. Με «Ανανεώθηκε» η λήξη μετακινείται μία περίοδο και οι εκκρεμείς υπενθυμίσεις ακυρώνονται.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=services&mode=new'); ?>">＋ Ανάθεση υπηρεσίας</a>
</section>
<?php $sm = $this->summary; $m = static fn($v) => number_format((float) $v, 2, ',', '.') . ' €'; $dr = static fn($a, $b) => Notify::dateGr($a) . ($a !== $b ? ' – ' . Notify::dateGr($b) : ''); ?>
<div class="sp-card sp-n-summary">
  <div class="sp-title-row">
    <h2>Σύνοψη ανανεώσεων</h2>
    <form class="sp-n-sumform" method="get" action="<?php echo $e(NotifyUi::url('', false)); ?>">
      <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="services">
      <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
      <?php foreach (['q' => $this->filters['search'], 'catalog_id' => $this->filters['catalog_id'] ?: '', 'status' => $this->filters['status'], 'expiring' => $this->filters['expiring']] as $hk => $hv): if ((string) $hv === '') continue; ?><input type="hidden" name="<?php echo $hk; ?>" value="<?php echo $e($hv); ?>"><?php endforeach; ?>
      <select name="sp" onchange="this.form.querySelector('.sp-n-sumdates').hidden=this.value!=='custom';if(this.value!=='custom')this.form.submit();" aria-label="Περίοδος">
        <?php foreach (Notify::SUMMARY_PERIODS as $pk => $pl): if ($pk === 'year') $pl = \Support\Component\Supportplans\Site\Helper\Year::isCurrent() ? 'Τρέχον έτος' : 'Όλο το ' . \Support\Component\Supportplans\Site\Helper\Year::get(); ?><option value="<?php echo $pk; ?>"<?php echo $this->summaryPeriod === $pk ? ' selected' : ''; ?>><?php echo $e($pl); ?></option><?php endforeach; ?>
      </select>
      <span class="sp-n-sumdates"<?php echo $this->summaryPeriod === 'custom' ? '' : ' hidden'; ?>>
        <input type="date" name="sf" value="<?php echo $e($this->summaryFrom); ?>" aria-label="Από"> – <input type="date" name="st" value="<?php echo $e($this->summaryTo); ?>" aria-label="Έως">
        <button class="btn btn-sm btn-outline-secondary">Εφαρμογή</button>
      </span>
    </form>
  </div>
  <div class="sp-n-sumgrid">
    <div class="sp-n-sum is-paid"><span>Πληρώθηκαν</span><strong><?php echo $m($sm['paid']['gross']); ?></strong><small><?php echo (int) $sm['paid']['count']; ?> πληρωμές · <?php echo $e($dr($sm['paid']['from'], $sm['paid']['to'])); ?> · με ΦΠΑ</small></div>
    <div class="sp-n-sum is-due"><span>Προς πληρωμή</span><strong><?php echo $m($sm['due']['gross']); ?></strong><small><?php echo (int) $sm['due']['count']; ?> υπηρεσίες λήγουν <?php echo $e($dr($sm['due']['from'], $sm['due']['to'])); ?> · <?php echo $e($m($sm['due']['net'])); ?> προ ΦΠΑ</small></div>
    <div class="sp-n-sum is-overdue"><span>Ληγμένες, απλήρωτες</span><strong><?php echo $m($sm['overdue']['gross']); ?></strong><small><?php echo (int) $sm['overdue']['count']; ?> υπηρεσίες · <?php echo $e($m($sm['overdue']['net'])); ?> προ ΦΠΑ</small></div>
  </div>
  <?php if ($sm['due']['by']): ?>
  <details class="sp-n-sumby"><summary>Προς πληρωμή ανά υπηρεσία</summary>
    <table class="table sp-offer-table"><thead><tr><th>Υπηρεσία</th><th class="sp-num">Πλήθος</th><th class="sp-num">Προ ΦΠΑ</th><th class="sp-num">Με ΦΠΑ</th></tr></thead><tbody>
    <?php foreach ($sm['due']['by'] as $bn => [$bc, $bv]): ?><tr><td><?php echo $e($bn); ?></td><td class="sp-num"><?php echo (int) $bc; ?></td><td class="sp-num"><?php echo $m($bv); ?></td><td class="sp-num"><?php echo $m(round($bv * (1 + Notify::vatRate() / 100), 2)); ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </details>
  <?php endif; ?>
</div>
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

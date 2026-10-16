<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$total = count($this->offers);
$pending = count(array_filter($this->offers, static fn($o) => in_array($o->status, ['draft', 'sent'], true)));
$acceptedValue = array_sum(array_map(static fn($o) => $o->status === 'accepted' ? (float) $o->totals['net'] : 0, $this->offers));
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Προσφορές</span><h1>Οικονομικές προσφορές</h1><p>Δημιουργήστε προσφορές από έτοιμα templates, επιλέξτε υπηρεσίες και αποθηκεύστε τες σε Pdf.</p></div>
  <?php if ($this->templates): ?><a class="sp-primary-action" href="<?php echo OfferHelper::url('view=offers&mode=new'); ?>">＋ Νέα προσφορά</a><?php endif; ?>
</section>
<section class="sp-kpis">
  <article><span>Σύνολο προσφορών</span><strong><?php echo $total; ?></strong><small>όλες οι εγγραφές</small></article>
  <article><span>Σε εκκρεμότητα</span><strong><?php echo $pending; ?></strong><small>πρόχειρες &amp; απεσταλμένες</small></article>
  <article><span>Αποδεκτές (προ Φ.Π.Α.)</span><strong><?php echo OfferHelper::money($acceptedValue); ?> €</strong><small>αξία αποδεκτών προσφορών</small></article>
</section>
<?php if (!$this->templates): ?>
<div class="sp-card sp-offer-empty">
  <h2>Ξεκινήστε με ένα template</h2>
  <p>Κάθε προσφορά δημιουργείται από ένα template. Φτιάξτε πρώτα τις <a href="<?php echo OfferHelper::url('view=offers&section=services'); ?>">Υπηρεσίες</a> (κείμενα και τιμές) και μετά ένα <a href="<?php echo OfferHelper::url('view=offers&section=templates'); ?>">Template</a> που τις συνδυάζει — ή δημιουργήστε ένα έτοιμο παράδειγμα και προσαρμόστε το.</p>
  <form method="post" action="<?php echo OfferHelper::url('task=offertemplate.sample'); ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-primary">Δημιουργία παραδείγματος (e-shop, φιλοξενία, συντήρηση)</button></form>
</div>
<?php endif; ?>
<div class="sp-card">
  <h2>Προσφορές</h2>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Αρ.</th><th>Ημ/νία</th><th>Πελάτης</th><th>Έργο</th><th class="sp-num">Αξία προ Φ.Π.Α.</th><th class="sp-num">Σύνολο</th><th>Κατάσταση</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->offers as $o): ?>
      <tr>
        <td><strong><?php echo $e($o->offer_number); ?></strong></td>
        <td><?php echo $e(OfferHelper::dateGr($o->offer_date)); ?></td>
        <td><?php echo $e($o->customer_name); ?></td>
        <td><?php echo $e($o->project_name ?: '—'); ?></td>
        <td class="sp-num"><?php echo OfferHelper::money($o->totals['net']); ?> €</td>
        <td class="sp-num"><?php echo OfferHelper::money($o->totals['gross']); ?> €</td>
        <td><span class="sp-offer-status is-<?php echo $e($o->status); ?>"><?php echo $e(OfferHelper::statusLabel($o->status)); ?></span></td>
        <td class="sp-num"><a class="btn btn-sm btn-outline-primary" href="<?php echo OfferHelper::url('view=offers&id=' . (int) $o->id); ?>">Άνοιγμα</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->offers): ?><tr><td colspan="8" class="text-muted">Δεν υπάρχουν προσφορές ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

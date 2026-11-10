<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Πελάτες &amp; Ειδοποιήσεις</span><h1>Πελάτες</h1><p>Τα στοιχεία επικοινωνίας των πελατών σας. Από τον πελάτη αναθέτετε υπηρεσίες του καταλόγου και βλέπετε τι του έχει σταλεί.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=clients&mode=new'); ?>">＋ Νέος πελάτης</a>
</section>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(NotifyUi::url()); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="clients">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
    <input type="search" name="q" value="<?php echo $e($this->filters['search']); ?>" placeholder="Όνομα, εταιρεία ή email">
    <?php echo NotifyUi::select('status', Notify::CLIENT_STATUSES, $this->filters['status'], 'Όλες οι καταστάσεις'); ?>
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
  </form>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Πελάτης</th><th>Email</th><th>Τηλέφωνο</th><th class="sp-center">Ενεργές υπηρεσίες</th><th>Κατάσταση</th><th>Ενημερώσεις</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $c): ?>
      <tr>
        <td><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $c->id); ?>"><strong><?php echo $e($c->name ?: '—'); ?></strong></a><?php if ($c->company !== ''): ?><br><small class="text-muted"><?php echo $e($c->company); ?></small><?php endif; ?></td>
        <td><?php echo $e($c->email); ?></td>
        <td><?php echo $e($c->phone); ?></td>
        <td class="sp-center"><?php echo (int) $c->active_services; ?></td>
        <td><?php echo NotifyUi::status($c->status, Notify::CLIENT_STATUSES); ?></td>
        <td><?php echo $c->unsubscribed_at ? '<span class="sp-offer-status">Διαγράφηκε</span>' : '<span class="sp-offer-status is-accepted">Ναι</span>'; ?></td>
        <td class="sp-num sp-row-actions">
          <a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=clients&id=' . (int) $c->id); ?>">Άνοιγμα</a>
          <a class="btn btn-sm btn-outline-secondary" href="<?php echo NotifyUi::url('section=services&mode=new&client_id=' . (int) $c->id); ?>">＋ Υπηρεσία</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν πελάτες.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<div class="sp-card">
  <h2>Εισαγωγή από CSV</h2>
  <p class="sp-n-help">Πρώτη γραμμή επικεφαλίδες, διαχωριστικό <code>;</code> ή <code>,</code>, UTF-8. Υποχρεωτική στήλη <code>email</code>. Προαιρετικά <code>name</code>, <code>company</code>, <code>phone</code> και για ανάθεση υπηρεσίας: <code>service</code> (όνομα υπηρεσίας από τον κατάλογο), <code>label</code> (π.χ. domain), <code>expiry_date</code> (ΗΗ/ΜΜ/ΕΕΕΕ), <code>price</code>, <code>duration_months</code>. Ίδιο email = ίδιος πελάτης· ίδιος πελάτης + υπηρεσία + label = ενημέρωση.</p>
  <form method="post" enctype="multipart/form-data" action="<?php echo $e(OfferHelper::url('task=notify.importCsv')); ?>" class="sp-n-filters">
    <input type="file" name="csv" accept=".csv,text/csv" required>
    <?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-primary">Εισαγωγή</button>
  </form>
</div>

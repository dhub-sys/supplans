<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
$all = Sites::sites();
$byCms = array_count_values(array_map(static fn($s) => $s->cms, $all));
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Sites</h1><p>Τα sites των πελατών με το CMS και τα extensions τους (plugins, themes, γλώσσες) και τις εκδόσεις τους. Μετά από κάθε μαζικό update, στο «Νέες ενημερώσεις» γράφετε τις νέες εκδόσεις. Κάθε πελάτης λαμβάνει σύνοψη με τις ενημερώσεις όλων των sites του, με τη συχνότητα που ορίζετε (π.χ. 1 ή 2 φορές τον μήνα).</p></div>
  <div class="sp-head-actions">
    <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=sites&mode=new'); ?>">＋ Νέο site</a>
    <a class="sp-primary-action is-secondary" href="<?php echo NotifyUi::url('section=siteupdates'); ?>">↻ Νέες ενημερώσεις</a>
  </div>
</section>
<section class="sp-kpis sp-n-kpis">
  <article><span>Sites</span><strong><?php echo count($all); ?></strong><small>συνολικά</small></article>
  <article><span>Joomla</span><strong><?php echo (int) ($byCms['joomla'] ?? 0); ?></strong><small>sites</small></article>
  <article><span>WordPress</span><strong><?php echo (int) ($byCms['wordpress'] ?? 0); ?></strong><small>sites</small></article>
  <article><span>Extensions</span><strong><?php echo array_sum(array_map(static fn($s) => (int) $s->items, $all)); ?></strong><small>εγκαταστάσεις σε όλα τα sites</small></article>
</section>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(NotifyUi::url('', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="sites">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
    <input type="search" name="q" value="<?php echo $e($this->filters['search']); ?>" placeholder="Site, πελάτης ή extension">
    <?php echo NotifyUi::select('cms', Sites::CMS, $this->filters['cms'], 'Όλα τα CMS'); ?>
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
  </form>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Site</th><th>Πελάτης</th><th>CMS</th><th class="sp-center">Extensions</th><th>Τελευταία ενημέρωση</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $s): ?>
      <tr<?php echo $s->status !== 'active' ? ' class="text-muted"' : ''; ?>>
        <td><a href="<?php echo NotifyUi::url('section=sites&id=' . (int) $s->id); ?>"><strong><?php echo $e($s->domain); ?></strong></a><?php if ($s->status !== 'active'): ?> <span class="sp-offer-status">Ανενεργό</span><?php endif; ?></td>
        <td><?php if ($s->client_id): ?><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id); ?>"><?php echo $e(Notify::clientLabel($s, 'client_')); ?></a><?php else: ?>—<?php endif; ?></td>
        <td><span class="sp-cms is-<?php echo $e($s->cms); ?>"><?php echo $e(Sites::CMS[$s->cms] ?? $s->cms); ?></span> <?php echo $e((string) $s->cms_version); ?></td>
        <td class="sp-center"><?php echo (int) $s->items; ?></td>
        <td><?php echo $s->last_update ? $e(Notify::datetimeGr($s->last_update)) : '<span class="text-muted">—</span>'; ?></td>
        <td class="sp-num sp-row-actions"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=sites&id=' . (int) $s->id); ?>">Άνοιγμα</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="6" class="text-muted"><?php echo $all ? 'Κανένα site με αυτά τα φίλτρα.' : 'Δεν υπάρχουν sites. Προσθέστε ένα με «＋ Νέο site» ή εισάγετε όλα μαζί από Excel (παρακάτω).'; ?></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<div class="sp-card">
  <h2>Εισαγωγή / εξαγωγή Excel</h2>
  <p class="sp-n-help">Μία γραμμή για κάθε extension κάθε site: <code>Email | Πελάτης | Site | CMS | Έκδοση CMS | Extension | Έκδοση</code>. Η εξαγωγή δίνει όλα τα sites σε αυτή τη μορφή (ή παράδειγμα, αν δεν υπάρχουν ακόμα): τη συμπληρώνετε ή τη διορθώνετε και την ανεβάζετε πάλι. Sites αναγνωρίζονται από το domain και πελάτες από το email. Αν το email δεν υπάρχει, δημιουργείται νέος πελάτης. Γράφετε τα ονόματα των extensions πάντα με τον ίδιο τρόπο (π.χ. «YOOtheme Pro»), ώστε να ενημερώνονται μαζικά.</p>
  <div class="sp-n-actions">
    <form method="post" action="<?php echo $e(OfferHelper::url('task=sites.export', false)); ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-secondary">⬇ Εξαγωγή / πρότυπο Excel</button></form>
    <form method="post" enctype="multipart/form-data" class="sp-n-filters" action="<?php echo $e(OfferHelper::url('task=sites.import', false)); ?>">
      <input type="file" name="sheet" accept=".xlsx,.csv" required><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-primary">⬆ Εισαγωγή</button>
    </form>
  </div>
</div>

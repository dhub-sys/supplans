<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
$s = $this->selected;
$core = null;
$lines = [];
foreach ($this->items as $i) {
    if ((int) $i->is_core) { $core = $i; continue; }
    $lines[] = $i->name . ' | ' . $i->version;
}
$clientId = $s ? (int) $s->client_id : Factory::getApplication()->input->getInt('client_id', 0);
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('sites.save')">Αποθήκευση</button>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=sites'); ?>">Κλείσιμο</a>
 <?php if ($s && $s->client_id): ?><a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=clients&id=' . (int) $s->client_id); ?>">Καρτέλα πελάτη</a><?php endif; ?>
</div>
<form action="<?php echo $e(OfferHelper::url('task=sites.save', false)); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $s ? $e($s->domain) : 'Νέο site'; ?></h2>
  <div class="sp-grid">
   <label>Site (domain) *<input name="domain" required value="<?php echo $e($s->domain ?? ''); ?>" placeholder="example.gr"></label>
   <label>Πελάτης *<select name="client_id" required><option value="">— Επιλέξτε —</option><?php foreach ($this->clients as $c): ?><option value="<?php echo (int) $c->id; ?>"<?php echo (int) $c->id === $clientId ? ' selected' : ''; ?>><?php echo $e(Notify::clientLabel($c) . ' — ' . $c->email); ?></option><?php endforeach; ?></select></label>
   <label>CMS<?php echo NotifyUi::select('cms', Sites::CMS, $s->cms ?? 'joomla'); ?></label>
   <label>Έκδοση CMS<input name="cms_version" value="<?php echo $e($core->version ?? ''); ?>" placeholder="π.χ. 6.2.0"></label>
   <label>Κατάσταση<?php echo NotifyUi::select('status', Sites::STATUSES, $s->status ?? 'active'); ?></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Extensions / plugins / themes / γλώσσες</span>
    <textarea name="items" rows="<?php echo max(8, min(30, count($lines) + 3)); ?>" class="sp-mono" placeholder="YOOtheme Pro | 5.5.3&#10;Akeeba Backup Pro | 10.2.2&#10;Ελληνικά (Greek) | 6.2.0.1"><?php echo $e(implode("\n", $lines)); ?></textarea>
    <small class="sp-n-help">Ένα ανά γραμμή: <code>Όνομα | Έκδοση</code>. Αρκεί και <code>Όνομα 1.2.3</code> ή αντιγραφή με tab από πίνακα. Γράφετε τα ονόματα με τον ίδιο τρόπο σε όλα τα sites, ώστε να ενημερώνονται μαζικά. Όποια γραμμή σβήσετε αφαιρείται από το site.</small></div>
   <label class="sp-wide">Σημειώσεις<textarea name="notes" rows="2"><?php echo $e($s->notes ?? ''); ?></textarea></label>
  </div>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($s->id ?? 0); ?>"><input type="hidden" name="task" value="sites.save">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($s): ?>
<div class="sp-card">
  <h2>Ιστορικό ενημερώσεων</h2>
  <?php if (!$this->history): ?><p class="text-muted">Δεν έχουν καταγραφεί ενημερώσεις ακόμα.</p><?php else: ?>
  <div class="table-responsive"><table class="table sp-offer-table">
   <thead><tr><th>Ημερομηνία</th><th>Στοιχείο</th><th>Από</th><th>Σε</th></tr></thead>
   <tbody><?php foreach ($this->history as $h): ?><tr><td><?php echo $e(Notify::datetimeGr($h->created)); ?></td><td><strong><?php echo $e($h->name); ?></strong></td><td class="text-muted"><?php echo $e($h->old_version ?: '—'); ?></td><td><strong><?php echo $e($h->new_version); ?></strong></td></tr><?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
</div>
<div class="sp-card">
  <h2>Διαγραφή site</h2>
  <p class="sp-n-help">Διαγράφονται οι εκδόσεις και το ιστορικό ενημερώσεων του site. Ο πελάτης και τα μηνύματα που στάλθηκαν μένουν.</p>
  <?php echo NotifyUi::button('sites.delete', ['id' => $s->id], 'Διαγραφή site', 'Οριστική διαγραφή του site ' . $s->domain . ' με τις εκδόσεις και το ιστορικό του;', 'btn btn-outline-danger'); ?>
</div>
<?php endif; ?>

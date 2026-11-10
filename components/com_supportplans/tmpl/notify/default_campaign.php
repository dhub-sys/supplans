<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$k = $this->selected;
$locked = $k && $k->status !== 'draft';
$aud = $k ? (json_decode((string) $k->audience, true) ?: ['mode' => 'all']) : ['mode' => 'all'];
$mode = $aud['mode'] ?? 'all';
$selCatalog = array_map('intval', (array) ($aud['catalog'] ?? []));
$selClients = array_map('intval', (array) ($aud['clients'] ?? []));
$dis = $locked ? ' disabled' : '';
?>
<div class="sp-standalone-actions sp-offer-actions">
<?php if (!$locked): ?>
 <button type="button" class="btn btn-primary" onclick="document.getElementById('sp-do').value='save';spSubmit('notify.saveCampaign')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-do').value='test';spSubmit('notify.saveCampaign')">Αποθήκευση &amp; δοκιμή στο email μου</button>
 <button type="button" class="btn btn-outline-primary" onclick="if(confirm('Έναρξη αποστολής σε όλους τους παραλήπτες; Δεν αναιρείται.')){document.getElementById('sp-do').value='send';spSubmit('notify.saveCampaign')}">Αποθήκευση &amp; αποστολή</button>
<?php endif; ?>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=campaigns'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveCampaign')); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $k ? 'Αποστολή' : 'Νέα αποστολή'; ?><?php if ($locked): ?> <span class="sp-offer-status is-sent">Σε ουρά από <?php echo $e(Notify::datetimeGr($k->queued)); ?></span><?php endif; ?></h2>
  <div class="sp-grid">
   <label class="sp-wide">Θέμα *<input name="subject" required value="<?php echo $e($k->subject ?? ''); ?>"<?php echo $dis; ?>></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο</span><textarea name="body" rows="14"<?php echo $locked ? ' disabled' : ' data-sp-editor'; ?>><?php echo $e($k->body ?? ''); ?></textarea>
   <p class="sp-n-help">Μεταβλητές: <code>{client_name}</code> <code>{client_company}</code> <code>{client_email}</code> <code>{services_list}</code> (οι ενεργές υπηρεσίες του πελάτη) και <code>{banks}</code> (τραπεζικοί λογαριασμοί), σε δική τους γραμμή, <code>{company}</code>. Ο σύνδεσμος διαγραφής από τις ενημερώσεις μπαίνει αυτόματα στο τέλος.</p></div>
   <fieldset class="sp-wide sp-n-audience"<?php echo $dis; ?>>
    <span class="sp-field-label">Παραλήπτες</span>
    <label><input type="radio" name="aud_mode" value="all" <?php echo $mode === 'all' ? 'checked' : ''; ?>> Όλοι οι ενεργοί πελάτες</label>
    <label><input type="radio" name="aud_mode" value="catalog" <?php echo $mode === 'catalog' ? 'checked' : ''; ?>> Πελάτες με ενεργή υπηρεσία:</label>
    <div class="sp-n-sub"><?php foreach ($this->catalog as $c): ?><label><input type="checkbox" name="aud_catalog[]" value="<?php echo (int) $c->id; ?>" <?php echo in_array((int) $c->id, $selCatalog, true) ? 'checked' : ''; ?>> <?php echo $e($c->name); ?></label><?php endforeach; ?><?php if (!$this->catalog): ?><small class="text-muted">Ο κατάλογος είναι κενός.</small><?php endif; ?></div>
    <label><input type="radio" name="aud_mode" value="clients" <?php echo $mode === 'clients' ? 'checked' : ''; ?>> Επιλεγμένοι πελάτες:</label>
    <div class="sp-n-sub"><select name="aud_clients[]" multiple><?php foreach ($this->clients as $c): ?><option value="<?php echo (int) $c->id; ?>" <?php echo in_array((int) $c->id, $selClients, true) ? 'selected' : ''; ?>><?php echo $e(Notify::clientLabel($c) . ' — ' . $c->email); ?></option><?php endforeach; ?></select></div>
    <?php if ($k): ?><p><strong><?php echo $locked ? 'Παραλήπτες' : 'Παραλήπτες με τις αποθηκευμένες επιλογές'; ?>: <?php echo (int) $this->total; ?></strong></p><?php endif; ?>
   </fieldset>
  </div>
  <?php if (!$locked): ?><p class="sp-n-help">Τα μηνύματα μπαίνουν σε ουρά και αποστέλλονται σταδιακά (<?php echo (int) Notify::setting('notify_batch_size'); ?> ανά εκτέλεση της εργασίας), ώστε να μη χαρακτηριστούν spam και να μην ξεπεραστούν τα όρια του server.</p>
  <?php else: ?><p><a href="<?php echo NotifyUi::url('section=log&campaign_id=' . (int) $k->id); ?>">Δείτε τα μηνύματα αυτής της αποστολής</a></p><?php endif; ?>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($k->id ?? 0); ?>"><input type="hidden" name="do" id="sp-do" value="save"><input type="hidden" name="task" value="notify.saveCampaign">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($this->preview): ?>
<div class="sp-card"><h2>Προεπισκόπηση</h2><dl class="sp-n-meta"><dt>Θέμα</dt><dd><?php echo $e($this->preview['subject']); ?></dd></dl>
<?php echo NotifyUi::preview($this->preview['body']); ?></div>
<?php endif; ?>

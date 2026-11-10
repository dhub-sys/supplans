<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$r = $this->selected;
$sent = $r && $r->status === 'sent';
$serviceId = $r ? (int) $r->client_service_id : Factory::getApplication()->input->getInt('service', 0);
$options = [];
foreach ($this->services as $s) $options[$s->id] = Notify::clientLabel($s, 'client_') . ' — ' . ($s->service_name ?: 'Υπηρεσία') . ($s->label !== '' ? ' ' . $s->label : '');
if ($serviceId && !isset($options[$serviceId]) && ($s = Notify::service($serviceId))) {
    $options[$s->id] = Notify::clientLabel($s, 'client_') . ' — ' . ($s->service_name ?: 'Υπηρεσία') . ' ' . $s->label;
}
$months = ['Ιανουάριος','Φεβρουάριος','Μάρτιος','Απρίλιος','Μάιος','Ιούνιος','Ιούλιος','Αύγουστος','Σεπτέμβριος','Οκτώβριος','Νοέμβριος','Δεκέμβριος'];
$today = new \DateTimeImmutable('now', Notify::tz());
$period = $r ? $r->period : $months[(int) $today->format('n') - 1] . ' ' . $today->format('Y');
$dis = $sent ? ' disabled' : '';
?>
<?php if (!$options): ?>
<div class="sp-card"><h2>Δεν υπάρχουν ενεργές υπηρεσίες πελατών</h2><p>Αναθέστε πρώτα μια υπηρεσία (π.χ. «Maintenance») σε πελάτη από τις <a href="<?php echo NotifyUi::url('section=services&mode=new'); ?>">Υπηρεσίες πελατών</a>.</p></div>
<?php return; endif; ?>
<div class="sp-standalone-actions sp-offer-actions">
<?php if (!$sent): ?>
 <button type="button" class="btn btn-primary" onclick="document.getElementById('sp-do').value='save';spSubmit('notify.saveReport')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-do').value='test';spSubmit('notify.saveReport')">Αποθήκευση &amp; δοκιμή στο email μου</button>
 <button type="button" class="btn btn-outline-primary" onclick="if(confirm('Αποστολή της αναφοράς στον πελάτη;')){document.getElementById('sp-do').value='send';spSubmit('notify.saveReport')}">Αποθήκευση &amp; αποστολή στον πελάτη</button>
<?php endif; ?>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=reports'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=notify.saveReport')); ?>" method="post" id="adminForm">
 <div class="sp-card">
  <h2><?php echo $r ? 'Αναφορά maintenance' : 'Νέα αναφορά maintenance'; ?><?php if ($sent): ?> <span class="sp-offer-status is-accepted">Εστάλη <?php echo $e(Notify::datetimeGr($r->sent)); ?></span><?php endif; ?></h2>
  <div class="sp-grid">
   <label class="sp-wide">Υπηρεσία πελάτη *<?php echo NotifyUi::select('client_service_id', $options, $serviceId ?: '', '— Επιλογή —', 'required' . $dis); ?></label>
   <label>Περίοδος<input name="period" value="<?php echo $e($period); ?>"<?php echo $dis; ?>></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Σύνοψη εργασιών</span><textarea name="summary" rows="8"<?php echo $sent ? ' disabled' : ' data-sp-editor'; ?>><?php echo $e($r->summary ?? ''); ?></textarea></div>
   <label class="sp-wide">Ενημερώσεις (updates)<textarea name="items" rows="8" class="font-monospace" placeholder="Joomla | Core | 6.2.0 | 6.2.1&#10;Extension | Akeeba Backup | 10.1.0 | 10.1.2&#10;Template | YOOtheme Pro | 4.5.3 | 4.5.6"<?php echo $dis; ?>><?php echo $e($r->items ?? ''); ?></textarea></label>
  </div>
  <p class="sp-n-help">Μία ενημέρωση ανά γραμμή: <code>Τύπος | Όνομα | Προηγούμενη έκδοση | Νέα έκδοση</code>. Εμφανίζεται ως πίνακας στο email. Το κείμενο του email ορίζεται στις <a href="<?php echo NotifyUi::url('section=settings'); ?>">Ρυθμίσεις email</a>.</p>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) ($r->id ?? 0); ?>"><input type="hidden" name="do" id="sp-do" value="save"><input type="hidden" name="task" value="notify.saveReport">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($this->preview): ?>
<div class="sp-card"><h2>Προεπισκόπηση</h2>
<dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($this->preview['to']); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->preview['subject']); ?></dd></dl>
<?php echo NotifyUi::preview($this->preview['body']); ?></div>
<?php endif; ?>

<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$m = $this->selected;
?>
<div class="sp-standalone-actions sp-offer-actions"><a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=log'); ?>">← Ιστορικό</a>
<?php if (in_array($m->status, ['failed', 'cancelled'], true)): ?><?php echo NotifyUi::button('retry', ['id' => $m->id], 'Επανάληψη αποστολής', '', 'btn btn-outline-primary'); ?><?php endif; ?></div>
<div class="sp-card">
  <h2>Μήνυμα #<?php echo (int) $m->id; ?></h2>
  <dl class="sp-n-meta">
    <dt>Προς</dt><dd><?php echo $e($m->to_email); ?></dd>
    <dt>Θέμα</dt><dd><?php echo $e($m->subject); ?></dd>
    <dt>Τύπος</dt><dd><?php echo $e(Notify::KINDS[$m->kind] ?? $m->kind); ?></dd>
    <dt>Κατάσταση</dt><dd><?php echo NotifyUi::status($m->status, Notify::MESSAGE_STATUSES); ?> <?php echo $m->last_error ? '<small class="text-muted">' . $e($m->last_error) . '</small>' : ''; ?></dd>
    <dt>Προσπάθειες</dt><dd><?php echo (int) $m->attempts; ?></dd>
    <dt>Δημιουργία / Αποστολή</dt><dd><?php echo $e(Notify::datetimeGr($m->created)); ?> / <?php echo $e(Notify::datetimeGr($m->sent)); ?></dd>
  </dl>
  <?php echo NotifyUi::preview((string) $m->body); ?>
</div>

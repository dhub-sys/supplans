<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
$r = $this->selected;
$draft = $r->status === 'draft';
$dis = $draft ? '' : ' disabled';
// Per entry: sites that get it (checked).
$per = [];
$mails = 0;
foreach ($this->planRows as $row) {
    $any = false;
    foreach ($row['p']['u'] as $u) {
        $k = mb_strtolower($u['n']);
        $per[$k] = ($per[$k] ?? 0) + (!empty($u['on']) ? 1 : 0);
        $any = $any || !empty($u['on']);
    }
    $ok = $row['client'] && $row['client']->status === 'active';
    if ($any && !empty($row['p']['m']) && $ok) $mails++;
}
?>
<div class="sp-standalone-actions sp-offer-actions">
<?php if ($draft): ?>
 <button type="button" class="btn btn-primary" onclick="if(confirm('Καταγραφή των τσεκαρισμένων ενημερώσεων; Οι νέες εκδόσεις γράφονται στα sites και οι πελάτες θα τις λάβουν στη μηνιαία σύνοψη.')){document.getElementById('sp-do').value='log';spSubmit('sites.saveRound')}">Καταγραφή (για τη μηνιαία σύνοψη)</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-do').value='save';spSubmit('sites.saveRound')">Αποθήκευση επιλογών</button>
 <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-do').value='test';spSubmit('sites.saveRound')">Δοκιμή στο email μου</button>
 <details class="sp-su-more"><summary class="btn btn-outline-secondary">Άλλες επιλογές ▾</summary><div>
  <button type="button" class="btn btn-outline-primary" onclick="if(confirm('Καταγραφή και άμεση αποστολή μηνυμάτων στους πελάτες (εκτός μηνιαίας σύνοψης); Δεν αναιρείται.')){document.getElementById('sp-do').value='send';spSubmit('sites.saveRound')}">Καταγραφή &amp; αποστολή τώρα</button>
  <button type="button" class="btn btn-outline-secondary" onclick="if(confirm('Καταγραφή των εκδόσεων χωρίς να ενημερωθούν ποτέ οι πελάτες γι\' αυτές;')){document.getElementById('sp-do').value='apply';spSubmit('sites.saveRound')}">Καταγραφή χωρίς ενημέρωση πελατών</button>
 </div></details>
 <?php echo NotifyUi::button('sites.deleteRound', ['id' => $r->id], 'Διαγραφή', 'Διαγραφή του πρόχειρου;', 'btn btn-outline-danger'); ?>
<?php else: ?>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=log&kind=siteupdate'); ?>">Μηνύματα</a>
<?php endif; ?>
 <a class="btn btn-outline-secondary" href="<?php echo NotifyUi::url('section=rounds'); ?>">Κλείσιμο</a>
</div>
<form action="<?php echo $e(OfferHelper::url('task=sites.saveRound', false)); ?>" method="post" id="adminForm">
<div class="sp-card">
  <div class="sp-title-row"><h2><?php echo $e($r->title); ?></h2><?php echo NotifyUi::status($r->status === 'applied' ? 'active' : $r->status, Sites::ROUND_STATUSES + ['active' => 'Χωρίς ενημέρωση πελάτη']); ?></div>
  <?php if ($draft): ?><label class="sp-su-title">Τίτλος<input name="title" value="<?php echo $e($r->title); ?>"></label><?php endif; ?>
  <div class="sp-su-summary">
   <?php foreach ($r->entriesList as $x): ?>
   <div><strong><?php echo $e($x['n']); ?></strong><span><?php echo $e(($x['o'] !== '' ? $x['o'] : 'παλαιότερες') . ' → ' . $x['v']); ?></span><b><?php echo (int) ($per[mb_strtolower($x['n'])] ?? 0); ?> sites</b></div>
   <?php endforeach; ?>
  </div>
  <p class="sp-n-help"><?php if ($draft): ?><b><?php echo count($this->planRows); ?></b> sites · <b><?php echo $mails; ?></b> πελάτες θα ενημερωθούν. Ξετσεκάρετε ενημερώσεις που δεν έγιναν σε κάποιο site, ή το «Email» για όσους δεν πρέπει να ενημερωθούν. Με «Καταγραφή» οι νέες εκδόσεις γράφονται στα sites και στο ιστορικό τους, και οι πελάτες τις λαμβάνουν συγκεντρωτικά στη <a href="<?php echo NotifyUi::url('section=sitedigest'); ?>">μηνιαία σύνοψη</a><?php echo \Support\Component\Supportplans\Site\Helper\Sites::digestDay() ? ' (αυτόματα την ' . \Support\Component\Supportplans\Site\Helper\Sites::digestDay() . 'η κάθε μήνα)' : ''; ?>.<?php else: ?>Καταγράφηκε <?php echo $e(Notify::datetimeGr($r->applied)); ?> σε <b><?php echo (int) $r->sites; ?></b> sites<?php echo (int) $r->messages ? ' · <b>' . (int) $r->messages . '</b> μηνύματα άμεσα' : ''; ?>.<?php endif; ?></p>
</div>
<div class="sp-card">
  <?php if ($draft): ?><div class="sp-su-bulk"><button type="button" class="btn btn-sm btn-outline-secondary" data-all="1">Όλα</button><button type="button" class="btn btn-sm btn-outline-secondary" data-all="0">Κανένα email</button></div><?php endif; ?>
  <div class="table-responsive"><table class="table sp-offer-table sp-su-plan">
   <thead><tr><th>Site</th><th>Πελάτης</th><th>Ενημερώσεις</th><th class="sp-center">Email</th><th></th></tr></thead>
   <tbody>
   <?php foreach ($this->planRows as $row): $i = $row['i']; $site = $row['site']; $c = $row['client']; $ok = $c && $c->status === 'active'; ?>
    <tr<?php echo ($this->sitePreview['i'] ?? -1) === $i ? ' class="is-previewed"' : ''; ?>>
     <td><a href="<?php echo NotifyUi::url('section=sites&id=' . (int) $site->id); ?>"><strong><?php echo $e($site->domain); ?></strong></a><br><small class="text-muted"><?php echo $e(Sites::CMS[$site->cms] ?? ''); ?></small></td>
     <td><?php if ($c): ?><?php echo $e(Notify::clientLabel($c)); ?><br><small class="text-muted"><?php echo $e($c->email); ?></small><?php if (!$ok): ?><br><span class="sp-offer-status is-rejected">Ανενεργός</span><?php endif; ?><?php else: ?><span class="sp-offer-status is-rejected">Χωρίς πελάτη</span><?php endif; ?></td>
     <td class="sp-su-ups"><?php foreach ($row['p']['u'] as $j => $u): ?><label class="sp-check"><input type="checkbox" name="on[<?php echo $i; ?>][<?php echo $j; ?>]" value="1"<?php echo !empty($u['on']) ? ' checked' : ''; ?><?php echo $dis; ?>> <span><b><?php echo $e($u['n']); ?></b> <?php echo $e(($u['o'] !== '' ? $u['o'] : '—') . ' → ' . $u['v']); ?></span></label><?php endforeach; ?></td>
     <td class="sp-center"><input type="checkbox" class="sp-su-mail" name="mail[<?php echo $i; ?>]" value="1"<?php echo !empty($row['p']['m']) && $ok ? ' checked' : ''; ?><?php echo $ok ? $dis : ' disabled'; ?> aria-label="Email"></td>
     <td class="sp-num"><?php if ($draft): ?><button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('sp-do').value='preview_<?php echo $i; ?>';spSubmit('sites.saveRound')">Προεπισκόπηση</button><?php else: ?><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=rounds&id=' . (int) $r->id . '&preview=' . $i); ?>#sp-su-preview">Μήνυμα</a><?php endif; ?></td>
    </tr>
    <?php if (($this->sitePreview['i'] ?? -1) === $i): ?>
    <tr class="sp-su-preview-row"><td colspan="5" id="sp-su-preview"><dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($this->sitePreview['to'] ?: '—'); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->sitePreview['subject']); ?></dd></dl><?php echo NotifyUi::preview($this->sitePreview['body']); ?></td></tr>
    <?php endif; ?>
   <?php endforeach; ?>
   <?php if (!$this->planRows): ?><tr><td colspan="5" class="text-muted">Κανένα site.</td></tr><?php endif; ?>
   </tbody>
  </table></div>
</div>
<input type="hidden" name="id" value="<?php echo (int) $r->id; ?>"><input type="hidden" name="do" id="sp-do" value="save"><input type="hidden" name="task" value="sites.saveRound">
<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($draft): ?>
<script>document.addEventListener('click',function(ev){var b=ev.target.closest('.sp-su-bulk [data-all]');if(!b)return;document.querySelectorAll('.sp-su-mail:not([disabled])').forEach(function(c){c.checked=b.getAttribute('data-all')==='1';});});</script>
<?php endif; ?>

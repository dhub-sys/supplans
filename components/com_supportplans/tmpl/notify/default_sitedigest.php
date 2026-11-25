<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
$when = Sites::scheduleLabel();
$total = 0;
foreach ($this->rows as $c) foreach ($c['groups'] as $g) $total += count($g['updates']);
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Σύνοψη πελατών</h1><p>Οι ενημερώσεις που έχουν καταγραφεί και δεν έχουν σταλεί ακόμα στους πελάτες. Κάθε πελάτης λαμβάνει <b>ένα μήνυμα</b> με όλα τα sites του. Κάθε update εμφανίζεται ξεχωριστά με την ημερομηνία του, ώστε να φαίνεται όλη η δουλειά του μήνα.</p></div>
  <a class="sp-primary-action is-secondary" href="<?php echo NotifyUi::url('section=sitesettings'); ?>">✎ Κείμενο &amp; συχνότητα</a>
</section>
<div class="sp-su-info"><?php if ($when !== ''): ?>Αυτόματη αποστολή: <b><?php echo $e($when); ?><?php echo Notify::sendTimeLabel() !== '' ? ', ' . $e(Notify::sendTimeLabel()) : ''; ?></b>, με όσα καταγράφηκαν από την προηγούμενη σύνοψη. Μπορείτε να στείλετε και τώρα, χειροκίνητα, για όσους επιλέξετε.<?php else: ?>Η αυτόματη αποστολή είναι <b>απενεργοποιημένη</b> (Ρυθμίσεις → Συχνότητα). Στείλτε χειροκίνητα από εδώ.<?php endif; ?></div>
<form method="post" action="<?php echo $e(OfferHelper::url('task=sites.digest', false)); ?>" id="sp-sd-form">
<div class="sp-card">
  <div class="sp-title-row"><h2><?php echo count(array_filter(array_keys($this->rows))); ?> πελάτες · <?php echo $total; ?> ενημερώσεις σε αναμονή</h2>
   <?php if ($this->rows): ?><div class="sp-n-actions">
    <button class="btn btn-primary" name="do" value="send" onclick="return confirm('Αποστολή της σύνοψης στους επιλεγμένους πελάτες τώρα;')">Αποστολή σύνοψης τώρα</button>
    <button class="btn btn-outline-secondary" name="do" value="skip" onclick="return confirm('Οι επιλεγμένοι πελάτες δεν θα ενημερωθούν για αυτές τις ενημερώσεις. Συνέχεια;')">Εξαίρεση από τη σύνοψη</button>
   </div><?php endif; ?></div>
  <div class="table-responsive"><table class="table sp-offer-table sp-su-plan">
   <thead><tr><th><input type="checkbox" class="sp-sd-all" checked aria-label="Όλοι"></th><th>Πελάτης</th><th>Sites &amp; ενημερώσεις</th><th>Περίοδος</th><th></th></tr></thead>
   <tbody>
   <?php foreach ($this->rows as $cid => $c): $ok = $cid && $c['client'] && $c['client']->status === 'active'; ?>
    <tr<?php echo ($this->sitePreview['i'] ?? 0) === $cid && $cid ? ' class="is-previewed"' : ''; ?>>
     <td><?php if ($ok): ?><input type="checkbox" class="sp-sd-one" name="clients[]" value="<?php echo (int) $cid; ?>" checked aria-label="Επιλογή"><?php endif; ?></td>
     <td><?php if ($c['client']): ?><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $cid); ?>"><strong><?php echo $e(Notify::clientLabel($c['client'])); ?></strong></a><br><small class="text-muted"><?php echo $e($c['client']->email); ?></small><?php if (!$ok): ?><br><span class="sp-offer-status is-rejected">Ανενεργός</span><?php endif; ?><?php else: ?><span class="sp-offer-status is-rejected">Sites χωρίς πελάτη</span><?php endif; ?></td>
     <td class="sp-sd-sites"><?php foreach ($c['groups'] as $g): ?><div><a href="<?php echo NotifyUi::url('section=sites&id=' . (int) $g['site']->id); ?>"><b><?php echo $e($g['site']->domain); ?></b></a>
       <?php foreach ($g['updates'] as $u): ?><span class="sp-sd-up"><small><?php echo $e(substr(Notify::dateGr($u['d']), 0, 5)); ?></small> <?php echo $e($u['n'] . ' ' . ($u['o'] !== '' ? $u['o'] . ' → ' : '') . $u['v']); ?></span><?php endforeach; ?></div><?php endforeach; ?></td>
     <td><?php echo $e(Sites::periodLabel($c['from'], $c['to'])); ?></td>
     <td class="sp-num"><?php if ($c['client']): ?><a class="sp-doc-btn" href="<?php echo NotifyUi::url('section=sitedigest&preview=' . (int) $cid); ?>#sp-su-preview">Προεπισκόπηση</a><?php endif; ?></td>
    </tr>
    <?php if (($this->sitePreview['i'] ?? 0) === $cid && $cid): ?>
    <tr class="sp-su-preview-row"><td colspan="5" id="sp-su-preview"><dl class="sp-n-meta"><dt>Προς</dt><dd><?php echo $e($this->sitePreview['to']); ?></dd><dt>Θέμα</dt><dd><?php echo $e($this->sitePreview['subject']); ?></dd></dl>
     <?php echo NotifyUi::preview($this->sitePreview['body']); ?></td></tr>
    <?php endif; ?>
   <?php endforeach; ?>
   <?php if (!$this->rows): ?><tr><td colspan="5" class="text-muted">Δεν υπάρχουν ενημερώσεις σε αναμονή. Καταγράψτε τις ενημερώσεις από το «Νέες ενημερώσεις».</td></tr><?php endif; ?>
   </tbody>
  </table></div>
</div>
<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php if ($this->sitePreview): ?>
<form method="post" action="<?php echo $e(OfferHelper::url('task=sites.digestTest', false)); ?>" class="sp-sd-test"><input type="hidden" name="client" value="<?php echo (int) $this->sitePreview['i']; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-secondary">Δοκιμή αυτού του μηνύματος στο email μου</button></form>
<?php endif; ?>
<script>document.addEventListener('change',function(ev){if(ev.target.matches('.sp-sd-all'))document.querySelectorAll('.sp-sd-one').forEach(function(c){c.checked=ev.target.checked;});});</script>

<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;

$e = [NotifyUi::class, 'e'];
$today = Notify::today();
$soon = Notify::addDays($today, 30);
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Ευχές &amp; Αποστολές</span><h1>Ονομαστικές εορτές</h1><p>Οι πελάτες με ονομαστική εορτή, με σειρά επόμενης γιορτής. Την ημέρα της γιορτής φεύγει αυτόματα το μήνυμα ευχών<?php echo Notify::sendTimeLabel() !== '' ? ' στις ' . $e(Notify::sendTimeLabel()) : ''; ?>. Η εορτή ορίζεται στην καρτέλα κάθε πελάτη (Ανανεώσεις → Πελάτες).</p></div>
  <a class="sp-primary-action is-secondary" href="<?php echo NotifyUi::url('section=greetsettings#sp-nameday-template'); ?>">✎ Μήνυμα ευχών</a>
</section>
<?php if (!(int) Notify::setting('notify_nameday_enabled')): ?><div class="sp-n-warning"><strong>Η αυτόματη αποστολή ευχών είναι απενεργοποιημένη</strong> (Ρυθμίσεις ευχών).</div><?php endif; ?>
<div class="sp-card sp-n-namedays">
  <?php if (!$this->namedays): ?><p class="text-muted">Δεν έχει οριστεί ονομαστική εορτή σε κανέναν πελάτη. Ανοίξτε έναν πελάτη στις Ανανεώσεις → Πελάτες και συμπληρώστε το πεδίο «Ονομαστική εορτή».</p>
  <?php else: ?>
  <div class="table-responsive"><table class="table sp-offer-table">
   <thead><tr><th>Επόμενη γιορτή</th><th>Πελάτης</th><th>Εορτή</th><th>Email</th><th>Ευχές</th><th></th></tr></thead>
   <tbody><?php foreach ($this->namedays as $row): $c = $row['client']; $d = $row['date']; ?>
    <tr<?php echo $d <= $soon ? ' class="is-soon"' : ''; ?>><td><strong><?php echo $e($d === $today ? 'Σήμερα' : Notify::dateGr($d)); ?></strong><?php if ($d !== $today && $d <= $soon): ?> <small class="text-muted">σε <?php echo (int) Notify::daysUntil($d); ?> ημ.</small><?php endif; ?></td>
     <td><a href="<?php echo NotifyUi::url('section=clients&id=' . (int) $c->id); ?>"><?php echo $e(Notify::clientLabel($c)); ?></a></td>
     <td><?php echo $e(Notify::namedayLabel((string) $c->nameday)); ?></td>
     <td><?php echo $e($c->email); ?></td>
     <td><?php echo $c->unsubscribed_at ? '<span class="sp-offer-status is-rejected">Διαγράφηκε από ενημερώσεις</span>' : ((int) Notify::setting('notify_nameday_enabled') ? '<span class="sp-offer-status is-accepted">Αυτόματα</span>' : '<span class="sp-offer-status">Ανενεργές</span>'); ?></td>
     <td class="sp-num"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=greetsettings&preview=nameday&client=' . (int) $c->id); ?>#sp-nameday-template">Προεπισκόπηση</a></td></tr>
   <?php endforeach; ?></tbody>
  </table></div>
  <?php endif; ?>
</div>

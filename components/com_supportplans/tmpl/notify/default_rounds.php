<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Ιστορικό ενημερώσεων</h1><p>Κάθε μαζική ενημέρωση ανά ημερομηνία: τι ενημερώθηκε και σε πόσα sites. Οι πελάτες ενημερώνονται συγκεντρωτικά με τη μηνιαία σύνοψη.</p></div>
  <a class="sp-primary-action" href="<?php echo NotifyUi::url('section=siteupdates'); ?>">↻ Νέες ενημερώσεις</a>
</section>
<div class="sp-card">
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Τίτλος</th><th>Ενημερώσεις</th><th>Κατάσταση</th><th class="sp-center">Sites</th><th class="sp-center">Άμεσα μηνύματα</th><th>Ημερομηνία</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): $entries = json_decode((string) $r->entries, true) ?: []; ?>
      <tr>
        <td><a href="<?php echo NotifyUi::url('section=rounds&id=' . (int) $r->id); ?>"><strong><?php echo $e($r->title); ?></strong></a></td>
        <td><small><?php echo $e(implode(', ', array_map(static fn($x) => $x['n'] . ' ' . $x['v'], array_slice($entries, 0, 4))) . (count($entries) > 4 ? ' +' . (count($entries) - 4) : '')); ?></small></td>
        <td><?php echo NotifyUi::status(['applied' => 'suspended', 'logged' => 'active'][$r->status] ?? $r->status, Sites::ROUND_STATUSES + ['active' => 'Καταγράφηκε', 'suspended' => 'Χωρίς ενημέρωση πελάτη', 'applying' => 'Σε εξέλιξη']); ?></td>
        <td class="sp-center"><?php echo (int) $r->sites; ?></td>
        <td class="sp-center"><?php echo (int) $r->messages; ?></td>
        <td><?php echo $e(Notify::datetimeGr($r->applied ?: $r->created)); ?></td>
        <td class="sp-num sp-row-actions"><a class="btn btn-sm btn-outline-primary" href="<?php echo NotifyUi::url('section=rounds&id=' . (int) $r->id); ?>"><?php echo $r->status === 'draft' ? 'Συνέχεια' : 'Προβολή'; ?></a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="7" class="text-muted">Δεν υπάρχουν ενημερώσεις ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

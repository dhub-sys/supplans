<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Structure;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Έντυπα</span><h1>Προτάσεις δομής</h1><p>Σχεδιάστε το sitemap μιας ιστοσελίδας σε μορφή δέντρου, με ενότητες και υποενότητες, γράψτε προτάσεις περιεχομένου για κάθε ενότητα και αποθηκεύστε την πρόταση σε Pdf.</p></div>
  <a class="sp-primary-action" href="<?php echo OfferHelper::url('view=structures&mode=new'); ?>">＋ Νέα πρόταση δομής</a>
</section>
<div class="sp-card">
  <h2>Προτάσεις δομής</h2>
  <div class="table-responsive"><table class="table sp-offer-table">
    <thead><tr><th>Αρ.</th><th>Ημ/νία</th><th>Πελάτης</th><th>Έργο</th><th class="sp-num">Ενότητες</th><th class="sp-num">Σελίδες</th><th>Κατάσταση</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): $c = Structure::counts(Structure::nodes($r->tree)); ?>
      <tr>
        <td><strong><?php echo $e($r->number); ?></strong></td>
        <td><?php echo $e(OfferHelper::dateGr($r->proposal_date)); ?></td>
        <td><?php echo $e($r->customer_name); ?></td>
        <td><?php echo $e($r->project_name ?: '—'); ?></td>
        <td class="sp-num"><?php echo $c['sections']; ?></td>
        <td class="sp-num"><?php echo $c['pages']; ?></td>
        <td><span class="sp-offer-status is-<?php echo $e($r->status); ?>"><?php echo $e(OfferHelper::statusLabel($r->status)); ?></span></td>
        <td class="sp-num"><a class="btn btn-sm btn-outline-primary" href="<?php echo OfferHelper::url('view=structures&id=' . (int) $r->id); ?>">Άνοιγμα</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="8" class="text-muted">Δεν υπάρχουν προτάσεις δομής ακόμα.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

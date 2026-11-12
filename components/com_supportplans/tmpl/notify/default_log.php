<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = [NotifyUi::class, 'e'];
$pages = (int) ceil($this->total / 50);
$base = 'section=log' . ($this->filters['kind'] ? '&kind=' . $this->filters['kind'] : '') . ($this->filters['status'] ? '&status=' . $this->filters['status'] : '') . ($this->filters['campaign_id'] ? '&campaign_id=' . (int) $this->filters['campaign_id'] : '');
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Ανανεώσεις</span><h1>Ιστορικό αποστολών</h1><p>Κάθε μήνυμα με το ακριβές περιεχόμενο που στάλθηκε. Τα αποτυχημένα ξαναδοκιμάζονται αυτόματα έως <?php echo Notify::MAX_ATTEMPTS; ?> φορές.</p></div>
  <?php echo NotifyUi::button('runNow', ['back' => 'section=log'], 'Αποστολή ουράς τώρα', '', 'sp-primary-action'); ?>
</section>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(NotifyUi::url('', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="notify"><input type="hidden" name="section" value="log">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo OfferHelper::itemid(); ?>">
    <?php if ($this->filters['campaign_id']): ?><input type="hidden" name="campaign_id" value="<?php echo (int) $this->filters['campaign_id']; ?>"><?php endif; ?>
    <?php echo NotifyUi::select('kind', Notify::KINDS, $this->filters['kind'], 'Όλοι οι τύποι'); ?>
    <?php echo NotifyUi::select('status', Notify::MESSAGE_STATUSES, $this->filters['status'], 'Όλες οι καταστάσεις'); ?>
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
    <span class="text-muted">Σε αναμονή: <?php echo (int) $this->counts['queued']; ?> · Απέτυχαν: <?php echo (int) $this->counts['failed']; ?></span>
  </form>
  <?php echo $this->loadTemplate('logtable'); ?>
  <?php if ($pages > 1): ?><div class="sp-n-pages"><?php for ($p = 1; $p <= $pages; $p++): ?><?php echo $p === $this->page ? '<span>' . $p . '</span>' : '<a href="' . NotifyUi::url($base . '&p=' . $p) . '">' . $p . '</a>'; ?><?php endfor; ?></div><?php endif; ?>
</div>

<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\Assets;
use Support\Component\Supportplans\Site\Helper\Documents;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sidebar;

echo Assets::css('supportplans.css', 'offers.css', 'notify.css', 'structure-editor.css');
$spItemid = OfferHelper::itemid();
$spCompany = trim((string) $this->params->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Dashboard');
$spLogoUrl = OfferHelper::assetUrl((string) $this->params->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= OfferHelper::upperGr(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$total = array_sum($this->counts);
$filterUrl = static fn(string $type) => OfferHelper::url('view=documents' . ($type !== '' ? '&type=' . $type : ''));
?>
<div class="sp-saas-root">
<header class="sp-appbar">
 <div class="sp-brand">
  <?php if ($spLogoUrl !== ''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo $e($spLogoUrl); ?>" alt="<?php echo $e($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo $e($spInitials ?: 'SP'); ?></span><?php endif; ?>
  <div class="sp-brand-copy"><strong><?php echo $e($spCompany); ?></strong><small>Dashboard</small></div>
 </div>
 <div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div>
</header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout"><aside class="sp-sidebar">
  <?php echo Sidebar::head(); ?>
  <?php echo Sidebar::nav(['plan' => 'plans', 'offer' => 'offers', 'structure' => 'structures'][$this->type] ?? 'documents'); ?>
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::bottom(); ?>
</aside>
<section class="sp-workspace sp-offers-workspace">
<?php
$titles = ['' => 'Έγγραφα', 'plan' => 'Πακέτα', 'offer' => 'Προσφορές', 'structure' => 'Προτάσεις δομής'];
$intro = [
    '' => 'Πακέτα, προσφορές και προτάσεις δομής με κοινή αρίθμηση. Κάθε νέο έγγραφο παίρνει τον επόμενο αριθμό μετά τον τελευταίο (<b>' . $e($this->nextNumber) . '</b>), που μπορείτε να αλλάξετε πριν την αποθήκευση.',
    'plan' => 'Πακέτα υποστήριξης πελατών, κατά αριθμό εγγράφου (το νεότερο πρώτο). Νέος αριθμός: <b>' . $e($this->nextNumber) . '</b>.',
    'offer' => 'Οικονομικές προσφορές, κατά αριθμό εγγράφου (η νεότερη πρώτη). Νέος αριθμός: <b>' . $e($this->nextNumber) . '</b>.',
    'structure' => 'Προτάσεις δομής ιστοσελίδων (sitemap και περιεχόμενο), κατά αριθμό εγγράφου. Νέος αριθμός: <b>' . $e($this->nextNumber) . '</b>.',
][$this->type];
$newBtns = ['offer' => ['view=offers&mode=new', '＋ Προσφορά'], 'structure' => ['view=structures&mode=new', '＋ Πρόταση δομής'], 'plan' => ['view=plans&mode=new', '＋ Πακέτο']];
if ($this->type !== '') $newBtns = [$this->type => $newBtns[$this->type]] + $newBtns;
?>
<?php if ($this->type === 'plan'): ?>
<?php $spPlanTabs = ['view=documents&type=plan' => 'Πακέτα', 'view=serviceplans' => 'Πλάνα']; ?>
<nav class="sp-offer-tabs" aria-label="Πακέτα"><?php foreach ($spPlanTabs as $tq => $tl): ?><a class="sp-offer-tab<?php echo $tl === 'Πακέτα' ? ' is-active' : ''; ?>" href="<?php echo \Support\Component\Supportplans\Site\Helper\OfferHelper::url($tq); ?>"><?php echo $tl; ?></a><?php endforeach; ?></nav>
<?php endif; ?>
<?php if ($this->type === 'offer'): ?>
<nav class="sp-offer-tabs" aria-label="Προσφορές">
 <?php foreach (['' => 'Προσφορές', 'templates' => 'Templates', 'services' => 'Υπηρεσίες / Περιγραφές', 'variables' => 'Μεταβλητές'] as $sk => $sl): ?>
 <a class="sp-offer-tab<?php echo $sk === '' ? ' is-active' : ''; ?>" href="<?php echo OfferHelper::url($sk === '' ? 'view=documents&type=offer' : 'view=offers&section=' . $sk); ?>"><?php echo $e($sl); ?></a>
 <?php endforeach; ?>
</nav>
<?php endif; ?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Έγγραφα</span><h1><?php echo $e($titles[$this->type]); ?> <?php echo \Support\Component\Supportplans\Site\Helper\Year::get(); ?></h1><p><?php echo $intro; ?></p></div>
  <div class="sp-doc-new">
   <?php $first = true; foreach ($newBtns as [$nu, $nl]): ?>
   <a class="<?php echo $first ? 'sp-primary-action' : 'sp-doc-btn is-lg'; ?>" href="<?php echo OfferHelper::url($nu); ?>"><?php echo $e($nl); ?></a>
   <?php $first = false; endforeach; ?>
  </div>
</section>
<div class="sp-card">
  <nav class="sp-doc-tabs" aria-label="Τύπος εγγράφου">
   <a class="<?php echo $this->type === '' ? 'is-active' : ''; ?>" href="<?php echo $filterUrl(''); ?>">Όλα <span><?php echo $total; ?></span></a>
   <?php foreach (Documents::TYPES as $k => $label): ?>
   <a class="<?php echo $this->type === $k ? 'is-active' : ''; ?>" href="<?php echo $filterUrl($k); ?>"><?php echo $e($label === 'Πακέτο' ? 'Πακέτα' : ($label === 'Προσφορά' ? 'Προσφορές' : 'Προτάσεις δομής')); ?> <span><?php echo (int) $this->counts[$k]; ?></span></a>
   <?php endforeach; ?>
   <form class="sp-doc-search" method="get" action="<?php echo $e(OfferHelper::url('view=documents', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="documents">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo (int) $spItemid; ?>">
    <?php if ($this->type !== ''): ?><input type="hidden" name="type" value="<?php echo $e($this->type); ?>"><?php endif; ?>
    <input type="search" name="q" value="<?php echo $e($this->q); ?>" placeholder="Αριθμός, πελάτης, έργο…" aria-label="Αναζήτηση">
   </form>
  </nav>
  <div class="table-responsive"><table class="table sp-offer-table sp-doc-table">
    <thead><tr><th>Αρ.</th><th>Είδος</th><th>Ημ/νία</th><th>Πελάτης</th><th>Έργο / Περιγραφή</th><th>Κατάσταση</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): ?>
      <tr>
        <td><strong><?php echo $e($r['number'] !== '' ? $r['number'] : '—'); ?></strong></td>
        <td><span class="sp-doc-type is-<?php echo $e($r['type']); ?>"><?php echo $e(Documents::TYPES[$r['type']]); ?></span></td>
        <td class="sp-n-nowrap"><?php echo $e(OfferHelper::dateGr($r['date'])); ?></td>
        <td><?php echo $e($r['customer']); ?></td>
        <td><?php echo $e($r['title'] !== '' ? $r['title'] : '—'); ?></td>
        <td><span class="sp-offer-status is-<?php echo $e($r['status']); ?>"><?php echo $e($r['statusLabel']); ?></span></td>
        <td class="sp-num sp-n-nowrap">
          <a class="sp-doc-btn" href="<?php echo OfferHelper::url($r['pdf']); ?>" target="_blank" rel="noopener" title="Άνοιγμα του εγγράφου για εκτύπωση / Pdf">Pdf</a>
          <a class="sp-doc-btn" href="<?php echo OfferHelper::url($r['open'] . '&send=1'); ?>" title="Αποστολή με email">✉ Email</a>
          <a class="sp-doc-btn is-primary" href="<?php echo OfferHelper::url($r['open']); ?>">Άνοιγμα</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="7" class="text-muted"><?php echo $this->q !== '' || $this->type !== '' ? 'Δεν βρέθηκαν έγγραφα με αυτά τα κριτήρια.' : 'Δεν υπάρχουν έγγραφα για το ' . \Support\Component\Supportplans\Site\Helper\Year::get() . '.'; ?></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
</section></div></div></main>
</div>

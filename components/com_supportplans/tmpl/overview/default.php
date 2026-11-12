<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\Assets;
use Support\Component\Supportplans\Site\Helper\Documents;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sidebar;
use Support\Component\Supportplans\Site\Helper\Year;

echo Assets::css('supportplans.css', 'offers.css', 'notify.css', 'structure-editor.css');
$spItemid = OfferHelper::itemid();
$spCompany = trim((string) $this->params->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Dashboard');
$spLogoUrl = OfferHelper::assetUrl((string) $this->params->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= OfferHelper::upperGr(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$m = static fn($v) => number_format((float) $v, 2, ',', '.') . ' €';
$sm = $this->summary;
$dr = static fn($a, $b) => Notify::dateGr($a) . ($a !== $b ? ' – ' . Notify::dateGr($b) : '');
$current = Year::isCurrent();
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
  <?php echo Sidebar::nav('overview'); ?>
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::bottom(); ?>
</aside>
<section class="sp-workspace sp-offers-workspace">
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Επισκόπηση <?php echo (int) $this->year; ?></span><h1><?php echo $e($spCompany); ?> Dashboard</h1><p><?php echo $current ? 'Η εικόνα της χρονιάς: έγγραφα, προσφορές, ανανεώσεις υπηρεσιών και ό,τι λήγει τις επόμενες ημέρες.' : 'Αρχείο ' . (int) $this->year . ': τα έγγραφα και τα στοιχεία εκείνης της χρονιάς.'; ?></p></div>
  <?php if ($current): ?>
  <div class="sp-doc-new">
   <a class="sp-primary-action" href="<?php echo OfferHelper::url('view=offers&mode=new'); ?>">＋ Προσφορά</a>
   <a class="sp-doc-btn is-lg" href="<?php echo OfferHelper::url('view=structures&mode=new'); ?>">＋ Πρόταση δομής</a>
   <a class="sp-doc-btn is-lg" href="<?php echo OfferHelper::url('view=plans&mode=new'); ?>">＋ Πακέτο</a>
  </div>
  <?php endif; ?>
</section>
<section class="sp-kpis sp-ov-kpis">
  <article><span>Έγγραφα <?php echo (int) $this->year; ?></span><strong><?php echo count($this->docs); ?></strong><small><?php echo (int) $this->counts['offer']; ?> προσφορές · <?php echo (int) $this->counts['structure']; ?> προτάσεις δομής · <?php echo (int) $this->counts['plan']; ?> πακέτα</small></article>
  <article><span>Αποδεκτές προσφορές</span><strong><?php echo $m($this->offers['accepted']); ?></strong><small><?php echo (int) $this->offers['acceptedCount']; ?> προσφορές · προ ΦΠΑ</small></article>
  <article><span>Προσφορές σε εκκρεμότητα</span><strong><?php echo (int) $this->offers['pending']; ?></strong><small>αξία <?php echo $e($m($this->offers['pendingValue'])); ?> προ ΦΠΑ</small></article>
  <article><span>Ενεργοί πελάτες / πακέτα</span><strong><?php echo (int) $this->clients; ?> / <?php echo (int) $this->activePlans; ?></strong><small>ενεργοί πελάτες · ενεργά πακέτα</small></article>
</section>
<div class="sp-card sp-n-summary">
  <div class="sp-title-row"><h2>Ανανεώσεις υπηρεσιών <?php echo $current ? '· τρέχων μήνας' : (int) $this->year; ?></h2><a class="sp-doc-btn" href="<?php echo OfferHelper::url('view=notify&section=services'); ?>">Αναλυτικά</a></div>
  <div class="sp-n-sumgrid">
    <div class="sp-n-sum is-paid"><span>Πληρώθηκαν</span><strong><?php echo $m($sm['paid']['gross']); ?></strong><small><?php echo (int) $sm['paid']['count']; ?> πληρωμές · <?php echo $e($dr($sm['paid']['from'], $sm['paid']['to'])); ?> · με ΦΠΑ</small></div>
    <?php if ($current): ?>
    <div class="sp-n-sum is-due"><span>Προς πληρωμή</span><strong><?php echo $m($sm['due']['gross']); ?></strong><small><?php echo (int) $sm['due']['count']; ?> υπηρεσίες λήγουν <?php echo $e($dr($sm['due']['from'], $sm['due']['to'])); ?> · με ΦΠΑ</small></div>
    <div class="sp-n-sum is-overdue"><span>Ληγμένες, απλήρωτες</span><strong><?php echo $m($sm['overdue']['gross']); ?></strong><small><?php echo (int) $sm['overdue']['count']; ?> υπηρεσίες · με ΦΠΑ</small></div>
    <?php endif; ?>
  </div>
</div>
<div class="sp-card sp-ov-export" id="sp-ov-export">
  <div class="sp-title-row"><div><h2>Αναφορά ανανεώσεων</h2><p class="sp-foot">Πληρωμές, λήξεις και ληγμένες υπηρεσίες για την περίοδο που θα επιλέξετε, σε Excel ή σε Pdf για εκτύπωση.</p></div></div>
  <form method="get" action="<?php echo $e(OfferHelper::url('', false)); ?>" class="sp-ov-export-form">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo (int) $spItemid; ?>">
    <label>Περίοδος<select name="sp" onchange="this.form.querySelector('.sp-ov-dates').hidden=this.value!=='custom'">
      <?php foreach (Notify::SUMMARY_PERIODS as $pk => $pl): if ($pk === 'year') $pl = $current ? 'Τρέχον έτος' : 'Όλο το ' . (int) $this->year; ?><option value="<?php echo $pk; ?>"<?php echo $pk === ($current ? 'month' : 'year') ? ' selected' : ''; ?>><?php echo $e($pl); ?></option><?php endforeach; ?>
    </select></label>
    <span class="sp-ov-dates" hidden><label>Από<input type="date" name="sf"></label><label>Έως<input type="date" name="st"></label></span>
    <fieldset><legend>Περιεχόμενο</legend>
      <label class="sp-check"><input type="checkbox" name="parts[]" value="paid" checked> Πληρωμές</label>
      <label class="sp-check"><input type="checkbox" name="parts[]" value="due" checked> Λήξεις (προς πληρωμή)</label>
      <label class="sp-check"><input type="checkbox" name="parts[]" value="overdue" checked> Ληγμένες απλήρωτες</label>
    </fieldset>
    <div class="sp-ov-export-btns">
      <button class="btn btn-outline-primary" name="task" value="overview.export">⭳ Excel</button>
      <button class="btn btn-primary" name="view" value="renewalsreport" formtarget="_blank">Pdf / εκτύπωση</button>
    </div>
  </form>
  <p class="sp-foot">«30 / 60 / 90 ημέρες»: οι πληρωμές των τελευταίων ημερών και οι λήξεις των επόμενων. Με δικές σας ημερομηνίες: πληρωμές και λήξεις μέσα στο διάστημα.</p>
</div>
<div class="sp-ov-cols">
  <div class="sp-card">
    <div class="sp-title-row"><h2>Πρόσφατα έγγραφα</h2><a class="sp-doc-btn" href="<?php echo OfferHelper::url('view=documents'); ?>">Όλα</a></div>
    <?php if ($this->docs): ?>
    <table class="table sp-offer-table sp-doc-table"><tbody>
    <?php foreach (array_slice($this->docs, 0, 8) as $r): ?>
      <tr><td><strong><?php echo $e($r['number'] !== '' ? $r['number'] : '—'); ?></strong></td><td><span class="sp-doc-type is-<?php echo $e($r['type']); ?>"><?php echo $e(Documents::TYPES[$r['type']]); ?></span></td>
      <td><?php echo $e($r['customer']); ?></td><td class="sp-num"><a class="sp-doc-btn" href="<?php echo OfferHelper::url($r['open']); ?>">Άνοιγμα</a></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="sp-foot">Δεν υπάρχουν έγγραφα για το <?php echo (int) $this->year; ?>.</p><?php endif; ?>
  </div>
  <?php if ($current): ?>
  <div class="sp-card">
    <div class="sp-title-row"><h2>Λήγουν τις επόμενες 30 ημέρες</h2><a class="sp-doc-btn" href="<?php echo OfferHelper::url('view=notify&section=services&expiring=30'); ?>">Όλες</a></div>
    <?php if ($this->expiring): ?>
    <table class="table sp-offer-table"><tbody>
    <?php foreach ($this->expiring as $s): ?>
      <tr><td><strong><?php echo $e(Notify::serviceFull($s)); ?></strong><br><small class="text-muted"><?php echo $e($s->client_name ?: ($s->client_company ?: $s->client_email)); ?></small></td>
      <td class="sp-num sp-n-nowrap"><?php echo NotifyUi::expiry($s->expiry_date, $s->status); ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="sp-foot">Καμία υπηρεσία δεν λήγει τις επόμενες 30 ημέρες.</p><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php echo Year::links(); ?>
</section></div></div></main>
</div>

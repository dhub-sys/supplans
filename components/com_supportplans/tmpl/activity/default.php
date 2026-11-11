<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\Activity;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sidebar;

echo \Support\Component\Supportplans\Site\Helper\Assets::css('supportplans.css', 'offers.css', 'notify.css');
$spItemid = OfferHelper::itemid();
$spCompany = trim((string) $this->params->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Dashboard');
$spLogoUrl = OfferHelper::assetUrl((string) $this->params->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= \Support\Component\Supportplans\Site\Helper\OfferHelper::upperGr(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
$e = [NotifyUi::class, 'e'];
$f = $this->filters;
$query = http_build_query(array_filter($f, static fn($v) => $v !== ''));
$pages = (int) ceil($this->total / 50);
$badge = ['ok' => 'is-accepted', 'warning' => 'sp-n-warn', 'error' => 'is-rejected'];
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
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::head(); ?>
  <?php echo Sidebar::nav('activity'); ?>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Dashboard</strong><span>Πακέτα, πλάνα, προσφορές και client management σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int) $spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace sp-offers-workspace">
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Ασφάλεια &amp; έλεγχος</span><h1>Ιστορικό ενεργειών</h1><p>Κάθε ενέργεια στο Dashboard: συνδέσεις, πακέτα, εργασίες, πλάνα, προσφορές, ρυθμίσεις, πελάτες, υπηρεσίες, πληρωμές, υπενθυμίσεις, αποστολές και αυτόματες εκτελέσεις. Δεν καταγράφονται κωδικοί ούτε κείμενα.</p></div>
  <a class="sp-primary-action" href="<?php echo $e(OfferHelper::url('task=activity.export' . ($query !== '' ? '&' . $query : ''), false)); ?>">⭳ Εξαγωγή σε Excel</a>
</section>
<div class="sp-card">
  <form class="sp-n-filters" method="get" action="<?php echo $e(OfferHelper::url('view=activity', false)); ?>">
    <input type="hidden" name="option" value="com_supportplans"><input type="hidden" name="view" value="activity">
    <input type="hidden" name="tmpl" value="component"><input type="hidden" name="spapp" value="1"><input type="hidden" name="Itemid" value="<?php echo (int) $spItemid; ?>">
    <label class="sp-n-inline">Από <input type="date" name="from" value="<?php echo $e(Notify::parseDate($f['from'])); ?>"></label>
    <label class="sp-n-inline">Έως <input type="date" name="to" value="<?php echo $e(Notify::parseDate($f['to'])); ?>"></label>
    <?php echo NotifyUi::select('area', array_combine($this->areas, $this->areas) ?: [], $f['area'], 'Όλες οι ενότητες'); ?>
    <?php echo NotifyUi::select('user', array_combine($this->users, $this->users) ?: [], $f['user'], 'Όλοι οι χρήστες'); ?>
    <?php echo NotifyUi::select('result', Activity::RESULTS, $f['result'], 'Όλα τα αποτελέσματα'); ?>
    <input type="search" name="q" value="<?php echo $e($f['q']); ?>" placeholder="Αναζήτηση (πελάτης, domain, IP…)">
    <button class="btn btn-outline-secondary">Φιλτράρισμα</button>
    <?php if ($query !== ''): ?><a class="btn btn-link" href="<?php echo OfferHelper::url('view=activity'); ?>">Καθαρισμός</a><?php endif; ?>
  </form>
  <p class="sp-n-help"><?php echo (int) $this->total; ?> εγγραφές<?php echo $query !== '' ? ' με τα φίλτρα' : ''; ?>. Η εξαγωγή σε Excel περιλαμβάνει όσες ταιριάζουν στα φίλτρα (έως 20.000).</p>
  <div class="table-responsive"><table class="table sp-offer-table sp-n-activity">
    <thead><tr><th>Ημερομηνία</th><th>Χρήστης</th><th>Ενότητα</th><th>Ενέργεια</th><th>Στοιχείο</th><th>Αποτέλεσμα</th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r): ?>
      <tr>
        <td class="sp-n-nowrap"><?php echo $e(Notify::datetimeGr($r->created)); ?></td>
        <td><?php echo $e($r->username); ?><?php if ($r->ip !== ''): ?><br><small class="text-muted"><?php echo $e($r->ip); ?></small><?php endif; ?></td>
        <td><?php echo $e($r->area); ?></td>
        <td><strong><?php echo $e($r->action); ?></strong></td>
        <td><?php echo $e($r->item ?: '—'); ?></td>
        <td><span class="sp-offer-status <?php echo $badge[$r->result] ?? ''; ?>"><?php echo $e(Activity::RESULTS[$r->result] ?? $r->result); ?></span><?php if ((string) $r->message !== ''): ?><br><small class="text-muted"><?php echo $e(mb_strimwidth((string) $r->message, 0, 160, '…')); ?></small><?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="6" class="text-muted">Δεν βρέθηκαν ενέργειες.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <?php if ($pages > 1): ?><div class="sp-n-pages"><?php for ($i = max(1, $this->page - 5); $i <= min($pages, $this->page + 5); $i++): ?><?php echo $i === $this->page ? '<span>' . $i . '</span>' : '<a href="' . OfferHelper::url('view=activity' . ($query !== '' ? '&' . $query : '') . '&p=' . $i) . '">' . $i . '</a>'; ?><?php endfor; ?></div><?php endif; ?>
</div>
</section></div></div></main>
</div>

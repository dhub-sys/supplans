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
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::bottom(); ?>
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
  <?php
  // Labels of the recorded fields for the details window.
  $fieldLabels = ['id' => 'Κωδικός εγγραφής', 'plan_id' => 'Πακέτο', 'client_id' => 'Πελάτης (κωδ.)', 'catalog_id' => 'Υπηρεσία καταλόγου (κωδ.)', 'service_plan_id' => 'Πλάνο (κωδ.)',
      'client_service_id' => 'Υπηρεσία πελάτη (κωδ.)', 'do' => 'Ενέργεια', 'status' => 'Κατάσταση', 'name' => 'Όνομα', 'company' => 'Εταιρεία', 'email' => 'Email', 'username' => 'Χρήστης',
      'label' => 'Περιγραφή / domain', 'customer_name' => 'Πελάτης', 'project_name' => 'Έργο', 'offer_number' => 'Αρ. προσφοράς', 'number' => 'Αριθμός', 'plan_type' => 'Τύπος πακέτου',
      'work_type' => 'Εργασία', 'period' => 'Περίοδος', 'subject' => 'Θέμα', 'expiry_date' => 'Λήξη', 'new_expiry' => 'Νέα λήξη', 'paid_on' => 'Ημερομηνία πληρωμής', 'amount' => 'Ποσό',
      'price' => 'Τιμή', 'duration_months' => 'Διάρκεια (μήνες)', 'value' => 'Τιμή', 'template' => 'Template', 'aud_mode' => 'Αποδέκτες', 'to' => 'Προς'];
  $dateLike = static fn($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? Notify::dateGr($v) : $v;
  ?>
  <div class="table-responsive"><table class="table sp-offer-table sp-n-activity is-compact">
    <thead><tr><th>Ημερομηνία</th><th>Χρήστης</th><th>Ενότητα</th><th>Ενέργεια</th><th>Αποτέλεσμα</th><th class="sp-num" aria-label="Λεπτομέρειες"></th></tr></thead>
    <tbody>
    <?php foreach ($this->rows as $r):
        $details = [];
        foreach ((array) json_decode((string) $r->details, true) as $dk => $dv) {
            $details[] = [$fieldLabels[$dk] ?? $dk, is_scalar($dv) ? (string) $dateLike($dv) : json_encode($dv, JSON_UNESCAPED_UNICODE)];
        }
        $info = ['date' => Notify::datetimeGr($r->created), 'user' => (string) $r->username, 'ip' => (string) $r->ip, 'area' => (string) $r->area, 'action' => (string) $r->action,
            'task' => (string) ($r->task ?? ''), 'item' => (string) $r->item, 'result' => Activity::RESULTS[$r->result] ?? (string) $r->result, 'resultClass' => (string) $r->result,
            'message' => (string) $r->message, 'details' => $details];
        $summary = trim((string) $r->item); ?>
      <tr>
        <td class="sp-n-nowrap"><?php echo $e(Notify::datetimeGr($r->created)); ?></td>
        <td class="sp-n-nowrap"><?php echo $e($r->username ?: '—'); ?></td>
        <td class="sp-n-nowrap sp-act-area"><?php echo $e($r->area); ?></td>
        <td class="sp-act-what"><span><strong><?php echo $e($r->action); ?></strong><?php if ($summary !== ''): ?><span class="sp-act-item"> · <?php echo $e($summary); ?></span><?php endif; ?></span></td>
        <td class="sp-n-nowrap"><span class="sp-act-result <?php echo $e($r->result); ?>"><?php echo $e(Activity::RESULTS[$r->result] ?? $r->result); ?></span></td>
        <td class="sp-num"><button type="button" class="sp-act-info" data-activity="<?php echo $e(json_encode($info, JSON_UNESCAPED_UNICODE)); ?>" title="Λεπτομέρειες" aria-label="Λεπτομέρειες ενέργειας"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5h.01"/></svg></button></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$this->rows): ?><tr><td colspan="6" class="text-muted">Δεν βρέθηκαν ενέργειες.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <dialog class="sp-act-dlg" id="sp-act-dlg" aria-label="Λεπτομέρειες ενέργειας">
    <div class="sp-act-dlg-head"><div><strong data-a="action"></strong><span data-a="date"></span></div><button type="button" class="sp-act-dlg-x" aria-label="Κλείσιμο" onclick="this.closest('dialog').close()">×</button></div>
    <dl class="sp-act-dlg-body"></dl>
  </dialog>
  <script>
  (function () {
    var dlg = document.getElementById('sp-act-dlg'), body = dlg.querySelector('dl');
    var row = function (label, value, cls) {
      if (value === undefined || value === null || value === '') return;
      var dt = document.createElement('dt'), dd = document.createElement('dd');
      dt.textContent = label; dd.textContent = value; if (cls) dd.className = cls;
      body.appendChild(dt); body.appendChild(dd);
    };
    document.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-activity]');
      if (!b) return;
      var d = JSON.parse(b.getAttribute('data-activity'));
      dlg.querySelector('[data-a="action"]').textContent = d.action;
      dlg.querySelector('[data-a="date"]').textContent = d.date;
      body.textContent = '';
      row('Αποτέλεσμα', d.result, 'sp-act-result ' + (d.resultClass || ''));
      row('Ενότητα', d.area);
      row('Στοιχείο', d.item);
      row('Χρήστης', d.user);
      row('IP', d.ip);
      row('Μήνυμα', d.message);
      (d.details || []).forEach(function (x) { row(x[0], x[1]); });
      row('Τεχνικό όνομα', d.task);
      dlg.showModal();
    });
    dlg.addEventListener('click', function (ev) { if (ev.target === dlg) dlg.close(); });
  })();
  </script>
  <?php if ($pages > 1): ?><div class="sp-n-pages"><?php for ($i = max(1, $this->page - 5); $i <= min($pages, $this->page + 5); $i++): ?><?php echo $i === $this->page ? '<span>' . $i . '</span>' : '<a href="' . OfferHelper::url('view=activity' . ($query !== '' ? '&' . $query : '') . '&p=' . $i) . '">' . $i . '</a>'; ?><?php endfor; ?></div><?php endif; ?>
</div>
</section></div></div></main>
</div>

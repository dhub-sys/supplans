<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sites;

$e = [NotifyUi::class, 'e'];
$n = 0;
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Συντήρηση sites</span><h1>Νέες ενημερώσεις</h1><p>Όλα τα CMS και extensions των sites με τις εκδόσεις που είναι σε χρήση. Μετά το μαζικό update, γράψτε τη <b>νέα έκδοση</b> σε όσα ενημερώθηκαν και πατήστε «Έλεγχος sites». Θα δείτε ποιο site παίρνει ποιο update, θα ξετσεκάρετε ό,τι δεν έγινε και θα το καταγράψετε. Οι πελάτες λαμβάνουν τις ενημερώσεις συγκεντρωτικά στη <b>μηνιαία σύνοψη</b>: ένα μήνυμα ανά πελάτη, με όλα τα sites του.</p></div>
</section>
<?php if ($this->rows): ?>
<div class="sp-n-warning"><strong>Πρόχειρες ενημερώσεις που δεν έχουν σταλεί:</strong>
<?php foreach ($this->rows as $r): ?> <a href="<?php echo NotifyUi::url('section=rounds&id=' . (int) $r->id); ?>"><?php echo $e($r->title); ?></a> (<?php echo (int) $r->sites; ?> sites)<?php endforeach; ?></div>
<?php endif; ?>
<?php if (!$this->inventory): ?>
<div class="sp-card"><p>Δεν υπάρχουν ακόμα sites με εκδόσεις. Προσθέστε sites στην καρτέλα <a href="<?php echo NotifyUi::url('section=sites'); ?>">Sites</a> ή εισάγετέ τα από Excel.</p></div>
<?php else: ?>
<form action="<?php echo $e(OfferHelper::url('task=sites.prepare', false)); ?>" method="post" id="sp-su-form">
<div class="sp-card">
  <div class="sp-su-top">
   <label>Τίτλος<input name="title" placeholder="Ενημερώσεις <?php echo $e(Notify::dateGr(Notify::today())); ?>"></label>
   <label>Αναζήτηση<input type="search" id="sp-su-q" placeholder="π.χ. YOOtheme, Greek, Akeeba"></label>
   <div class="sp-su-cms" role="group" aria-label="CMS"><button type="button" class="is-active" data-cms="">Όλα</button><button type="button" data-cms="joomla">Joomla</button><button type="button" data-cms="wordpress">WordPress</button></div>
   <label class="sp-check sp-su-only"><input type="checkbox" id="sp-su-filled"> Μόνο με νέα έκδοση</label>
  </div>
  <div class="table-responsive"><table class="table sp-offer-table sp-su-table">
   <thead><tr><th>CMS / Extension</th><th class="sp-center">Sites</th><th>Εκδόσεις σε χρήση <small class="text-muted">(κλικ = νέα έκδοση)</small></th><th>Από έκδοση</th><th>Νέα έκδοση</th><th class="sp-center">Θα ενημερωθούν</th></tr></thead>
   <tbody>
   <?php foreach ($this->inventory as $x): $i = $n++; ?>
    <tr data-name="<?php echo $e(mb_strtolower($x['name'])); ?>" data-cms="<?php echo $e(implode(' ', array_keys($x['cms']))); ?>" data-versions="<?php echo $e(json_encode($x['versions'], JSON_UNESCAPED_UNICODE)); ?>">
     <td><strong><?php echo $e($x['name']); ?></strong><?php if ($x['core']): ?> <span class="sp-cms is-core">CMS</span><?php endif; ?>
      <?php foreach ($x['cms'] as $cms => $cnt): ?><span class="sp-cms is-<?php echo $e($cms); ?>"><?php echo $e(Sites::CMS[$cms] ?? $cms); ?></span><?php endforeach; ?>
      <input type="hidden" name="entries[<?php echo $i; ?>][n]" value="<?php echo $e($x['name']); ?>"></td>
     <td class="sp-center"><?php echo (int) $x['sites']; ?></td>
     <td class="sp-su-versions"><?php foreach ($x['versions'] as $v => $cnt): ?><button type="button" class="sp-su-v" data-v="<?php echo $e((string) $v); ?>"><?php echo $e((string) $v); ?> <b>×<?php echo (int) $cnt; ?></b></button><?php endforeach; ?></td>
     <td><select name="entries[<?php echo $i; ?>][o]" class="sp-su-old"><option value="">Όλες τις παλαιότερες</option><?php foreach ($x['versions'] as $v => $cnt): if ($v === '—') continue; ?><option value="<?php echo $e((string) $v); ?>">μόνο <?php echo $e((string) $v); ?></option><?php endforeach; ?></select></td>
     <td><input name="entries[<?php echo $i; ?>][v]" class="sp-su-new" placeholder="π.χ. <?php echo $e((string) array_key_first($x['versions'])); ?>" autocomplete="off"></td>
     <td class="sp-center sp-su-count">—</td>
    </tr>
   <?php endforeach; ?>
   </tbody>
  </table></div>
  <div class="sp-su-foot"><span id="sp-su-total" class="sp-n-help"></span><button class="btn btn-primary">Έλεγχος sites →</button></div>
</div>
<?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
(function () {
  var form = document.getElementById('sp-su-form'); if (!form) return;
  var rows = [].slice.call(form.querySelectorAll('tbody tr')), cms = '';
  var num = function (v) { return /^v?\d+(\.\d+)*/i.test(v); };
  var cmp = function (a, b) {
    a = String(a).replace(/^v/i, ''); b = String(b).replace(/^v/i, '');
    if (!num(a) || !num(b)) return a.toLowerCase() === b.toLowerCase() ? 0 : -1;
    var x = a.split(/[.\-+ ]/), y = b.split(/[.\-+ ]/);
    for (var i = 0; i < Math.max(x.length, y.length); i++) {
      var p = parseInt(x[i] || '0', 10), q = parseInt(y[i] || '0', 10);
      if (isNaN(p) || isNaN(q)) { if ((x[i] || '') !== (y[i] || '')) return (x[i] || '') < (y[i] || '') ? -1 : 1; continue; }
      if (p !== q) return p < q ? -1 : 1;
    }
    return 0;
  };
  var count = function (tr) {
    var nv = tr.querySelector('.sp-su-new').value.trim(), old = tr.querySelector('.sp-su-old').value, vs = JSON.parse(tr.getAttribute('data-versions')), n = 0;
    if (nv === '') { tr.querySelector('.sp-su-count').textContent = '—'; tr.classList.remove('is-filled'); return 0; }
    Object.keys(vs).forEach(function (v) { var cur = v === '—' ? '' : v; if (old !== '' && cur.toLowerCase() !== old.toLowerCase()) return; if (cur !== '' && cmp(cur, nv) >= 0) return; n += vs[v]; });
    tr.querySelector('.sp-su-count').innerHTML = n ? '<b>' + n + '</b>' : '0';
    tr.classList.add('is-filled'); return n;
  };
  var total = function () {
    var items = 0, inst = 0; rows.forEach(function (tr) { if (tr.querySelector('.sp-su-new').value.trim() !== '') { items++; inst += count(tr); } });
    document.getElementById('sp-su-total').textContent = items ? items + (items === 1 ? ' στοιχείο' : ' στοιχεία') + ' με νέα έκδοση · ' + inst + ' ενημερώσεις σε sites' : '';
  };
  var filter = function () {
    var q = document.getElementById('sp-su-q').value.trim().toLowerCase(), only = document.getElementById('sp-su-filled').checked;
    rows.forEach(function (tr) {
      var ok = (!q || tr.getAttribute('data-name').indexOf(q) >= 0) && (!cms || (' ' + tr.getAttribute('data-cms') + ' ').indexOf(' ' + cms + ' ') >= 0) && (!only || tr.classList.contains('is-filled'));
      tr.hidden = !ok;
    });
  };
  form.addEventListener('input', function (ev) { if (ev.target.matches('.sp-su-new')) { count(ev.target.closest('tr')); total(); } if (ev.target.id === 'sp-su-q') filter(); });
  form.addEventListener('change', function (ev) { if (ev.target.matches('.sp-su-old')) { count(ev.target.closest('tr')); total(); } if (ev.target.id === 'sp-su-filled') filter(); });
  form.addEventListener('click', function (ev) {
    var v = ev.target.closest('.sp-su-v');
    if (v) { var tr = v.closest('tr'); tr.querySelector('.sp-su-new').value = v.getAttribute('data-v') === '—' ? '' : v.getAttribute('data-v'); count(tr); total(); return; }
    var c = ev.target.closest('[data-cms]');
    if (c && c.tagName === 'BUTTON') { cms = c.getAttribute('data-cms'); form.querySelectorAll('.sp-su-cms button').forEach(function (b) { b.classList.toggle('is-active', b === c); }); filter(); }
  });
  form.addEventListener('keydown', function (ev) { if (ev.key === 'Enter' && ev.target.matches('input:not([type=submit])')) ev.preventDefault(); });
  form.addEventListener('submit', function (ev) {
    var any = rows.some(function (tr) { return tr.querySelector('.sp-su-new').value.trim() !== ''; });
    if (!any) { ev.preventDefault(); alert('Γράψτε τη νέα έκδοση σε τουλάχιστον ένα CMS ή extension.'); return; }
    // Only the filled rows are sent (hundreds of empty rows would exceed the server limits).
    rows.forEach(function (tr) { if (tr.querySelector('.sp-su-new').value.trim() === '') tr.querySelectorAll('input,select').forEach(function (i) { i.disabled = true; }); });
  });
})();
</script>
<?php endif; ?>

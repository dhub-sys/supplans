<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Variables editor. Expects $this->varContext = ['rows' => [...], 'builtin' => [key => value]|null, 'intro' => string]. */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$ctx = $this->varContext;
$rows = $ctx['rows'] ?: [['key' => '', 'value' => '']];
$varRow = static function ($key, $value) use ($e) {
    return '<div class="sp-var-row"><span class="sp-var-brace">{</span><input class="sp-var-key" name="var_key[]" value="' . $e($key) . '" placeholder="π.χ. πακέτο" aria-label="Όνομα μεταβλητής"><span class="sp-var-brace">}</span>'
        . '<textarea class="sp-var-value" name="var_value[]" rows="1" placeholder="Τιμή, π.χ. Green" aria-label="Τιμή μεταβλητής">' . $e($value) . '</textarea>'
        . '<button type="button" class="sp-oi-btn sp-oi-remove sp-var-remove" title="Αφαίρεση">×</button></div>';
};
?>
<div class="sp-card sp-vars" id="sp-vars">
 <h3>Μεταβλητές</h3>
 <p class="sp-foot"><?php echo $ctx['intro']; ?></p>
 <?php if ($ctx['builtin'] !== null): ?>
 <div class="sp-var-builtin">
  <?php foreach (OfferHelper::BUILTIN_VARIABLES as $key => $label): ?>
  <div><code>{<?php echo $e($key); ?>}</code><span><?php echo $e($label); ?></span><strong><?php echo $e($ctx['builtin'][$key] ?? '') ?: '—'; ?></strong></div>
  <?php endforeach; ?>
 </div>
 <?php endif; ?>
 <div class="sp-var-rows" id="sp-var-rows"><?php foreach ($rows as $row) echo $varRow($row['key'], $row['value']); ?></div>
 <template id="sp-var-template"><?php echo $varRow('', ''); ?></template>
 <button type="button" class="btn btn-outline-primary btn-sm" id="sp-var-add">＋ Νέα μεταβλητή</button>
</div>
<script>
(function () {
  var box = document.getElementById('sp-var-rows');
  document.getElementById('sp-var-add').addEventListener('click', function () {
    var w = document.createElement('div');
    w.innerHTML = document.getElementById('sp-var-template').innerHTML.trim();
    box.appendChild(w.firstElementChild);
    box.lastElementChild.querySelector('.sp-var-key').focus();
    box.dispatchEvent(new Event('input', { bubbles: true }));
  });
  box.addEventListener('click', function (e) {
    var b = e.target.closest('.sp-var-remove');
    if (!b) return;
    var row = b.closest('.sp-var-row');
    if (box.children.length > 1) row.remove(); else row.querySelectorAll('input,textarea').forEach(function (i) { i.value = ''; });
    box.dispatchEvent(new Event('input', { bubbles: true }));
  });
  // Variable names: lower case, no spaces or braces (same rule as the server).
  box.addEventListener('change', function (e) {
    if (!e.target.classList.contains('sp-var-key')) return;
    e.target.value = e.target.value.trim().replace(/^\{|\}$/g, '').toLocaleLowerCase('el-GR').replace(/\s+/g, '_').replace(/[^\p{L}\p{N}_]/gu, '');
  });
  // Offered in the editor's "Μεταβλητή…" list.
  window.spCustomVariables = function () {
    var out = [];
    box.querySelectorAll('.sp-var-row').forEach(function (r) {
      var k = r.querySelector('.sp-var-key').value.trim().toLocaleLowerCase('el-GR').replace(/\s+/g, '_').replace(/[^\p{L}\p{N}_]/gu, '');
      if (k) out.push(['{' + k + '}', r.querySelector('.sp-var-value').value.trim() || 'δική σας μεταβλητή']);
    });
    return out;
  };
})();
</script>

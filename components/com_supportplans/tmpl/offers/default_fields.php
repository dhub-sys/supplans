<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$aliases = array_flip(OfferHelper::VARIABLE_ALIASES);
$row = static function (array $f) use ($e) {
    ob_start(); ?>
  <div class="sp-field-row" data-field-row>
   <div class="sp-oi-move"><button type="button" class="sp-oi-btn" data-up title="Μετακίνηση πάνω">↑</button><button type="button" class="sp-oi-btn" data-down title="Μετακίνηση κάτω">↓</button></div>
   <label class="sp-oi-field">Όνομα πεδίου στην προσφορά<input name="field_label[]" value="<?php echo $e($f['label']); ?>" placeholder="π.χ. Intro Service" data-label></label>
   <label class="sp-oi-field">Μεταβλητή<span class="sp-field-key"><span>{</span><input name="field_key[]" value="<?php echo $e($f['key']); ?>" placeholder="intro_service" data-key<?php echo $f['key'] !== '' ? ' data-manual="1"' : ''; ?>><span>}</span></span></label>
   <label class="sp-oi-field">Τύπος<select name="field_type[]"><option value="text"<?php echo $f['type'] === 'text' ? ' selected' : ''; ?>>Μία γραμμή</option><option value="textarea"<?php echo $f['type'] === 'textarea' ? ' selected' : ''; ?>>Πολλές γραμμές</option></select></label>
   <label class="sp-oi-field">Προεπιλεγμένη τιμή (προαιρετικά)<input name="field_default[]" value="<?php echo $e($f['default']); ?>" placeholder="Συμπληρώνεται σε κάθε νέα προσφορά"></label>
   <button type="button" class="sp-oi-btn sp-oi-remove" data-remove title="Διαγραφή μεταβλητής">×</button>
  </div>
<?php return ob_get_clean();
};
$fields = $this->fields ?: [['key' => '', 'label' => '', 'type' => 'text', 'default' => '']];
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Προσφορές</span><h1>Μεταβλητές</h1><p>Κάθε μεταβλητή γίνεται πεδίο σε όλες τις προσφορές, όπως ο Πελάτης και το Έργο. Γράψτε τη μεταβλητή στα κείμενα (π.χ. <code>{intro_service}</code>) και η τιμή του πεδίου κάθε προσφοράς μπαίνει αυτόματα στο Pdf.</p></div>
</section>

<?php if ($this->undefinedVariables): ?>
<div class="sp-card sp-undefined-vars">
 <h3>Χρησιμοποιούνται στα κείμενα αλλά δεν έχουν οριστεί</h3>
 <p class="sp-foot">Βρέθηκαν στις Υπηρεσίες ή στα Templates. Προσθέστε τες για να αποκτήσουν πεδίο στις προσφορές.</p>
 <div class="sp-undefined-list">
  <?php foreach ($this->undefinedVariables as $key): ?>
  <button type="button" class="btn btn-sm btn-outline-primary" data-add-key="<?php echo $e($key); ?>">＋ {<?php echo $e($key); ?>}</button>
  <?php endforeach; ?>
 </div>
</div>
<?php endif; ?>

<form action="<?php echo OfferHelper::url('view=offers&section=variables'); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <div class="sp-title-row"><h2>Οι μεταβλητές σας</h2><button type="button" class="btn btn-primary" onclick="spSubmit('offerfield.save')">Αποθήκευση</button></div>
  <div id="sp-field-rows" class="sp-field-rows"><?php foreach ($fields as $f) echo $row($f); ?></div>
  <template id="sp-field-template"><?php echo $row(['key' => '', 'label' => '', 'type' => 'text', 'default' => '']); ?></template>
  <button type="button" class="btn btn-outline-primary" id="sp-field-add">＋ Νέα μεταβλητή</button>
  <p class="sp-foot">Η μεταβλητή συμπληρώνεται αυτόματα από το όνομα του πεδίου (π.χ. «Intro Service» → <code>{intro_service}</code>) και μπορείτε να την αλλάξετε. Η σειρά εδώ είναι και η σειρά των πεδίων στην προσφορά. Αν διαγράψετε μια μεταβλητή, οι προσφορές που έχουν ήδη τιμή την κρατούν.</p>
 </div>
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

<div class="sp-card">
 <h3>Βασικές μεταβλητές</h3>
 <p class="sp-foot">Συμπληρώνονται αυτόματα από τα στοιχεία κάθε προσφοράς.</p>
 <div class="sp-var-builtin">
  <?php foreach (OfferHelper::BUILTIN_VARIABLES as $key => $label): ?>
  <div><code>{<?php echo $e($key); ?>}</code><span><?php echo $e($label); ?></span><strong><?php echo isset($aliases[$key]) ? 'ή <code>{' . $e($aliases[$key]) . '}</code>' : '&nbsp;'; ?></strong></div>
  <?php endforeach; ?>
 </div>
</div>

<script>
(function () {
  var rows = document.getElementById('sp-field-rows');
  var tpl = document.getElementById('sp-field-template');
  function keyFrom(text) {
    return text.trim().replace(/^\{|\}$/g, '').toLocaleLowerCase('el-GR').replace(/\s+/g, '_').replace(/[^\p{L}\p{N}_]/gu, '');
  }
  function labelFrom(key) { var l = key.replace(/_/g, ' ').trim(); return l.charAt(0).toLocaleUpperCase('el-GR') + l.slice(1); }
  function addRow(key) {
    var w = document.createElement('div');
    w.innerHTML = tpl.innerHTML.trim();
    var r = w.firstElementChild;
    if (key) { r.querySelector('[data-key]').value = key; r.querySelector('[data-key]').dataset.manual = '1'; r.querySelector('[data-label]').value = labelFrom(key); }
    // Replace a single empty row instead of adding below it.
    var only = rows.querySelectorAll('[data-field-row]');
    if (only.length === 1 && !only[0].querySelector('[data-key]').value && !only[0].querySelector('[data-label]').value) only[0].remove();
    rows.appendChild(r);
    (key ? r.querySelector('[data-label]') : r.querySelector('[data-label]')).focus();
    return r;
  }
  document.getElementById('sp-field-add').addEventListener('click', function () { addRow(''); });
  document.querySelectorAll('[data-add-key]').forEach(function (b) {
    b.addEventListener('click', function () { addRow(b.dataset.addKey); var card = b.closest('.sp-undefined-vars'); b.remove(); if (card && !card.querySelector('[data-add-key]')) card.hidden = true; });
  });
  rows.addEventListener('input', function (e) {
    var r = e.target.closest('[data-field-row]');
    if (e.target.matches('[data-label]')) {
      var k = r.querySelector('[data-key]');
      if (!k.dataset.manual) k.value = keyFrom(e.target.value);
    } else if (e.target.matches('[data-key]')) {
      e.target.dataset.manual = e.target.value ? '1' : '';
    }
  });
  rows.addEventListener('change', function (e) { if (e.target.matches('[data-key]')) e.target.value = keyFrom(e.target.value); });
  rows.addEventListener('click', function (e) {
    var r = e.target.closest('[data-field-row]');
    if (!r) return;
    if (e.target.closest('[data-remove]')) {
      if (rows.children.length > 1) r.remove(); else r.querySelectorAll('input').forEach(function (i) { i.value = ''; delete i.dataset.manual; });
    } else if (e.target.closest('[data-up]') && r.previousElementSibling) rows.insertBefore(r, r.previousElementSibling);
    else if (e.target.closest('[data-down]') && r.nextElementSibling) rows.insertBefore(r.nextElementSibling, r);
  });
})();
</script>

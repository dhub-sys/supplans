<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$o = $this->selected;
$docUrl = OfferHelper::url('view=offerdoc&id=' . (int) $o->id);
$fileTitle = trim($o->offer_number . ' - ' . ($o->project_name ?: $o->customer_name), ' -');
$num = static fn($v) => number_format((float) $v, 2, '.', '');

$row = static function ($i, $item) use ($e, $num) {
    ob_start(); ?>
  <div class="sp-oi" data-row data-sort-item>
   <div class="sp-oi-head">
    <button type="button" class="sp-grip" data-sort-handle title="Σύρετε για αλλαγή σειράς" aria-label="Αλλαγή σειράς (σύρετε ή χρησιμοποιήστε τα βελάκια)"><svg viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></button>
    <input type="hidden" name="items[<?php echo $i; ?>][service_id]" value="<?php echo (int) ($item->service_id ?? 0); ?>">
    <label class="sp-oi-field sp-oi-title">Τίτλος ενότητας<input name="items[<?php echo $i; ?>][section_title]" value="<?php echo $e($item->section_title ?? ''); ?>" data-field="section_title"></label>
    <label class="sp-oi-field sp-oi-label">Περιγραφή στον πίνακα τιμών<input name="items[<?php echo $i; ?>][table_label]" value="<?php echo $e($item->table_label ?? ''); ?>" data-field="table_label"></label>
    <label class="sp-oi-field sp-oi-money">Τιμή €<input type="number" step="0.01" name="items[<?php echo $i; ?>][price]" value="<?php echo $num($item->price ?? 0); ?>" data-field="price" data-calc></label>
    <label class="sp-oi-field sp-oi-money">Έκπτωση €<input type="number" step="0.01" min="0" name="items[<?php echo $i; ?>][discount]" value="<?php echo $num($item->discount ?? 0); ?>" data-field="discount" data-calc></label>
    <div class="sp-oi-net"><span>Αξία</span><strong data-net>0,00 €</strong></div>
    <button type="button" class="sp-icon-btn is-danger sp-oi-remove" data-remove title="Αφαίρεση από την προσφορά" aria-label="Αφαίρεση από την προσφορά"><svg viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg></button>
   </div>
   <div class="sp-oi-flags">
    <label class="sp-check"><input type="checkbox" name="items[<?php echo $i; ?>][show_description]" value="1" data-field="show_description" <?php echo !empty($item->show_description) ? 'checked' : ''; ?>> Περιγράφεται στο έγγραφο</label>
    <label class="sp-check"><input type="checkbox" name="items[<?php echo $i; ?>][in_table]" value="1" data-field="in_table" data-calc <?php echo !empty($item->in_table) ? 'checked' : ''; ?>> Στον πίνακα τιμών</label>
    <span class="sp-oi-layout" data-layout>
     <select name="items[<?php echo $i; ?>][page_break]" data-field="page_break" aria-label="Θέση στο Pdf">
      <option value="0"<?php echo empty($item->page_break) ? ' selected' : ''; ?>>Συνέχεια στην ίδια σελίδα</option>
      <option value="1"<?php echo !empty($item->page_break) ? ' selected' : ''; ?>>Ξεκινά σε νέα σελίδα</option>
     </select>
     <label class="sp-oi-space" data-space>απόσταση <input type="number" min="0" max="80" step="1" name="items[<?php echo $i; ?>][space_before]" value="<?php echo (int) ($item->space_before ?? 5); ?>" data-field="space_before" aria-label="Απόσταση από την προηγούμενη ενότητα σε mm"> mm</label>
    </span>
    <details class="sp-oi-body"><summary>Κείμενο περιγραφής</summary><textarea name="items[<?php echo $i; ?>][body]" rows="10" data-field="body" data-sp-editor><?php echo $e($item->body ?? ''); ?></textarea></details>
    <?php echo OfferHelper::listsEditor('items[' . $i . '][lists]', OfferHelper::lists($item->lists ?? '')); ?>
   </div>
  </div>
<?php return ob_get_clean();
};

$library = [];
foreach ($this->services as $s) {
    if (!(int) $s->published) continue;
    $library[(int) $s->id] = [
        'service_id' => (int) $s->id,
        'section_title' => trim((string) $s->section_title) ?: $s->name,
        'table_label' => trim((string) $s->table_label) ?: $s->name,
        'price' => (float) $s->price,
        'discount' => 0,
        'show_description' => (int) $s->show_description,
        'in_table' => (int) $s->in_table,
        'page_break' => (int) ($s->page_break ?? 0),
        'space_before' => (int) ($s->space_before ?? 5),
        'body' => (string) $s->body,
        'lists' => OfferHelper::lists($s->lists ?? ''),
    ];
}
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('offer.apply')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spSubmit('offer.save')">Αποθήκευση &amp; κλείσιμο</button>
 <button type="button" class="btn btn-secondary" onclick="spPrintOffer()" title="Αποθηκεύστε πρώτα τις αλλαγές">Εκτύπωση / Pdf</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spPreviewOffer()" title="Προβολή του εγγράφου με τις τρέχουσες αλλαγές, χωρίς αποθήκευση">Προεπισκόπηση</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=offers'); ?>">Κλείσιμο</a>
</div>
<iframe id="sp-offer-print-frame" title="Προσφορά" src="<?php echo $docUrl; ?>" style="position:fixed;width:1px;height:1px;right:0;bottom:0;border:0;opacity:0;pointer-events:none;" aria-hidden="true"></iframe>

<dialog class="sp-preview" id="sp-preview" aria-label="Προεπισκόπηση προσφοράς">
 <div class="sp-preview-bar">
  <div><strong>Προεπισκόπηση</strong><span id="sp-preview-note">Με τις τρέχουσες αλλαγές — δεν έχουν αποθηκευτεί ακόμα.</span></div>
  <div class="sp-preview-actions">
   <button type="button" class="btn btn-outline-secondary btn-sm" onclick="spPreviewOffer()">↻ Ανανέωση</button>
   <button type="button" class="btn btn-secondary btn-sm" onclick="spPrintPreview()">Εκτύπωση / Pdf</button>
   <button type="button" class="btn btn-primary btn-sm" onclick="spSubmit('offer.apply')">Αποθήκευση</button>
   <button type="button" class="sp-preview-close" onclick="document.getElementById('sp-preview').close()" aria-label="Κλείσιμο">×</button>
  </div>
 </div>
 <div class="sp-preview-body"><div class="sp-preview-loading" id="sp-preview-loading">Δημιουργία προεπισκόπησης…</div><iframe name="sp-preview-frame" id="sp-preview-frame" title="Προεπισκόπηση προσφοράς"></iframe></div>
</dialog>

<form action="<?php echo OfferHelper::url('view=offers&id=' . (int) $o->id); ?>" method="post" name="adminForm" id="adminForm" data-dirty-watch>
 <div class="sp-card">
  <div class="sp-title-row"><div><h2>Προσφορά <?php echo $e($o->offer_number); ?></h2><div class="sp-plan-subtitle"><?php echo $e($o->customer_name . ($o->project_name ? ' · ' . $o->project_name : '')); ?></div></div><span class="sp-offer-status is-<?php echo $e($o->status); ?>"><?php echo $e(OfferHelper::statusLabel($o->status)); ?></span></div>
  <div class="sp-grid sp-offer-head-grid">
   <label>Αρ. προσφοράς<input name="offer_number" value="<?php echo $e($o->offer_number); ?>" required></label>
   <label>Ημερομηνία<input type="date" name="offer_date" value="<?php echo $e($o->offer_date); ?>" required></label>
   <label>Πελάτης<input name="customer_name" value="<?php echo $e($o->customer_name); ?>" required></label>
   <label>Έργο / Εταιρεία<input name="project_name" value="<?php echo $e($o->project_name); ?>"></label>
   <label>Κατάσταση<select name="status"><?php foreach (OfferHelper::STATUSES as $k => $label): ?><option value="<?php echo $k; ?>" <?php echo $o->status === $k ? 'selected' : ''; ?>><?php echo $e($label); ?></option><?php endforeach; ?></select></label>
   <label>Φ.Π.Α. %<input type="number" step="0.01" min="0" name="vat_rate" id="sp-offer-vat" value="<?php echo $e($o->vat_rate); ?>"></label>
  </div>
  <?php $this->fieldValues = OfferHelper::offerFields($o, $this->params); echo $this->loadTemplate('offerfields'); ?>
 </div>

 <div class="sp-card">
  <div class="sp-title-row"><h3>Υπηρεσίες προσφοράς</h3></div>
  <p class="sp-foot">Για κάθε υπηρεσία επιλέξτε αν <b>περιγράφεται</b> ως ενότητα στο έγγραφο ή/και αν μπαίνει στον <b>πίνακα τιμών</b>. Σύρετε από τη λαβή <span class="sp-inline-grip">⋮⋮</span> για να αλλάξετε τη σειρά στο Pdf. Οι αλλαγές αφορούν μόνο αυτή την προσφορά.</p>
  <div id="sp-offer-items" data-sortable>
   <?php $i = 0; foreach ($this->items as $item) { echo $row($i++, $item); } ?>
  </div>
  <template id="sp-offer-row-template"><?php echo $row('__i__', (object) ['show_description' => 1, 'in_table' => 1, 'page_break' => 0, 'space_before' => 5]); ?></template>
  <div class="sp-oi-add">
   <select id="sp-offer-library">
    <option value="">— Προσθήκη υπηρεσίας από τη βιβλιοθήκη —</option>
    <?php foreach ($library as $id => $s): ?><option value="<?php echo $id; ?>"><?php echo $e($s['section_title'] . ($s['price'] ? ' — ' . OfferHelper::money($s['price']) . ' €' : '')); ?></option><?php endforeach; ?>
   </select>
   <button type="button" class="btn btn-outline-primary" id="sp-offer-add">＋ Προσθήκη</button>
   <button type="button" class="btn btn-outline-secondary" id="sp-offer-add-blank">＋ Κενή γραμμή</button>
  </div>
  <div class="sp-offer-totals" id="sp-offer-totals">
   <div><span>Σύνολο τιμών</span><strong data-total="price">0,00 €</strong></div>
   <div><span>Έκπτωση</span><strong data-total="discount">0,00 €</strong></div>
   <div><span>Αξία προ Φ.Π.Α.</span><strong data-total="net">0,00 €</strong></div>
   <div><span>Φ.Π.Α.</span><strong data-total="vat">0,00 €</strong></div>
   <div class="is-grand"><span>Σύνολο</span><strong data-total="gross">0,00 €</strong></div>
  </div>
 </div>

 <div class="sp-card">
  <h3>Κείμενα εγγράφου</h3>
  <div class="sp-grid">
   <label class="sp-wide">Τίτλος εξωφύλλου (μία γραμμή ανά σειρά — το Έργο προστίθεται ως τελευταία γραμμή)<textarea name="doc_title" rows="2"><?php echo $e($o->doc_title); ?></textarea></label>
   <label>Υπότιτλος<input name="doc_subtitle" value="<?php echo $e($o->doc_subtitle); ?>" placeholder="ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ"></label>
   <label>Κείμενο footer<input name="footer_title" value="<?php echo $e($o->footer_title); ?>" placeholder="Αν μείνει κενό, χρησιμοποιείται ο τίτλος"></label>
   <label>Τίτλος ενότητας τιμών<input name="pricing_title" value="<?php echo $e($o->pricing_title); ?>" placeholder="Αξία Έργου – Τρόπος Πληρωμής"></label>
   <?php $this->layoutCtx = ['name' => 'pricing', 'label' => 'Ενότητα τιμών', 'break' => (int) ($o->pricing_break ?? 0), 'space' => (int) ($o->pricing_space ?? 5)]; echo $this->loadTemplate('sectionlayout'); ?>
   <label class="sp-check sp-check-inline"><input type="checkbox" name="show_banks" value="1" <?php echo (int) $o->show_banks ? 'checked' : ''; ?>> Εμφάνιση τραπεζικών λογαριασμών</label>
   <label class="sp-check sp-check-inline"><input type="checkbox" name="show_header" value="1" <?php echo (int) ($o->show_header ?? 1) ? 'checked' : ''; ?>> Header σε κάθε σελίδα (εκτός εξωφύλλου)</label>
   <label>Μέγεθος λογοτύπου header (px)<input type="number" min="12" max="120" step="1" name="header_logo" value="<?php echo (int) ($o->header_logo ?? 28); ?>"></label>
   <label>Απόσταση περιεχομένου από το header (mm)<input type="number" min="0" max="40" step="1" name="header_gap" value="<?php echo (int) ($o->header_gap ?? 6); ?>"></label>
   <label class="sp-wide">Κείμενο header<input name="header_text" value="<?php echo $e($o->header_text ?? ''); ?>" placeholder="Αν μείνει κενό: υπότιτλος + αριθμός προσφοράς · έργο — δέχεται και μεταβλητές"></label>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κείμενο μετά τον πίνακα τιμών (όροι πληρωμής)</span><textarea name="payment_text" rows="6" data-sp-editor><?php echo $e($o->payment_text); ?></textarea></div>
   <label class="sp-wide">Τίτλος όρων<input name="terms_title" value="<?php echo $e($o->terms_title); ?>" placeholder="Όροι Προσφοράς / Παραπομπές"></label>
   <?php $this->layoutCtx = ['name' => 'terms', 'label' => 'Ενότητα όρων', 'break' => (int) ($o->terms_break ?? 0), 'space' => (int) ($o->terms_space ?? 5)]; echo $this->loadTemplate('sectionlayout'); ?>
   <div class="sp-wide sp-field"><span class="sp-field-label">Όροι προσφοράς / παραπομπές</span><textarea name="terms_body" rows="10" data-sp-editor><?php echo $e($o->terms_body); ?></textarea></div>
  </div>
  <?php echo $this->loadTemplate('markup'); ?>
 </div>

 <div class="sp-card">
  <h3>Εσωτερικές σημειώσεις</h3>
  <div class="sp-grid"><label class="sp-wide">Δεν εμφανίζονται στο Pdf<textarea name="notes" rows="3"><?php echo $e($o->notes); ?></textarea></label></div>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) $o->id; ?>">
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>

<div class="sp-card sp-offer-danger">
 <form method="post" action="<?php echo OfferHelper::url('task=offer.duplicate'); ?>"><input type="hidden" name="id" value="<?php echo (int) $o->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-primary">Αντίγραφο ως νέα προσφορά</button></form>
 <form method="post" action="<?php echo OfferHelper::url('task=offer.delete'); ?>" onsubmit="return confirm('Να διαγραφεί οριστικά η προσφορά;');"><input type="hidden" name="id" value="<?php echo (int) $o->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-danger">Διαγραφή προσφοράς</button></form>
</div>

<script>
(function(){
  const library = <?php echo json_encode($library, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  const list = document.getElementById('sp-offer-items');
  const tpl = document.getElementById('sp-offer-row-template');
  const vat = document.getElementById('sp-offer-vat');
  const form = document.getElementById('adminForm');
  let next = list.querySelectorAll('[data-row]').length;
  let dirty = false;
  const money = v => (Math.round(v * 100) / 100).toLocaleString('el-GR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
  const val = (row, f) => parseFloat((row.querySelector('[data-field="' + f + '"]') || {}).value || 0) || 0;

  function recalc(){
    const t = {price: 0, discount: 0, net: 0, vat: 0, gross: 0};
    const rate = parseFloat(vat.value || 0) || 0;
    list.querySelectorAll('[data-row]').forEach(row => {
      const price = val(row, 'price');
      const discount = Math.min(Math.abs(val(row, 'discount')), Math.abs(price));
      const net = price - discount;
      const inTable = row.querySelector('[data-field="in_table"]').checked;
      row.querySelector('[data-net]').textContent = money(net);
      row.classList.toggle('is-excluded', !inTable);
      row.classList.toggle('is-hidden-text', !row.querySelector('[data-field="show_description"]').checked);
      // Layout applies to sections shown in the document; spacing only when continuing on the same page.
      row.querySelector('[data-layout]').classList.toggle('is-disabled', !row.querySelector('[data-field="show_description"]').checked);
      row.querySelector('[data-space]').hidden = row.querySelector('[data-field="page_break"]').value === '1';
      if (!inTable) return;
      const v = Math.round(net * rate) / 100;
      t.price += price; t.discount += discount; t.net += net; t.vat += v; t.gross += net + v;
    });
    Object.keys(t).forEach(k => { document.querySelector('[data-total="' + k + '"]').textContent = (k === 'discount' && t[k] ? '-' : '') + money(t[k]); });
  }

  function addRow(data){
    const html = tpl.innerHTML.replace(/__i__/g, String(next++));
    const wrap = document.createElement('div');
    wrap.innerHTML = html.trim();
    const row = wrap.firstElementChild;
    if (data) {
      Object.keys(data).forEach(k => {
        const el = row.querySelector('[data-field="' + k + '"]');
        if (!el) return;
        if (el.type === 'checkbox') el.checked = !!+data[k]; else el.value = data[k];
      });
      row.querySelector('input[name$="[service_id]"]').value = data.service_id || 0;
    }
    list.appendChild(row);
    if (window.SpEditor) window.SpEditor.attachAll(row);
    if (data && data.lists && window.SpLists) data.lists.forEach(l => window.SpLists.add(row.querySelector('[data-lists]'), l));
    dirty = true;
    recalc();
    row.querySelector('[data-field="section_title"]').focus();
  }

  list.addEventListener('click', ev => {
    const row = ev.target.closest('[data-row]');
    if (!row) return;
    if (ev.target.closest('[data-remove]')) {
      const title = row.querySelector('[data-field="section_title"]').value || 'τη γραμμή';
      if (confirm('Αφαίρεση «' + title + '» από την προσφορά;')) { row.remove(); dirty = true; recalc(); }
    }
  });
  form.addEventListener('input', () => { dirty = true; recalc(); });
  form.addEventListener('change', () => { dirty = true; recalc(); });
  form.addEventListener('submit', () => { dirty = false; });
  document.getElementById('sp-offer-add').addEventListener('click', () => {
    const sel = document.getElementById('sp-offer-library');
    if (!sel.value || !library[sel.value]) { sel.focus(); return; }
    addRow(library[sel.value]);
    sel.value = '';
  });
  document.getElementById('sp-offer-add-blank').addEventListener('click', () => addRow({section_title: '', table_label: '', price: '0.00', discount: '0.00', show_description: 1, in_table: 1, page_break: 0, space_before: 5}));
  window.addEventListener('beforeunload', ev => { if (dirty) { ev.preventDefault(); ev.returnValue = ''; } });

  const origSubmit = window.spSubmit;
  window.spSubmit = function(task){ dirty = false; origSubmit(task); };
  // Preview: posts the current form (including unsaved changes) to offer.preview inside the dialog.
  window.spPreviewOffer = function(){
    const dlg = document.getElementById('sp-preview');
    const frame = document.getElementById('sp-preview-frame');
    const taskInput = form.querySelector('input[name="task"]');
    document.getElementById('sp-preview-loading').hidden = false;
    document.getElementById('sp-preview-note').textContent = dirty ? 'Με τις τρέχουσες αλλαγές — δεν έχουν αποθηκευτεί ακόμα.' : 'Όπως είναι αποθηκευμένη.';
    form.target = 'sp-preview-frame';
    taskInput.value = 'offer.preview';
    HTMLFormElement.prototype.submit.call(form);
    form.target = '';
    taskInput.value = '';
    if (!dlg.open) dlg.showModal();
  };
  document.getElementById('sp-preview-frame').addEventListener('load', () => { document.getElementById('sp-preview-loading').hidden = true; });
  window.spPrintPreview = function(){
    const frame = document.getElementById('sp-preview-frame');
    if (!frame.contentWindow) return;
    const title = document.title;
    document.title = <?php echo json_encode($fileTitle, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
    (frame.contentWindow.spOfferReady || Promise.resolve()).then(() => {
      frame.contentWindow.focus();
      frame.contentWindow.print();
      setTimeout(() => { document.title = title; }, 1000);
    });
  };
  window.spPrintOffer = function(){
    if (dirty && !confirm('Υπάρχουν μη αποθηκευμένες αλλαγές που δεν θα εμφανιστούν στο Pdf. Συνέχεια;')) return;
    const frame = document.getElementById('sp-offer-print-frame');
    if (!frame || !frame.contentWindow) return;
    const title = document.title;
    document.title = <?php echo json_encode($fileTitle, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
    const ready = frame.contentWindow.spOfferReady || Promise.resolve();
    ready.then(() => {
      frame.contentWindow.focus();
      frame.contentWindow.print();
      setTimeout(() => { document.title = title; }, 1000);
    });
  };
  recalc();
})();
</script>

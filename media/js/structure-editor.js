// Tree editor of the structure proposals: an ordered list of rows with a level (0 = main
// section). A row "block" is the row plus the rows under it (deeper levels), which move with it.
(function () {
  var box = document.getElementById('sp-st');
  if (!box) return;
  var form = document.getElementById('adminForm');
  var hidden = document.getElementById('sp-st-tree');
  var summary = document.getElementById('sp-st-summary');
  var MAX = parseInt(box.dataset.maxLevel, 10) || 4;
  var dirty = false;
  var data = [];
  try { data = JSON.parse(document.getElementById('sp-st-data').textContent) || []; } catch (e) { data = []; }

  var ICON = {
    up: '<svg viewBox="0 0 24 24"><path d="M12 19V5M6 11l6-6 6 6"/></svg>',
    down: '<svg viewBox="0 0 24 24"><path d="M12 5v14M6 13l6 6 6-6"/></svg>',
    out: '<svg viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>',
    in: '<svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    child: '<svg viewBox="0 0 24 24"><path d="M6 4v9a3 3 0 0 0 3 3h9M15 13l3 3-3 3"/></svg>',
    add: '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>',
    del: '<svg viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>',
    chev: '<svg viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></svg>'
  };
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]; }); }
  function btn(act, title, icon, cls) { return '<button type="button" class="sp-st-btn' + (cls ? ' ' + cls : '') + '" data-act="' + act + '" title="' + title + '" aria-label="' + title + '">' + icon + '</button>'; }

  function makeRow(n) {
    var row = document.createElement('div');
    row.className = 'sp-st-row';
    row.innerHTML =
      '<div class="sp-st-line">' +
        '<span class="sp-st-no"></span>' +
        '<input class="sp-st-title" data-f="title" maxlength="190" placeholder="Τίτλος ενότητας / σελίδας" aria-label="Τίτλος ενότητας">' +
        '<button type="button" class="sp-st-more" data-act="toggle" aria-expanded="false">' + ICON.chev + '<span>Περιεχόμενο</span><i class="sp-st-dot" title="Έχει περιεχόμενο"></i></button>' +
        '<span class="sp-st-tools">' +
          btn('up', 'Μετακίνηση πάνω', ICON.up) + btn('down', 'Μετακίνηση κάτω', ICON.down) +
          btn('out', 'Ένα επίπεδο πάνω (←)', ICON.out) + btn('in', 'Υποενότητα της από πάνω (→)', ICON.in) +
          btn('child', 'Νέα υποενότητα', ICON.child) + btn('after', 'Νέα ενότητα από κάτω', ICON.add) +
          btn('del', 'Διαγραφή', ICON.del, 'is-danger') +
        '</span>' +
      '</div>' +
      '<div class="sp-st-details" hidden>' +
        '<label class="sp-st-field">Στόχος της ενότητας <small>(μία πρόταση — στο Pdf πάνω από τις προτάσεις περιεχομένου)</small><input data-f="purpose" maxlength="1000" placeholder="π.χ. Να πείσει τον επισκέπτη να ζητήσει προσφορά"></label>' +
        '<div class="sp-st-field"><span>Προτάσεις περιεχομένου</span><textarea data-f="content" rows="6"></textarea></div>' +
        '<div class="sp-st-field sp-st-layout" data-layout-pick><span>Θέση στο Pdf <small>(στις προτάσεις περιεχομένου)</small></span><div class="sp-oi-layout">' +
          '<select data-f="break"><option value="0">Συνέχεια στην ίδια σελίδα</option><option value="1">Ξεκινά σε νέα σελίδα</option></select>' +
          '<label class="sp-oi-space" data-space>απόσταση <input type="number" min="0" max="80" step="1" data-f="space" placeholder="αυτόματη" aria-label="Απόσταση από την προηγούμενη ενότητα σε mm"> mm</label>' +
        '</div></div>' +
      '</div>';
    row.querySelector('[data-f="title"]').value = n.title || '';
    row.querySelector('[data-f="purpose"]').value = n.purpose || '';
    row.querySelector('[data-f="content"]').value = n.content || '';
    row.querySelector('[data-f="break"]').value = n['break'] ? '1' : '0';
    row.querySelector('[data-f="space"]').value = n.space === undefined || n.space === null ? '' : n.space;
    row.querySelector('[data-space]').hidden = !!n['break'];
    setLevel(row, n.level || 0);
    return row;
  }
  function rows() { return Array.prototype.slice.call(box.children); }
  function level(row) { return parseInt(row.dataset.level, 10) || 0; }
  function setLevel(row, l) { row.dataset.level = l; row.style.setProperty('--l', l); }
  // The row and every following row that is deeper.
  function block(row) {
    var all = rows(), i = all.indexOf(row), l = level(row), out = [row];
    for (var j = i + 1; j < all.length && level(all[j]) > l; j++) out.push(all[j]);
    return out;
  }
  function prevSibling(row) {
    var all = rows(), l = level(row);
    for (var j = all.indexOf(row) - 1; j >= 0; j--) {
      if (level(all[j]) < l) return null;
      if (level(all[j]) === l) return all[j];
    }
    return null;
  }
  function nextSibling(row) {
    var b = block(row), after = b[b.length - 1].nextElementSibling;
    return after && level(after) === level(row) ? after : null;
  }
  function hasContent(row) {
    return row.querySelector('[data-f="purpose"]').value.trim() !== '' || row.querySelector('[data-f="content"]').value.trim() !== '';
  }

  function refresh() {
    var counters = [], top = 0, deepest = 0, all = rows(), prev = -1;
    all.forEach(function (row, i) {
      var l = Math.min(level(row), prev + 1); // never deeper than one under the row above
      if (l !== level(row)) setLevel(row, l);
      counters = counters.slice(0, l + 1);
      counters[l] = (counters[l] || 0) + 1;
      row.querySelector('.sp-st-no').textContent = counters.join('.');
      if (l === 0) top++;
      deepest = Math.max(deepest, l + 1);
      row.classList.toggle('has-content', hasContent(row));
      row.classList.toggle('is-parent', i + 1 < all.length && level(all[i + 1]) > l);
      row.querySelector('[data-act="up"]').disabled = !prevSibling(row);
      row.querySelector('[data-act="down"]').disabled = !nextSibling(row);
      row.querySelector('[data-act="out"]').disabled = l === 0;
      var deepestInBlock = Math.max.apply(null, block(row).map(level));
      row.querySelector('[data-act="in"]').disabled = !prevSibling(row) || deepestInBlock >= MAX;
      row.querySelector('[data-act="child"]').disabled = l >= MAX;
      prev = l;
    });
    if (summary) summary.textContent = all.length ? top + (top === 1 ? ' κύρια ενότητα' : ' κύριες ενότητες') + ' · ' + all.length + (all.length === 1 ? ' σελίδα' : ' σελίδες') + ' · ' + deepest + (deepest === 1 ? ' επίπεδο' : ' επίπεδα') : 'Δεν υπάρχουν ενότητες.';
  }
  function serialize() {
    hidden.value = JSON.stringify(rows().map(function (row) {
      return { title: row.querySelector('[data-f="title"]').value, level: level(row), purpose: row.querySelector('[data-f="purpose"]').value, content: row.querySelector('[data-f="content"]').value, 'break': row.querySelector('[data-f="break"]').value === '1' ? 1 : 0, space: row.querySelector('[data-f="space"]').value };
    }));
  }
  function open(row, on) {
    var d = row.querySelector('.sp-st-details');
    d.hidden = !on;
    row.classList.toggle('is-open', on);
    row.querySelector('[data-act="toggle"]').setAttribute('aria-expanded', on ? 'true' : 'false');
    if (on && window.SpEditor) window.SpEditor.attach(row.querySelector('[data-f="content"]'));
  }
  function insertAfter(ref, node) { ref.parentNode.insertBefore(node, ref.nextSibling); }
  function add(afterBlockOf, l) {
    var row = makeRow({ level: l });
    if (!afterBlockOf) box.appendChild(row);
    else { var b = block(afterBlockOf); insertAfter(b[b.length - 1], row); }
    dirty = true; refresh();
    row.querySelector('[data-f="title"]').focus();
    return row;
  }
  function shift(row, d) {
    block(row).forEach(function (r) { setLevel(r, Math.max(0, Math.min(MAX, level(r) + d))); });
  }
  function act(row, a) {
    var b, s;
    if (a === 'toggle') { open(row, row.querySelector('.sp-st-details').hidden); return; }
    if (a === 'up' && (s = prevSibling(row))) { block(row).forEach(function (r) { box.insertBefore(r, s); }); }
    else if (a === 'down' && (s = nextSibling(row))) { b = block(s); var anchor = b[b.length - 1]; block(row).reverse().forEach(function (r) { insertAfter(anchor, r); }); }
    else if (a === 'in') { if (prevSibling(row) && Math.max.apply(null, block(row).map(level)) < MAX) shift(row, 1); }
    else if (a === 'out') { if (level(row) > 0) shift(row, -1); }
    else if (a === 'child') { var c = makeRow({ level: level(row) + 1 }); insertAfter(row, c); dirty = true; refresh(); c.querySelector('[data-f="title"]').focus(); return; }
    else if (a === 'after') { add(row, level(row)); return; }
    else if (a === 'del') {
      b = block(row);
      var name = row.querySelector('[data-f="title"]').value.trim() || 'Χωρίς τίτλο';
      if (!confirm(b.length > 1 ? 'Διαγραφή της «' + name + '» και των ' + (b.length - 1) + ' υποενοτήτων της;' : 'Διαγραφή της «' + name + '»;')) return;
      var focus = b[b.length - 1].nextElementSibling || row.previousElementSibling;
      b.forEach(function (r) { r.remove(); });
      if (focus) focus.querySelector('[data-f="title"]').focus();
    }
    dirty = true; refresh();
    var btnEl = row.isConnected && row.querySelector('[data-act="' + a + '"]');
    if (btnEl && !btnEl.disabled) btnEl.focus(); else if (row.isConnected) row.querySelector('[data-f="title"]').focus();
  }

  data.forEach(function (n) { box.appendChild(makeRow(n)); });
  if (!data.length) box.appendChild(makeRow({ level: 0, title: 'Αρχική' }));
  refresh();

  box.addEventListener('click', function (e) {
    var b = e.target.closest('[data-act]');
    if (b && box.contains(b)) act(b.closest('.sp-st-row'), b.dataset.act);
  });
  box.addEventListener('keydown', function (e) {
    if (!e.target.matches('.sp-st-title')) return;
    var row = e.target.closest('.sp-st-row');
    if (e.key === 'Enter') { e.preventDefault(); add(row, level(row)); }
    else if (e.key === 'Tab' && !e.altKey && !e.ctrlKey && !e.metaKey) {
      // Tab only changes the level when it can; otherwise it moves focus as usual.
      if (e.shiftKey ? level(row) > 0 : (prevSibling(row) && Math.max.apply(null, block(row).map(level)) < MAX)) {
        e.preventDefault(); shift(row, e.shiftKey ? -1 : 1); dirty = true; refresh(); e.target.focus();
      }
    } else if (e.altKey && (e.key === 'ArrowUp' || e.key === 'ArrowDown')) {
      e.preventDefault(); act(row, e.key === 'ArrowUp' ? 'up' : 'down'); e.target.focus();
    }
  });
  box.addEventListener('input', function (e) {
    var row = e.target.closest('.sp-st-row');
    if (row) row.classList.toggle('has-content', hasContent(row));
  });
  document.querySelector('[data-st-add-root]').addEventListener('click', function () { add(null, 0); });
  document.querySelector('[data-st-expand]').addEventListener('click', function () { rows().forEach(function (r) { open(r, true); }); });
  document.querySelector('[data-st-collapse]').addEventListener('click', function () { rows().forEach(function (r) { open(r, false); }); });

  // Page layout pickers: the spacing only applies when the section continues on the same page.
  form.addEventListener('change', function (e) {
    var pick = e.target.closest && e.target.closest('[data-layout-pick]');
    if (pick && e.target.tagName === 'SELECT') pick.querySelector('[data-space]').hidden = e.target.value === '1';
  });
  form.addEventListener('input', function () { dirty = true; });
  form.addEventListener('change', function () { dirty = true; });
  form.addEventListener('submit', function () { serialize(); dirty = false; });
  window.spBeforeSubmit = function () { serialize(); dirty = false; };
  window.addEventListener('beforeunload', function (ev) { if (dirty) { ev.preventDefault(); ev.returnValue = ''; } });

  function printFrame(frame) {
    if (!frame || !frame.contentWindow) return;
    var title = document.title;
    document.title = window.spDocFileTitle || title;
    (frame.contentWindow.spOfferReady || Promise.resolve()).then(function () {
      frame.contentWindow.focus();
      frame.contentWindow.print();
      setTimeout(function () { document.title = title; }, 1000);
    });
  }
  // Preview: posts the current form (with unsaved changes) to structure.preview inside the dialog.
  window.spPreviewDoc = function () {
    var dlg = document.getElementById('sp-preview');
    var frame = document.getElementById('sp-preview-frame');
    var task = form.querySelector('input[name="task"]');
    document.getElementById('sp-preview-loading').hidden = false;
    document.getElementById('sp-preview-note').textContent = dirty ? 'Με τις τρέχουσες αλλαγές — δεν έχουν αποθηκευτεί ακόμα.' : 'Όπως είναι αποθηκευμένη.';
    serialize();
    form.target = 'sp-preview-frame';
    task.value = 'structure.preview';
    HTMLFormElement.prototype.submit.call(form);
    form.target = '';
    task.value = '';
    frame.onload = function () { document.getElementById('sp-preview-loading').hidden = true; };
    if (!dlg.open) dlg.showModal();
  };
  window.spPrintPreview = function () { printFrame(document.getElementById('sp-preview-frame')); };
  window.spPrintDoc = function () {
    if (dirty && !confirm('Υπάρχουν μη αποθηκευμένες αλλαγές που δεν θα εμφανιστούν στο Pdf. Συνέχεια;')) return;
    printFrame(document.getElementById('sp-doc-print-frame'));
  };
})();
